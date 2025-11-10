<?php

require_once __DIR__ . '/BaseModel.php';

/**
 * Unit model
 *
 * Handles unit records tied to properties.
 */
class Unit extends BaseModel
{
    /**
     * Fetch all units for a specific property
     */
    public function getAllByProperty(int $propertyId): array
    {
        $sql = "SELECT * FROM units WHERE property_id = ? ORDER BY unit_number ASC";
        return $this->fetchAll($sql, [$propertyId], 'i');
    }

    /**
     * Create a single unit
     */
    public function create(array $data): ?int
    {
        $sql = "INSERT INTO units 
                (property_id, unit_number, bedrooms, bathrooms, square_feet, rent_amount, deposit_amount, status, description, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";

        $stmt = $this->execute(
            $sql,
            [
                $data['property_id'],
                $data['unit_number'],
                $data['bedrooms'] ?? 1,
                $data['bathrooms'] ?? 1.0,
                $data['square_feet'] ?? null,
                $data['rent_amount'],
                $data['deposit_amount'] ?? 0.00,
                $data['status'] ?? 'vacant',
                $data['description'] ?? null,
            ],
            'isiddddss'
        );

        if ($stmt) {
            return $this->lastInsertId();
        }

        return null;
    }

    /**
     * Bulk create units (used when creating property with floors/units)
     */
    public function bulkCreate(array $units): bool
    {
        if (empty($units)) {
            return false;
        }

        $connection = $this->getConnection();
        $connection->begin_transaction();

        try {
            foreach ($units as $unitData) {
                $result = $this->create($unitData);
                if (!$result) {
                    throw new Exception("Failed to create unit: " . ($unitData['unit_number'] ?? 'unknown'));
                }
            }

            $connection->commit();
            return true;
        } catch (Exception $e) {
            $connection->rollback();
            error_log("Bulk unit creation failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update a unit
     */
    public function update(int $unitId, array $data): bool
    {
        // If only status is being updated, use updateStatus instead
        if (count($data) === 1 && isset($data['status'])) {
            return $this->updateStatus($unitId, $data['status']);
        }

        $sql = "UPDATE units 
                SET unit_number = ?, bedrooms = ?, bathrooms = ?, square_feet = ?, 
                    rent_amount = ?, deposit_amount = ?, status = ?, description = ?, updated_at = NOW()
                WHERE id = ?";

        $stmt = $this->execute(
            $sql,
            [
                $data['unit_number'],
                $data['bedrooms'] ?? 1,
                $data['bathrooms'] ?? 1.0,
                $data['square_feet'] ?? null,
                $data['rent_amount'],
                $data['deposit_amount'] ?? 0.00,
                $data['status'] ?? 'vacant',
                $data['description'] ?? null,
                $unitId,
            ],
            'siddddss i'
        );

        return (bool) $stmt;
    }

    /**
     * Update just the status of a unit
     */
    public function updateStatus(int $unitId, string $status): bool
    {
        $sql = "UPDATE units SET status = ?, updated_at = NOW() WHERE id = ?";
        $stmt = $this->execute($sql, [$status, $unitId], 'si');
        return (bool) $stmt;
    }

    /**
     * Delete a unit
     */
    public function delete(int $unitId): bool
    {
        $sql = "DELETE FROM units WHERE id = ?";
        $stmt = $this->execute($sql, [$unitId], 'i');

        return $stmt && $this->affectedRows() > 0;
    }

    /**
     * Find a unit by ID
     */
    public function findById(int $unitId): ?array
    {
        $sql = "SELECT * FROM units WHERE id = ? LIMIT 1";
        return $this->fetch($sql, [$unitId], 'i');
    }

    /**
     * Get all vacant units for a property owner
     */
    public function getVacantUnitsByOwner(int $ownerId): array
    {
        $sql = "SELECT u.*, p.name AS property_name, p.address AS property_address
                FROM units u
                INNER JOIN properties p ON u.property_id = p.id
                WHERE p.owner_id = ? AND u.status = 'vacant'
                ORDER BY p.name ASC, u.unit_number ASC";
        
        return $this->fetchAll($sql, [$ownerId], 'i');
    }
}
