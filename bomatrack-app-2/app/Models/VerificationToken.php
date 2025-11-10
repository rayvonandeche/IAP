<?php

require_once __DIR__ . '/../Core/Database.php';
require_once __DIR__ . '/BaseModel.php';

/**
 * VerificationToken Model - Handles email verification tokens for BomaTrack
 * 
 * This model manages verification tokens for user email verification during registration.
 * It provides secure token generation, storage, validation, and cleanup functionality.
 * 
 * Features:
 * - Secure token generation using cryptographically strong random bytes
 * - Token expiration handling (24 hours default)
 * - Database operations for token management
 * - Security measures against token reuse and timing attacks
 */
class VerificationToken extends BaseModel
{
    private $id;
    private $userId;
    private $email;
    private $token;
    private $expiresAt;
    private $createdAt;
    private $usedAt;
    private $isUsed;

    // Token expiration time in seconds (24 hours)
    const TOKEN_EXPIRATION_HOURS = 24;
    const TOKEN_LENGTH = 32; // 32 bytes = 256 bits

    /**
     * Constructor
     * 
     * @param int|null $id Token ID
     * @param int|null $userId User ID
     * @param string|null $email User email
     * @param string|null $token Verification token
     * @param string|null $expiresAt Expiration timestamp
     * @param string|null $createdAt Creation timestamp
     * @param string|null $usedAt Usage timestamp
     * @param bool $isUsed Whether token has been used
     */
    public function __construct(
        ?int $id = null,
        ?int $userId = null,
        ?string $email = null,
        ?string $token = null,
        ?string $expiresAt = null,
        ?string $createdAt = null,
        ?string $usedAt = null,
        bool $isUsed = false
    ) {
        $this->id = $id;
        $this->userId = $userId;
        $this->email = $email;
        $this->token = $token;
        $this->expiresAt = $expiresAt;
        $this->createdAt = $createdAt;
        $this->usedAt = $usedAt;
        $this->isUsed = $isUsed;
    }

