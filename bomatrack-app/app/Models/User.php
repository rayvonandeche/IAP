<?php

require_once __DIR__ . '/BaseModel.php';

/**
 * User Model - Enhanced for BomaTrack property management system
 * 
 * This model handles user management including authentication, email verification,
 * and user profile operations. Designed for property managers and landlords.
 */
class User extends BaseModel {
    private $id;
    private $username;
    private $email;
    private $password;
    private $firstName;
    private $lastName;
    private $phone;
    private $isEmailVerified;
    private $emailVerifiedAt;
    private $createdAt;
    private $updatedAt;
    private $lastLoginAt;
    private $profileCompleted;

    /**
     * Constructor
     */
    public function __construct(
        ?int $id = null,
        ?string $username = null,
        ?string $email = null,
        ?string $password = null,
        ?string $firstName = null,
        ?string $lastName = null,
        ?string $phone = null,
        bool $isEmailVerified = false,
        ?string $emailVerifiedAt = null,
        ?string $createdAt = null,
        ?string $updatedAt = null,
        ?string $lastLoginAt = null,
        bool $profileCompleted = false
    ) {
        $this->id = $id;
        $this->username = $username;
        $this->email = $email;
        $this->password = $password;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->phone = $phone;
        $this->isEmailVerified = $isEmailVerified;
        $this->emailVerifiedAt = $emailVerifiedAt;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
        $this->lastLoginAt = $lastLoginAt;
        $this->profileCompleted = $profileCompleted;
    }

