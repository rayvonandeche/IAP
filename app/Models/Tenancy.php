<?php

require_once __DIR__ . '/BaseModel.php';

/**
 * Tenancy model
 *
 * Handles the relationship between tenants and units (lease agreements).
 */
class Tenancy extends BaseModel
{
    /**
     * Create a new tenancy (lease agreement)
     */
    public function create(array $data): ?int
    {
        $sql = "INSERT INTO tenancies 
                (tenant_id, unit_id, start_date, end_date, rent_amount, deposit_paid, status, lease_terms, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";

        $stmt = $this->execute(
            $sql,
            [
                $data['tenant_id'],
                $data['unit_id'],
                $data['start_date'],
                $data['end_date'] ?? null,
                $data['rent_amount'],
                $data['deposit_paid'] ?? 0.00,
                $data['status'] ?? 'active',
                $data['lease_terms'] ?? null,
            ],
            'iissddss'
        );

        if ($stmt) {
            return $this->lastInsertId();
        }

        return null;
    }

    /**
     * Update a tenancy
     */
    public function update(int $tenancyId, array $data): bool
    {
        $sql = "UPDATE tenancies 
                SET start_date = ?, end_date = ?, rent_amount = ?, deposit_paid = ?, 
                    status = ?, lease_terms = ?, updated_at = NOW()
                WHERE id = ?";

        $stmt = $this->execute(
            $sql,
            [
                $data['start_date'],
                $data['end_date'] ?? null,
                $data['rent_amount'],
                $data['deposit_paid'] ?? 0.00,
                $data['status'] ?? 'active',
                $data['lease_terms'] ?? null,
                $tenancyId,
            ],
            'ssddssi'
        );

        return (bool) $stmt;
    }

    /**
     * Find a tenancy by ID
     */
    public function findById(int $tenancyId): ?array
    {
        $sql = "SELECT * FROM tenancies WHERE id = ? LIMIT 1";
        return $this->fetch($sql, [$tenancyId], 'i');
    }

    /**
     * Get all tenancies for a specific tenant
     */
    public function getByTenant(int $tenantId): array
    {
        $sql = "SELECT t.*, u.unit_number, u.bedrooms, u.bathrooms, 
                       p.name AS property_name, p.address AS property_address
                FROM tenancies t
                INNER JOIN units u ON t.unit_id = u.id
                INNER JOIN properties p ON u.property_id = p.id
                WHERE t.tenant_id = ?
                ORDER BY t.start_date DESC";
        
        return $this->fetchAll($sql, [$tenantId], 'i');
    }

    /**
     * Get all active tenancies for a specific unit
     */
    public function getActiveByUnit(int $unitId): ?array
    {
        $sql = "SELECT * FROM tenancies WHERE unit_id = ? AND status = 'active' LIMIT 1";
        return $this->fetch($sql, [$unitId], 'i');
    }

    /**
     * End a tenancy
     */
    public function endTenancy(int $tenancyId, string $endDate): bool
    {
        $sql = "UPDATE tenancies 
                SET status = 'ended', end_date = ?, updated_at = NOW()
                WHERE id = ?";

        $stmt = $this->execute($sql, [$endDate, $tenancyId], 'si');
        return (bool) $stmt;
    }

    /**
     * Delete a tenancy
     */
    public function delete(int $tenancyId): bool
    {
        $sql = "DELETE FROM tenancies WHERE id = ?";
        $stmt = $this->execute($sql, [$tenancyId], 'i');

        return $stmt && $this->affectedRows() > 0;
    }
}