    /**
     * Generate a new verification token for a user
     * 
     * @param int $userId User ID
     * @param string $email User email address
     * @return self|null New VerificationToken instance or null on failure
     */
    public static function generateForUser(int $userId, string $email): ?self
    {
        try {
            // Generate cryptographically secure token
            $token = bin2hex(random_bytes(self::TOKEN_LENGTH));
            
            // Calculate expiration time
            $expiresAt = date('Y-m-d H:i:s', time() + (self::TOKEN_EXPIRATION_HOURS * 3600));
            
            // Create new token instance
            $verificationToken = new self(
                null, // ID will be set by database
                $userId,
                $email,
                $token,
                $expiresAt,
                date('Y-m-d H:i:s'), // createdAt
                null, // usedAt
                false // isUsed
            );

            // Save to database
            if ($verificationToken->save()) {
                return $verificationToken;
            }

            return null;

        } catch (Exception $e) {
            error_log("VerificationToken generation error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Find verification token by token string
     * 
     * @param string $token Token string to search for
     * @return self|null VerificationToken instance or null if not found
     */
    public static function findByToken(string $token): ?self
    {
        try {
            $db = Database::getInstance();
            $connection = $db->getConnection();

            $query = "SELECT * FROM verification_tokens WHERE token = ? AND is_used = 0 LIMIT 1";
            $stmt = $connection->prepare($query);
            $stmt->bind_param('s', $token);
            $stmt->execute();
            
            $result = $stmt->get_result();
            
            if ($result && $result->num_rows > 0) {
                $data = $result->fetch_assoc();
                return new self(
                    $data['id'],
                    $data['user_id'],
                    $data['email'],
                    $data['token'],
                    $data['expires_at'],
                    $data['created_at'],
                    $data['used_at'],
                    (bool)$data['is_used']
                );
            }

            return null;

        } catch (Exception $e) {
            error_log("Error finding verification token: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Find all tokens for a specific user
     * 
     * @param int $userId User ID
     * @return array Array of VerificationToken instances
     */
    public static function findByUserId(int $userId): array
    {
        try {
            $db = Database::getInstance();
            $connection = $db->getConnection();

            $query = "SELECT * FROM verification_tokens WHERE user_id = ? ORDER BY created_at DESC";
            $stmt = $connection->prepare($query);
            $stmt->bind_param('i', $userId);
            $stmt->execute();
            
            $result = $stmt->get_result();
            $tokens = [];
            
            if ($result) {
                while ($data = $result->fetch_assoc()) {
                    $tokens[] = new self(
                        $data['id'],
                        $data['user_id'],
                        $data['email'],
                        $data['token'],
                        $data['expires_at'],
                        $data['created_at'],
                        $data['used_at'],
                        (bool)$data['is_used']
                    );
                }
            }

            return $tokens;

        } catch (Exception $e) {
            error_log("Error finding tokens by user ID: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Validate if token is still valid (not expired and not used)
     * 
     * @return bool True if token is valid, false otherwise
     */
    public function isValid(): bool
    {
        // Check if token is already used
        if ($this->isUsed) {
            return false;
        }

        // Check if token is expired
        $currentTime = time();
        $expirationTime = strtotime($this->expiresAt);
        
        if ($currentTime > $expirationTime) {
            return false;
        }

        return true;
    }

    /**
     * Mark token as used
     * 
     * @return bool True if successfully marked as used, false otherwise
     */
    public function markAsUsed(): bool
    {
        try {
            $db = Database::getInstance();
            $connection = $db->getConnection();

            $query = "UPDATE verification_tokens SET is_used = 1, used_at = NOW() WHERE id = ?";
            $stmt = $connection->prepare($query);
            $stmt->bind_param('i', $this->id);
            
            if ($stmt->execute()) {
                $this->isUsed = true;
                $this->usedAt = date('Y-m-d H:i:s');
                return true;
            }

            return false;

        } catch (Exception $e) {
            error_log("Error marking token as used: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Save verification token to database
     * 
     * @return bool True if saved successfully, false otherwise
     */
    public function save(): bool
    {
        try {
            $db = Database::getInstance();
            $connection = $db->getConnection();

            if ($this->id === null) {
                // Insert new token
                $query = "INSERT INTO verification_tokens (user_id, email, token, expires_at, created_at, is_used) 
                         VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $connection->prepare($query);
                $isUsedInt = $this->isUsed ? 1 : 0;
                $stmt->bind_param(
                    'issssi', 
                    $this->userId, 
                    $this->email, 
                    $this->token, 
                    $this->expiresAt, 
                    $this->createdAt,
                    $isUsedInt
                );
                
                if ($stmt->execute()) {
                    $this->id = $connection->insert_id;
                    return true;
                }
            } else {
                // Update existing token
                $query = "UPDATE verification_tokens SET 
                         user_id = ?, email = ?, token = ?, expires_at = ?, 
                         used_at = ?, is_used = ? WHERE id = ?";
                $stmt = $connection->prepare($query);
                $isUsedInt = $this->isUsed ? 1 : 0;
                $stmt->bind_param(
                    'issssii', 
                    $this->userId, 
                    $this->email, 
                    $this->token, 
                    $this->expiresAt,
                    $this->usedAt,
                    $isUsedInt,
                    $this->id
                );
                
                return $stmt->execute();
            }

            return false;

        } catch (Exception $e) {
            error_log("Error saving verification token: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete verification token from database
     * 
     * @return bool True if deleted successfully, false otherwise
     */
    public function delete(): bool
    {
        try {
            if ($this->id === null) {
                return false;
            }

            $db = Database::getInstance();
            $connection = $db->getConnection();

            $query = "DELETE FROM verification_tokens WHERE id = ?";
            $stmt = $connection->prepare($query);
            $stmt->bind_param('i', $this->id);
            
            return $stmt->execute();

        } catch (Exception $e) {
            error_log("Error deleting verification token: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Clean up expired tokens (maintenance method)
     * 
     * @return int Number of tokens cleaned up
     */
    public static function cleanupExpiredTokens(): int
    {
        try {
            $db = Database::getInstance();
            $connection = $db->getConnection();

            $query = "DELETE FROM verification_tokens WHERE expires_at < NOW()";
            $stmt = $connection->prepare($query);
            $stmt->execute();
            
            return $stmt->affected_rows;

        } catch (Exception $e) {
            error_log("Error cleaning up expired tokens: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Revoke all tokens for a specific user
     * 
     * @param int $userId User ID
     * @return bool True if tokens were revoked successfully, false otherwise
     */
    public static function revokeAllForUser(int $userId): bool
    {
        try {
            $db = Database::getInstance();
            $connection = $db->getConnection();

            $query = "UPDATE verification_tokens SET is_used = 1, used_at = NOW() WHERE user_id = ? AND is_used = 0";
            $stmt = $connection->prepare($query);
            $stmt->bind_param('i', $userId);
            
            return $stmt->execute();

        } catch (Exception $e) {
            error_log("Error revoking tokens for user: " . $e->getMessage());
            return false;
        }
    }

    // Getters
    public function getId(): ?int { return $this->id; }
    public function getUserId(): ?int { return $this->userId; }
    public function getEmail(): ?string { return $this->email; }
    public function getToken(): ?string { return $this->token; }
    public function getExpiresAt(): ?string { return $this->expiresAt; }
    public function getCreatedAt(): ?string { return $this->createdAt; }
    public function getUsedAt(): ?string { return $this->usedAt; }
    public function getIsUsed(): bool { return $this->isUsed; }

    // Setters
    public function setUserId(int $userId): void { $this->userId = $userId; }
    public function setEmail(string $email): void { $this->email = $email; }
}