    /**
     * Create a new user account
     * 
     * @param string $username Username
     * @param string $email Email address
     * @param string $password Plain text password (will be hashed)
     * @param string $firstName First name
     * @param string $lastName Last name
     * @return self|null New User instance or null on failure
     */
    public static function create(string $username, string $email, string $password, string $firstName = '', string $lastName = ''): ?self
    {
        try {
            // Check if user already exists
            if (self::findByEmail($email) || self::findByUsername($username)) {
                return null; // User already exists
            }

            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $db = Database::getInstance();
            $connection = $db->getConnection();

            $query = "INSERT INTO users (username, email, password, first_name, last_name, created_at, updated_at) 
                     VALUES (?, ?, ?, ?, ?, NOW(), NOW())";
            
            $stmt = $connection->prepare($query);
            $stmt->bind_param('sssss', $username, $email, $hashedPassword, $firstName, $lastName);
            
            if ($stmt->execute()) {
                $userId = $connection->insert_id;
                return self::findById($userId);
            }

            return null;

        } catch (Exception $e) {
            error_log("Error creating user: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Find user by ID
     * 
     * @param int $id User ID
     * @return self|null User instance or null if not found
     */
    public static function findById(int $id): ?self
    {
        try {
            $db = Database::getInstance();
            $connection = $db->getConnection();

            $query = "SELECT * FROM users WHERE id = ? LIMIT 1";
            $stmt = $connection->prepare($query);
            $stmt->bind_param('i', $id);
            $stmt->execute();
            
            $result = $stmt->get_result();
            
            if ($result && $result->num_rows > 0) {
                $data = $result->fetch_assoc();
                return self::createFromArray($data);
            }

            return null;

        } catch (Exception $e) {
            error_log("Error finding user by ID: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Find user by username
     * 
     * @param string $username Username
     * @return self|null User instance or null if not found
     */
    public static function findByUsername(string $username): ?self
    {
        try {
            $db = Database::getInstance();
            $connection = $db->getConnection();

            $query = "SELECT * FROM users WHERE username = ? LIMIT 1";
            $stmt = $connection->prepare($query);
            $stmt->bind_param('s', $username);
            $stmt->execute();
            
            $result = $stmt->get_result();
            
            if ($result && $result->num_rows > 0) {
                $data = $result->fetch_assoc();
                return self::createFromArray($data);
            }

            return null;

        } catch (Exception $e) {
            error_log("Error finding user by username: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Find user by email
     * 
     * @param string $email Email address
     * @return self|null User instance or null if not found
     */
    public static function findByEmail(string $email): ?self
    {
        try {
            $db = Database::getInstance();
            $connection = $db->getConnection();

            $query = "SELECT * FROM users WHERE email = ? LIMIT 1";
            $stmt = $connection->prepare($query);
            $stmt->bind_param('s', $email);
            $stmt->execute();
            
            $result = $stmt->get_result();
            
            if ($result && $result->num_rows > 0) {
                $data = $result->fetch_assoc();
                return self::createFromArray($data);
            }

            return null;

        } catch (Exception $e) {
            error_log("Error finding user by email: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Find user by username or email
     * 
     * @param string $usernameOrEmail Username or email address
     * @return User|null User object if found, null otherwise
     */
    public static function findByUsernameOrEmail(string $usernameOrEmail): ?self
    {
        try {
            $db = Database::getInstance();
            $connection = $db->getConnection();

            $query = "SELECT * FROM users WHERE username = ? OR email = ? LIMIT 1";
            $stmt = $connection->prepare($query);
            $stmt->bind_param('ss', $usernameOrEmail, $usernameOrEmail);
            $stmt->execute();
            
            $result = $stmt->get_result();
            $data = $result->fetch_assoc();

            if (!$data) {
                return null;
            }

            return new self(
                $data['id'],
                $data['username'],
                $data['email'],
                $data['password'],
                $data['first_name'],
                $data['last_name'],
                $data['phone'],
                (bool)$data['is_email_verified'],
                $data['email_verified_at'],
                (bool)$data['profile_completed'],
                $data['last_login_at'],
                $data['created_at'],
                $data['updated_at']
            );

        } catch (Exception $e) {
            error_log("Error finding user by username/email: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Verify user password
     * 
     * @param string $password Plain text password
     * @return bool True if password is correct, false otherwise
     */
    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->password);
    }

    /**
     * Mark email as verified
     * 
     * @return bool True if successfully verified, false otherwise
     */
    public function markEmailAsVerified(): bool
    {
        try {
            $db = Database::getInstance();
            $connection = $db->getConnection();

            $query = "UPDATE users SET is_email_verified = 1, email_verified_at = NOW(), updated_at = NOW() WHERE id = ?";
            $stmt = $connection->prepare($query);
            $stmt->bind_param('i', $this->id);
            
            if ($stmt->execute()) {
                $this->isEmailVerified = true;
                $this->emailVerifiedAt = date('Y-m-d H:i:s');
                return true;
            }

            return false;

        } catch (Exception $e) {
            error_log("Error marking email as verified: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update last login timestamp
     * 
     * @return bool True if successfully updated, false otherwise
     */
    public function updateLastLogin(): bool
    {
        try {
            $db = Database::getInstance();
            $connection = $db->getConnection();

            $query = "UPDATE users SET last_login_at = NOW(), updated_at = NOW() WHERE id = ?";
            $stmt = $connection->prepare($query);
            $stmt->bind_param('i', $this->id);
            
            if ($stmt->execute()) {
                $this->lastLoginAt = date('Y-m-d H:i:s');
                return true;
            }

            return false;

        } catch (Exception $e) {
            error_log("Error updating last login: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update user profile
     * 
     * @param array $data Profile data to update
     * @return bool True if successfully updated, false otherwise
     */
    public function updateProfile(array $data): bool
    {
        try {
            $db = Database::getInstance();
            $connection = $db->getConnection();

            $updateFields = [];
            $params = [];
            $types = '';

            // Build dynamic update query based on provided data
            if (isset($data['first_name'])) {
                $updateFields[] = "first_name = ?";
                $params[] = $data['first_name'];
                $types .= 's';
                $this->firstName = $data['first_name'];
            }

            if (isset($data['last_name'])) {
                $updateFields[] = "last_name = ?";
                $params[] = $data['last_name'];
                $types .= 's';
                $this->lastName = $data['last_name'];
            }

            if (isset($data['phone'])) {
                $updateFields[] = "phone = ?";
                $params[] = $data['phone'];
                $types .= 's';
                $this->phone = $data['phone'];
            }

            if (isset($data['username'])) {
                $updateFields[] = "username = ?";
                $params[] = $data['username'];
                $types .= 's';
                $this->username = $data['username'];
            }

            if (empty($updateFields)) {
                return true; // Nothing to update
            }

            $updateFields[] = "updated_at = NOW()";
            
            // Check if profile is now complete
            if ($this->firstName && $this->lastName && $this->phone) {
                $updateFields[] = "profile_completed = 1";
                $this->profileCompleted = true;
            }

            $params[] = $this->id;
            $types .= 'i';

            $query = "UPDATE users SET " . implode(', ', $updateFields) . " WHERE id = ?";
            $stmt = $connection->prepare($query);
            $stmt->bind_param($types, ...$params);
            
            return $stmt->execute();

        } catch (Exception $e) {
            error_log("Error updating user profile: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Change user password
     * 
     * @param string $newPassword New plain text password
     * @return bool True if successfully changed, false otherwise
     */
    public function changePassword(string $newPassword): bool
    {
        try {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            
            $db = Database::getInstance();
            $connection = $db->getConnection();

            $query = "UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?";
            $stmt = $connection->prepare($query);
            $stmt->bind_param('si', $hashedPassword, $this->id);
            
            if ($stmt->execute()) {
                $this->password = $hashedPassword;
                return true;
            }

            return false;

        } catch (Exception $e) {
            error_log("Error changing password: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete user account
     * 
     * @return bool True if successfully deleted, false otherwise
     */
    public function delete(): bool
    {
        try {
            if ($this->id === null) {
                return false;
            }

            $db = Database::getInstance();
            $connection = $db->getConnection();

            // Start transaction
            $connection->begin_transaction();

            try {
                // Delete user's verification tokens
                $query = "DELETE FROM verification_tokens WHERE user_id = ?";
                $stmt = $connection->prepare($query);
                $stmt->bind_param('i', $this->id);
                $stmt->execute();

                // Delete user
                $query = "DELETE FROM users WHERE id = ?";
                $stmt = $connection->prepare($query);
                $stmt->bind_param('i', $this->id);
                $stmt->execute();

                $connection->commit();
                return true;

            } catch (Exception $e) {
                $connection->rollback();
                throw $e;
            }

        } catch (Exception $e) {
            error_log("Error deleting user: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Create User instance from database array
     * 
     * @param array $data Database row data
     * @return self User instance
     */
    private static function createFromArray(array $data): self
    {
        return new self(
            (int)$data['id'],
            $data['username'],
            $data['email'],
            $data['password'],
            $data['first_name'] ?? '',
            $data['last_name'] ?? '',
            $data['phone'] ?? '',
            (bool)($data['is_email_verified'] ?? false),
            $data['email_verified_at'],
            $data['created_at'],
            $data['updated_at'],
            $data['last_login_at'],
            (bool)($data['profile_completed'] ?? false)
        );
    }

    /**
     * Get user's full name
     * 
     * @return string Full name
     */
    public function getFullName(): string
    {
        return trim($this->firstName . ' ' . $this->lastName);
    }

    /**
     * Check if user needs to complete profile
     * 
     * @return bool True if profile needs completion, false otherwise
     */
    public function needsProfileCompletion(): bool
    {
        return !$this->profileCompleted;
    }

    // Getters
    public function getId(): ?int { return $this->id; }
    public function getUsername(): ?string { return $this->username; }
    public function getEmail(): ?string { return $this->email; }
    public function getFirstName(): ?string { return $this->firstName; }
    public function getLastName(): ?string { return $this->lastName; }
    public function getPhone(): ?string { return $this->phone; }
    public function isEmailVerified(): bool { return $this->isEmailVerified; }
    public function getEmailVerifiedAt(): ?string { return $this->emailVerifiedAt; }
    public function getCreatedAt(): ?string { return $this->createdAt; }
    public function getUpdatedAt(): ?string { return $this->updatedAt; }
    public function getLastLoginAt(): ?string { return $this->lastLoginAt; }
    public function isProfileCompleted(): bool { return $this->profileCompleted; }

    // Setters (for updating object state)
    public function setFirstName(string $firstName): void { $this->firstName = $firstName; }
    public function setLastName(string $lastName): void { $this->lastName = $lastName; }
    public function setPhone(string $phone): void { $this->phone = $phone; }
    public function setUsername(string $username): void { $this->username = $username; }
}