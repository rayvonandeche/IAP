<?php

/**
 * Database - Singleton database connection handler for BomaTrack
 * 
 * This class manages database connections using MySQLi with singleton pattern
 * to ensure only one connection instance exists throughout the application.
 */
class Database {
    private static $instance = null;
    private $connection;
    private $config;

    /**
     * Private constructor to prevent direct instantiation
     */
    private function __construct() {
        // Load database configuration
        $this->config = include __DIR__ . '/../../config/database.php';
        $this->connect();
    }

    /**
     * Get singleton instance of Database
     * 
     * @return Database Database instance
     */
    public static function getInstance(): Database {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Establish MySQLi connection
     */
    private function connect(): void {
        try {
            $this->connection = new mysqli(
                $this->config['host'], 
                $this->config['username'], 
                $this->config['password'], 
                $this->config['dbname'],
                3306 // Default MySQL port
            );

            // Set charset from config or default to utf8mb4
            $charset = $this->config['charset'] ?? 'utf8mb4';
            $this->connection->set_charset($charset);

            if ($this->connection->connect_error) {
                throw new Exception("Connection failed: " . $this->connection->connect_error);
            }

        } catch (Exception $e) {
            error_log("Database connection error: " . $e->getMessage());
            die("Database connection failed. Please check configuration.");
        }
    }

    /**
     * Get the MySQLi connection
     * 
     * @return mysqli MySQLi connection object
     */
    public function getConnection(): mysqli {
        // Check if connection is still alive
        if (!$this->connection || !$this->connection->ping()) {
            $this->connect();
        }
        
        return $this->connection;
    }

    /**
     * Execute a prepared statement query
     * 
     * @param string $query SQL query with placeholders
     * @param array $params Parameters for the query
     * @return mysqli_result|bool Query result
     */
    public function query(string $query, array $params = []) {
        try {
            $stmt = $this->connection->prepare($query);
            
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $this->connection->error);
            }

            if (!empty($params)) {
                $types = '';
                foreach ($params as $param) {
                    if (is_int($param)) {
                        $types .= 'i';
                    } elseif (is_float($param)) {
                        $types .= 'd';
                    } else {
                        $types .= 's';
                    }
                }
                $stmt->bind_param($types, ...$params);
            }

            $stmt->execute();
            return $stmt->get_result();

        } catch (Exception $e) {
            error_log("Database query error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get the last inserted ID
     * 
     * @return int Last inserted ID
     */
    public function lastInsertId(): int {
        return $this->connection->insert_id;
    }

    /**
     * Get the number of affected rows from last operation
     * 
     * @return int Number of affected rows
     */
    public function affectedRows(): int {
        return $this->connection->affected_rows;
    }

    /**
     * Escape string for safe SQL usage
     * 
     * @param string $string String to escape
     * @return string Escaped string
     */
    public function escapeString(string $string): string {
        return $this->connection->real_escape_string($string);
    }

    /**
     * Begin transaction
     * 
     * @return bool True on success, false on failure
     */
    public function beginTransaction(): bool {
        return $this->connection->begin_transaction();
    }

    /**
     * Commit transaction
     * 
     * @return bool True on success, false on failure
     */
    public function commit(): bool {
        return $this->connection->commit();
    }

    /**
     * Rollback transaction
     * 
     * @return bool True on success, false on failure
     */
    public function rollback(): bool {
        return $this->connection->rollback();
    }

    /**
     * Close database connection
     */
    public function close(): void {
        if ($this->connection) {
            $this->connection->close();
            $this->connection = null;
        }
    }

    /**
     * Prevent cloning of the instance
     */
    private function __clone() {}

    /**
     * Prevent unserialization of the instance
     */
    public function __wakeup() {
        throw new Exception("Cannot unserialize a singleton.");
    }

    /**
     * Close connection on destruction
     */
    public function __destruct() {
        $this->close();
    }
}