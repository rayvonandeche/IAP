<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

/**
 * EmailService - Handles email operations for the BomaTrack application
 * 
 * This service manages email sending functionality including:
 * - User verification emails during registration
 * - Password reset emails
 * - General notifications
 * 
 * Features proper OOP design with error handling and configuration management
 */
class EmailService 
{
    private $mailer;
    private $config;
    private $isConfigured;

    /**
     * Constructor - Initializes PHPMailer with configuration
     */
    public function __construct()
    {
        $this->mailer = new PHPMailer(true);
        $this->loadConfiguration();
        $this->configureSMTP();
    }

    /**
     * Load email configuration from config file or environment
     */
    private function loadConfiguration(): void
    {
        // Load from config file - using your working Gmail SMTP settings
        $this->config = [
            'smtp_host' => 'smtp.gmail.com',
            'smtp_port' => 465, // Using SMTPS port 465
            'smtp_username' => 'rayvon.andeche@strathmore.edu',
            'smtp_password' => 'rzog henx tauv aclh', // Your working app password
            'from_email' => 'noreply@bomatrack.com',
            'from_name' => 'BomaTrack Property Management',
            'encryption' => PHPMailer::ENCRYPTION_SMTPS // Using SMTPS for port 465
        ];

        $this->isConfigured = !empty($this->config['smtp_username']);
    }

    /**
     * Configure SMTP settings for PHPMailer
     */
    private function configureSMTP(): void
    {
        try {
            $this->mailer->isSMTP();
            $this->mailer->Host = $this->config['smtp_host'];
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = $this->config['smtp_username'];
            $this->mailer->Password = $this->config['smtp_password'];
            $this->mailer->SMTPSecure = $this->config['encryption'];
            $this->mailer->Port = $this->config['smtp_port'];

            // Set default from address
            $this->mailer->setFrom($this->config['from_email'], $this->config['from_name']);

            $this->mailer->SMTPDebug = SMTP::DEBUG_OFF;
            $this->mailer->Debugoutput = 'html';

        } catch (Exception $e) {
            error_log("EmailService SMTP Configuration Error: " . $e->getMessage());
            $this->isConfigured = false;
        }
    }

