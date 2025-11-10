<?php

/**
 * BaseModel - Simple base class for all models
 * Uses MySQLi for database operations
 */
abstract class BaseModel {
    protected $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Get database connection
     */
    protected function getConnection() {
        return $this->db->getConnection();
    }

    /**
     * Execute a prepared statement with MySQLi
     */
    protected function execute($query, $params = [], $types = '') {
        try {
            $connection = $this->getConnection();
            $stmt = $connection->prepare($query);
            
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $connection->error);
            }

            if (!empty($params)) {
                if (empty($types)) {
                    // Auto-detect parameter types
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
                }
                $stmt->bind_param($types, ...$params);
            }

            $result = $stmt->execute();
            
            if (!$result) {
                throw new Exception("Execute failed: " . $stmt->error);
            }

            return $stmt;

        } catch (Exception $e) {
            error_log("Database query error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Fetch all results from a query
     */
    protected function fetchAll($query, $params = [], $types = '') {
        $stmt = $this->execute($query, $params, $types);
        if (!$stmt) {
            return [];
        }

        $result = $stmt->get_result();
        if (!$result) {
            return [];
        }

        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }

        return $data;
    }

    /**
     * Fetch single result from a query
     */
    protected function fetch($query, $params = [], $types = '') {
        $stmt = $this->execute($query, $params, $types);
        if (!$stmt) {
            return null;
        }

        $result = $stmt->get_result();
        if (!$result) {
            return null;
        }

        return $result->fetch_assoc();
    }

    /**
     * Get the number of affected rows from the last operation
     */
    protected function affectedRows() {
        return $this->getConnection()->affected_rows;
    }

    /**
     * Get the last inserted ID
     */
    protected function lastInsertId() {
        return $this->getConnection()->insert_id;
    }
}