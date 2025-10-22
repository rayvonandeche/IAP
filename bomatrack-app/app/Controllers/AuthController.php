<?php

require_once __DIR__ . '/../Models/User.php';
require_once __DIR__ . '/../Models/VerificationToken.php';
require_once __DIR__ . '/../Services/EmailService.php';
require_once __DIR__ . '/../Helpers/AuthHelper.php';

/**
 * AuthController - Handles authentication operations for BomaTrack
 * 
 * This controller manages user authentication including:
 * - User registration with email verification
 * - User login with security checks
 * - Email verification process
 */
class AuthController extends BaseController
{
    private $emailService;

    public function __construct()
    {
        $this->emailService = new EmailService();
    }

    /**
     * Handle user login
     */
    public function login()
    {
        // Redirect if already authenticated
        AuthHelper::redirectIfAuthenticated();
        
        $errors = [];
        $message = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Sanitize input
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';

            // Validate input
            if (empty($username)) {
                $errors['username'] = 'Username or email is required';
            }
            if (empty($password)) {
                $errors['password'] = 'Password is required';
            }

            if (empty($errors)) {
                // Use AuthHelper for login
                if (AuthHelper::login($username, $password)) {
                    $user = AuthHelper::getCurrentUser();
                    
                    // Check if email is verified
                    if (!$user->isEmailVerified()) {
                        // Log out and show error
                        AuthHelper::logout();
                        $errors['general'] = 'Please verify your email address before logging in. Check your inbox for the verification email.';
                    } else {
                        // Successful login - redirect to dashboard
                        header('Location: /dashboard');
                        exit;
                    }
                } else {
                    $errors['general'] = 'Invalid username/email or password';
                }
            }
        }