    /**
     * Send verification email to newly registered user
     * 
     * @param string $toEmail Recipient email address
     * @param string $toName Recipient name
     * @param string $verificationToken Unique verification token
     * @param string $baseUrl Application base URL
     * @return bool True if email sent successfully, false otherwise
     */
    public function sendVerificationEmail(string $toEmail, string $toName, string $verificationToken, string $baseUrl = 'http://localhost:8080'): bool
    {
        if (!$this->isConfigured) {
            error_log("EmailService: SMTP not configured properly");
            return false;
        }

        try {
            // Clear any previous recipients
            $this->mailer->clearAddresses();
            $this->mailer->clearAttachments();

            // Set recipient
            $this->mailer->addAddress($toEmail, $toName);

            // Set email content
            $this->mailer->isHTML(true);
            $this->mailer->Subject = 'Verify Your BomaTrack Account';

            $verificationUrl = $baseUrl . '/verify-email?token=' . urlencode($verificationToken);

            $htmlBody = $this->buildVerificationEmailTemplate($toName, $verificationUrl);
            $textBody = $this->buildVerificationEmailText($toName, $verificationUrl);

            $this->mailer->Body = $htmlBody;
            $this->mailer->AltBody = $textBody;

            // Send the email
            $result = $this->mailer->send();
            
            if ($result) {
                error_log("Verification email sent successfully to: " . $toEmail);
            }

            return $result;

        } catch (Exception $e) {
            error_log("EmailService Error sending verification email: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send password reset email
     * 
     * @param string $toEmail Recipient email address
     * @param string $toName Recipient name
     * @param string $resetToken Password reset token
     * @param string $baseUrl Application base URL
     * @return bool True if email sent successfully, false otherwise
     */
    public function sendPasswordResetEmail(string $toEmail, string $toName, string $resetToken, string $baseUrl = 'http://localhost:8080'): bool
    {
        if (!$this->isConfigured) {
            error_log("EmailService: SMTP not configured properly");
            return false;
        }

        try {
            // Clear any previous recipients
            $this->mailer->clearAddresses();
            $this->mailer->clearAttachments();

            // Set recipient
            $this->mailer->addAddress($toEmail, $toName);

            // Set email content
            $this->mailer->isHTML(true);
            $this->mailer->Subject = 'Reset Your BomaTrack Password';

            $resetUrl = $baseUrl . '/reset-password?token=' . urlencode($resetToken);

            $htmlBody = $this->buildPasswordResetEmailTemplate($toName, $resetUrl);
            $textBody = $this->buildPasswordResetEmailText($toName, $resetUrl);

            $this->mailer->Body = $htmlBody;
            $this->mailer->AltBody = $textBody;

            // Send the email
            $result = $this->mailer->send();
            
            if ($result) {
                error_log("Password reset email sent successfully to: " . $toEmail);
            }

            return $result;

        } catch (Exception $e) {
            error_log("EmailService Error sending password reset email: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Build HTML template for verification email
     * 
     * @param string $userName User's name
     * @param string $verificationUrl Verification URL
     * @return string HTML email content
     */
    private function buildVerificationEmailTemplate(string $userName, string $verificationUrl): string
    {
        return '
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Verify Your BomaTrack Account</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #2f3b45; color: white; padding: 20px; text-align: center; }
                .content { padding: 30px 20px; background: #f9f9f9; }
                .button { display: inline-block; padding: 12px 30px; background: #3b82f6; color: white; text-decoration: none; border-radius: 8px; margin: 20px 0; }
                .footer { padding: 20px; text-align: center; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>Welcome to BomaTrack</h1>
                    <p>Property Management Made Simple</p>
                </div>
                <div class="content">
                    <h2>Hello ' . htmlspecialchars($userName) . ',</h2>
                    <p>Thank you for registering with BomaTrack! To complete your account setup and start managing your properties, please verify your email address.</p>
                    
                    <p>Click the button below to verify your account:</p>
                    
                    <a href="' . htmlspecialchars($verificationUrl) . '" class="button">Verify My Account</a>
                    
                    <p>Or copy and paste this URL into your browser:</p>
                    <p style="word-break: break-all; background: #eee; padding: 10px; border-radius: 4px;">
                        ' . htmlspecialchars($verificationUrl) . '
                    </p>
                    
                    <p><strong>Important:</strong> This verification link will expire in 24 hours for security reasons.</p>
                    
                    <p>If you did not create an account with BomaTrack, please ignore this email.</p>
                </div>
                <div class="footer">
                    <p>&copy; ' . date('Y') . ' BomaTrack Property Management. All rights reserved.</p>
                    <p>This is an automated email. Please do not reply to this message.</p>
                </div>
            </div>
        </body>
        </html>';
    }

    /**
     * Build plain text version for verification email
     * 
     * @param string $userName User's name
     * @param string $verificationUrl Verification URL
     * @return string Plain text email content
     */
    private function buildVerificationEmailText(string $userName, string $verificationUrl): string
    {
        return "Welcome to BomaTrack - Property Management Made Simple\n\n" .
               "Hello " . $userName . ",\n\n" .
               "Thank you for registering with BomaTrack! To complete your account setup and start managing your properties, please verify your email address.\n\n" .
               "Please visit this URL to verify your account:\n" .
               $verificationUrl . "\n\n" .
               "Important: This verification link will expire in 24 hours for security reasons.\n\n" .
               "If you did not create an account with BomaTrack, please ignore this email.\n\n" .
               "© " . date('Y') . " BomaTrack Property Management. All rights reserved.\n" .
               "This is an automated email. Please do not reply to this message.";
    }

    /**
     * Build HTML template for password reset email
     * 
     * @param string $userName User's name
     * @param string $resetUrl Password reset URL
     * @return string HTML email content
     */
    private function buildPasswordResetEmailTemplate(string $userName, string $resetUrl): string
    {
        return '
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Reset Your BomaTrack Password</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #2f3b45; color: white; padding: 20px; text-align: center; }
                .content { padding: 30px 20px; background: #f9f9f9; }
                .button { display: inline-block; padding: 12px 30px; background: #e74c3c; color: white; text-decoration: none; border-radius: 8px; margin: 20px 0; }
                .footer { padding: 20px; text-align: center; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>Password Reset Request</h1>
                    <p>BomaTrack Property Management</p>
                </div>
                <div class="content">
                    <h2>Hello ' . htmlspecialchars($userName) . ',</h2>
                    <p>We received a request to reset the password for your BomaTrack account.</p>
                    
                    <p>Click the button below to create a new password:</p>
                    
                    <a href="' . htmlspecialchars($resetUrl) . '" class="button">Reset My Password</a>
                    
                    <p>Or copy and paste this URL into your browser:</p>
                    <p style="word-break: break-all; background: #eee; padding: 10px; border-radius: 4px;">
                        ' . htmlspecialchars($resetUrl) . '
                    </p>
                    
                    <p><strong>Important:</strong> This reset link will expire in 2 hours for security reasons.</p>
                    
                    <p>If you did not request a password reset, please ignore this email. Your password will remain unchanged.</p>
                </div>
                <div class="footer">
                    <p>&copy; ' . date('Y') . ' BomaTrack Property Management. All rights reserved.</p>
                    <p>This is an automated email. Please do not reply to this message.</p>
                </div>
            </div>
        </body>
        </html>';
    }

    /**
     * Build plain text version for password reset email
     * 
     * @param string $userName User's name
     * @param string $resetUrl Password reset URL
     * @return string Plain text email content
     */
    private function buildPasswordResetEmailText(string $userName, string $resetUrl): string
    {
        return "Password Reset Request - BomaTrack Property Management\n\n" .
               "Hello " . $userName . ",\n\n" .
               "We received a request to reset the password for your BomaTrack account.\n\n" .
               "Please visit this URL to create a new password:\n" .
               $resetUrl . "\n\n" .
               "Important: This reset link will expire in 2 hours for security reasons.\n\n" .
               "If you did not request a password reset, please ignore this email. Your password will remain unchanged.\n\n" .
               "© " . date('Y') . " BomaTrack Property Management. All rights reserved.\n" .
               "This is an automated email. Please do not reply to this message.";
    }

    /**
     * Test email configuration
     * 
     * @return bool True if configuration is valid, false otherwise
     */
    public function testConfiguration(): bool
    {
        return $this->isConfigured;
    }

    /**
     * Get current configuration status
     * 
     * @return array Configuration status information
     */
    public function getStatus(): array
    {
        return [
            'configured' => $this->isConfigured,
            'smtp_host' => $this->config['smtp_host'] ?? 'Not set',
            'from_email' => $this->config['from_email'] ?? 'Not set'
        ];
    }
}
