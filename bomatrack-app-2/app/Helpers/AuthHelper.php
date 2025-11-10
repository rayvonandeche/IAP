<?php

require_once __DIR__ . '/../Models/User.php';

class AuthHelper {
    
    /**
     * Start session if not already started
     */
    public static function startSession(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Login user with username/email and password
     */
    public static function login(string $usernameOrEmail, string $password): bool {
        self::startSession();
        
        $user = User::findByUsernameOrEmail($usernameOrEmail);
        
        if ($user && $user->verifyPassword($password)) {
            $_SESSION['user_id'] = $user->getId();
            $_SESSION['username'] = $user->getUsername();
            $_SESSION['email'] = $user->getEmail();
            $_SESSION['is_email_verified'] = $user->isEmailVerified();
            
            // Update last login
            $user->updateLastLogin();
            
            return true;
        }
        
        return false;
    }

    /**
     * Logout current user
     */
    public static function logout(): void {
        self::startSession();
        
        // Clear all session data
        $_SESSION = [];
        
        // Destroy the session
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        session_destroy();
    }

    /**
     * Check if user is logged in
     */
    public static function isLoggedIn(): bool {
        self::startSession();
        return isset($_SESSION['user_id']);
    }

    /**
     * Check if user is authenticated (logged in)
     * Alias for isLoggedIn()
     */
    public static function isAuthenticated(): bool {
        return self::isLoggedIn();
    }

    /**
     * Get current logged-in user
     */
    public static function getCurrentUser(): ?User {
        self::startSession();
        
        if (!isset($_SESSION['user_id'])) {
            return null;
        }
        
        return User::findById($_SESSION['user_id']);
    }

    /**
     * Get current user ID
     */
    public static function getCurrentUserId(): ?int {
        self::startSession();
        return $_SESSION['user_id'] ?? null;
    }

    /**
     * Check if current user's email is verified
     */
    public static function isEmailVerified(): bool {
        self::startSession();
        return $_SESSION['is_email_verified'] ?? false;
    }

    /**
     * Update session verification status
     */
    public static function updateEmailVerificationStatus(bool $verified): void {
        self::startSession();
        $_SESSION['is_email_verified'] = $verified;
        
        // Also refresh the user data to ensure it's current
        if (isset($_SESSION['user_id'])) {
            $user = User::findById($_SESSION['user_id']);
            if ($user) {
                $_SESSION['is_email_verified'] = $user->isEmailVerified();
            }
        }
    }

    /**
     * Require authentication - redirect to login if not authenticated
     */
    public static function requireAuth(): void {
        if (!self::isLoggedIn()) {
            header('Location: /login');
            exit();
        }
    }

    /**
     * Require email verification - redirect to verification page if not verified
     */
    public static function requireEmailVerification(): void {
        self::requireAuth();
        
        if (!self::isEmailVerified()) {
            header('Location: /verify-email');
            exit();
        }
    }

    /**
     * Redirect authenticated users away from auth pages
     */
    public static function redirectIfAuthenticated(string $redirectTo = '/dashboard'): void {
        if (self::isLoggedIn()) {
            header("Location: $redirectTo");
            exit();
        }
    }

    /**
     * Refresh session data from database
     * Useful when user data might have changed (like email verification)
     */
    public static function refreshSessionFromDatabase(): bool {
        self::startSession();
        
        if (!isset($_SESSION['user_id'])) {
            return false;
        }
        
        $user = User::findById($_SESSION['user_id']);
        if (!$user) {
            // User no longer exists, logout
            self::logout();
            return false;
        }
        
        // Update session with fresh data
        $_SESSION['username'] = $user->getUsername();
        $_SESSION['email'] = $user->getEmail();
        $_SESSION['is_email_verified'] = $user->isEmailVerified();
        
        return true;
    }
}