        // Render login view with any errors
        $this->render('auth/login', [
            'errors' => $errors,
            'message' => $message,
            'username' => $username ?? ''
        ]);
    }

    /**
     * Handle user registration
     */
    public function register()
    {
        // Redirect if already authenticated
        AuthHelper::redirectIfAuthenticated();
        
        $errors = [];
        $message = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Sanitize input
            $username = trim($_POST['username'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            $firstName = trim($_POST['first_name'] ?? '');
            $lastName = trim($_POST['last_name'] ?? '');

            // Validate input
            $errors = $this->validateRegistrationData($username, $email, $password, $confirmPassword);

            if (empty($errors)) {
                // Check if user already exists
                if (User::findByEmail($email)) {
                    $errors['email'] = 'An account with this email already exists';
                }
                if (User::findByUsername($username)) {
                    $errors['username'] = 'This username is already taken';
                }

                if (empty($errors)) {
                    // Create new user
                    $user = User::create($username, $email, $password, $firstName, $lastName);

                    if ($user) {
                        // Generate verification token
                        $verificationToken = VerificationToken::generateForUser($user->getId(), $user->getEmail());

                        if ($verificationToken) {
                            // Send verification email
                            $emailSent = $this->emailService->sendVerificationEmail(
                                $user->getEmail(),
                                $user->getFullName() ?: $user->getUsername(),
                                $verificationToken->getToken()
                            );

                            if ($emailSent) {
                                $message = 'Registration successful! Please check your email to verify your account before logging in.';
                                
                                // Clear form data on success
                                $username = $email = $firstName = $lastName = '';
                            } else {
                                $errors['general'] = 'Account created but verification email could not be sent. Please contact support.';
                            }
                        } else {
                            $errors['general'] = 'Account created but verification token could not be generated. Please contact support.';
                        }
                    } else {
                        $errors['general'] = 'Registration failed. Please try again.';
                    }
                }
            }
        }

        // Render registration view
        $this->render('auth/register', [
            'errors' => $errors,
            'message' => $message,
            'username' => $username ?? '',
            'email' => $email ?? '',
            'first_name' => $firstName ?? '',
            'last_name' => $lastName ?? ''
        ]);
    }

    /**
     * Handle email verification
     */
    public function verifyEmail()
    {
        // First, refresh session data to ensure it's current
        AuthHelper::refreshSessionFromDatabase();
        
        // If user is logged in and already verified, redirect to dashboard
        if (AuthHelper::isLoggedIn() && AuthHelper::isEmailVerified()) {
            header('Location: /dashboard');
            exit;
        }
        
        $token = $_GET['token'] ?? '';
        $message = '';
        $success = false;

        if (empty($token)) {
            // No token provided - show the verification page for logged-in users
            if (AuthHelper::isLoggedIn()) {
                $user = AuthHelper::getCurrentUser();
                if ($user && $user->isEmailVerified()) {
                    // User is verified but session is out of sync - fix it
                    AuthHelper::updateEmailVerificationStatus(true);
                    header('Location: /dashboard');
                    exit;
                }
                $message = 'Please check your email for the verification link, or request a new one below.';
            } else {
                $message = 'Invalid verification link. No token provided.';
            }
        } else {
            // Find verification token
            $verificationToken = VerificationToken::findByToken($token);

            if (!$verificationToken) {
                $message = 'Invalid or expired verification token.';
            } elseif (!$verificationToken->isValid()) {
                $message = 'This verification token has expired or has already been used.';
            } else {
                // Find user
                $user = User::findById($verificationToken->getUserId());

                if (!$user) {
                    $message = 'User not found.';
                } elseif ($user->isEmailVerified()) {
                    $message = 'Your email is already verified. You can log in to your account.';
                    $success = true;
                } else {
                    // Mark email as verified
                    if ($user->markEmailAsVerified()) {
                        // Mark token as used
                        $verificationToken->markAsUsed();
                        
                        $message = 'Email verification successful! You can now log in to your account.';
                        $success = true;

                        // Update session if user is already logged in
                        session_start();
                        if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $user->getId()) {
                            $_SESSION['is_email_verified'] = true;
                            AuthHelper::updateEmailVerificationStatus(true);
                        }
                        
                        // Update last login
                        $user->updateLastLogin();
                    } else {
                        $message = 'Verification failed. Please try again or contact support.';
                    }
                }
            }
        }

        // Render verification result page
        $this->render('auth/verify-email', [
            'message' => $message,
            'success' => $success
        ]);
    }

    /**
     * Handle logout
     */
    public function logout()
    {
        AuthHelper::logout();
        header('Location: /login');
        exit;
    }

    /**
     * Resend verification email
     */
    public function resendVerification()
    {
        $message = '';
        $success = false;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');

            if (empty($email)) {
                $message = 'Email address is required.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $message = 'Please enter a valid email address.';
            } else {
                $user = User::findByEmail($email);

                if (!$user) {
                    $message = 'No account found with this email address.';
                } elseif ($user->isEmailVerified()) {
                    $message = 'This email is already verified. You can log in to your account.';
                } else {
                    // Revoke old tokens and generate new one
                    VerificationToken::revokeAllForUser($user->getId());
                    $verificationToken = VerificationToken::generateForUser($user->getId(), $user->getEmail());

                    if ($verificationToken) {
                        $emailSent = $this->emailService->sendVerificationEmail(
                            $user->getEmail(),
                            $user->getFullName() ?: $user->getUsername(),
                            $verificationToken->getToken()
                        );

                        if ($emailSent) {
                            $message = 'Verification email sent! Please check your inbox.';
                            $success = true;
                        } else {
                            $message = 'Failed to send verification email. Please try again.';
                        }
                    } else {
                        $message = 'Failed to generate verification token. Please try again.';
                    }
                }
            }
        }

        $this->render('auth/resend-verification', [
            'message' => $message,
            'success' => $success,
            'email' => $_POST['email'] ?? ''
        ]);
    }

    /**
     * Validate registration data
     * 
     * @param string $username Username
     * @param string $email Email
     * @param string $password Password
     * @param string $confirmPassword Confirm password
     * @return array Array of validation errors
     */
    private function validateRegistrationData(string $username, string $email, string $password, string $confirmPassword): array
    {
        $errors = [];

        // Username validation
        if (empty($username)) {
            $errors['username'] = 'Username is required';
        } elseif (strlen($username) < 3) {
            $errors['username'] = 'Username must be at least 3 characters long';
        } elseif (strlen($username) > 30) {
            $errors['username'] = 'Username must be less than 30 characters';
        } elseif (!preg_match('/^[a-zA-Z0-9_.-]+$/', $username)) {
            $errors['username'] = 'Username can only contain letters, numbers, dots, dashes, and underscores';
        }

        // Email validation
        if (empty($email)) {
            $errors['email'] = 'Email is required';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Please enter a valid email address';
        }

        // Password validation
        if (empty($password)) {
            $errors['password'] = 'Password is required';
        } elseif (strlen($password) < 8) {
            $errors['password'] = 'Password must be at least 8 characters long';
        } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/', $password)) {
            $errors['password'] = 'Password must contain at least one uppercase letter, one lowercase letter, and one number';
        }

        // Confirm password validation
        if (empty($confirmPassword)) {
            $errors['confirm_password'] = 'Please confirm your password';
        } elseif ($password !== $confirmPassword) {
            $errors['confirm_password'] = 'Passwords do not match';
        }

        return $errors;
    }
}