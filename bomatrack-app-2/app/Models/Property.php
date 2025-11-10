<?php

require_once __DIR__ . '/BaseModel.php';

/**
 * Property model
 *
 * Handles property records as well as aggregated stats for units tied to a property
 * for the currently authenticated owner.
 */
class Property extends BaseModel
{
    /**
     * Fetch every property owned by the given owner including unit stats.
     */
    public function getAllByOwner(int $ownerId): array
    {
        $sql = "SELECT 
                    p.id,
                    p.name,
                    p.address,
                    p.city,
                    p.state,
                    p.country,
                    p.property_type,
                    p.total_units,
                    p.description,
                    p.created_at,
                    p.updated_at,
                    COUNT(u.id) AS units_count,
                    COALESCE(SUM(CASE WHEN u.status = 'occupied' THEN 1 ELSE 0 END), 0) AS occupied_units,
                    COALESCE(SUM(CASE WHEN u.status = 'vacant' THEN 1 ELSE 0 END), 0) AS vacant_units,
                    COALESCE(SUM(CASE WHEN u.status = 'maintenance' THEN 1 ELSE 0 END), 0) AS maintenance_units,
                    COALESCE(SUM(u.rent_amount), 0) AS total_rent_roll,
                    COALESCE(AVG(u.rent_amount), 0) AS avg_rent
                FROM properties p
                LEFT JOIN units u ON u.property_id = p.id
                WHERE p.owner_id = ?
                GROUP BY p.id
                ORDER BY p.created_at DESC";

        return $this->fetchAll($sql, [$ownerId], 'i');
    }

    /**
     * Quick summary figures for dashboard style counts
     */
    public function getSummary(int $ownerId): array
    {
        $sql = "SELECT 
                    COUNT(*) AS total_properties,
                    COALESCE(SUM(total_units), 0) AS declared_units
                FROM properties
                WHERE owner_id = ?";

        $summary = $this->fetch($sql, [$ownerId], 'i') ?? [];

        return [
            'total_properties' => (int) ($summary['total_properties'] ?? 0),
            'declared_units' => (int) ($summary['declared_units'] ?? 0),
        ];
    }

    /**
     * Find a property by ID and verify ownership
     */
    public function findById(int $propertyId, int $ownerId): ?array
    {
        $sql = "SELECT * FROM properties WHERE id = ? AND owner_id = ? LIMIT 1";
        return $this->fetch($sql, [$propertyId, $ownerId], 'ii');
    }

    /**
     * Create a new property
     */
    public function create(array $data): ?int
    {
        $sql = "INSERT INTO properties 
                (owner_id, name, address, city, state, postal_code, country, property_type, total_units, description, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";

        $stmt = $this->execute(
            $sql,
            [
                $data['owner_id'],
                $data['name'],
                $data['address'],
                $data['city'],
                $data['state'],
                $data['postal_code'],
                $data['country'] ?? 'Kenya',
                $data['property_type'] ?? 'apartment',
                $data['total_units'] ?? 1,
                $data['description'] ?? null,
            ],
            'isssssssis'
        );

        if ($stmt) {
            return $this->lastInsertId();
        }

        return null;
    }

    /**
     * Update an existing property
     */
    public function update(int $propertyId, int $ownerId, array $data): bool
    {
        $sql = "UPDATE properties 
                SET name = ?, address = ?, city = ?, state = ?, postal_code = ?, 
                    country = ?, property_type = ?, total_units = ?, description = ?, updated_at = NOW()
                WHERE id = ? AND owner_id = ?";

        $stmt = $this->execute(
            $sql,
            [
                $data['name'],
                $data['address'],
                $data['city'],
                $data['state'],
                $data['postal_code'],
                $data['country'] ?? 'Kenya',
                $data['property_type'] ?? 'apartment',
                $data['total_units'] ?? 1,
                $data['description'] ?? null,
                $propertyId,
                $ownerId,
            ],
            'sssssssisii'
        );

        return (bool) $stmt;
    }

    /**
     * Delete a property (and cascade delete units if needed)
     */
    public function delete(int $propertyId, int $ownerId): bool
    {
        // First delete all units associated with this property
        $deleteUnits = "DELETE FROM units WHERE property_id = ?";
        $this->execute($deleteUnits, [$propertyId], 'i');

        // Then delete the property
        $sql = "DELETE FROM properties WHERE id = ? AND owner_id = ?";
        $stmt = $this->execute($sql, [$propertyId, $ownerId], 'ii');

        return $stmt && $this->affectedRows() > 0;
    }
}
