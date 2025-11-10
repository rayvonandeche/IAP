<?php

require_once __DIR__ . '/BaseModel.php';

/**
 * Tenant model
 *
 * Handles tenant records with contact and emergency information.
 */
class Tenant extends BaseModel
{
    /**
     * Fetch all tenants for a specific property owner
     */
    public function getAllByOwner(int $ownerId): array
    {
        $sql = "SELECT DISTINCT
                    t.id,
                    t.user_id,
                    t.first_name,
                    t.last_name,
                    t.email,
                    t.phone,
                    t.national_id,
                    t.emergency_contact_name,
                    t.emergency_contact_phone,
                    t.employment_info,
                    t.created_at,
                    t.updated_at,
                    COUNT(DISTINCT ten.id) AS tenancy_count,
                    MAX(CASE WHEN ten.status = 'active' THEN 1 ELSE 0 END) AS has_active_tenancy
                FROM tenants t
                LEFT JOIN tenancies ten ON t.id = ten.tenant_id
                LEFT JOIN units u ON ten.unit_id = u.id
                LEFT JOIN properties p ON u.property_id = p.id
                WHERE p.owner_id = ? OR t.id NOT IN (
                    SELECT DISTINCT tenant_id FROM tenancies
                )
                GROUP BY t.id
                ORDER BY t.created_at DESC";

        return $this->fetchAll($sql, [$ownerId], 'i');
    }

    /**
     * Find a tenant by ID
     */
    public function findById(int $tenantId): ?array
    {
        $sql = "SELECT * FROM tenants WHERE id = ? LIMIT 1";
        return $this->fetch($sql, [$tenantId], 'i');
    }

    /**
     * Create a new tenant
     */
    public function create(array $data): ?int
    {
        $sql = "INSERT INTO tenants 
                (user_id, first_name, last_name, email, phone, national_id, 
                 emergency_contact_name, emergency_contact_phone, employment_info, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";

        $stmt = $this->execute(
            $sql,
            [
                $data['user_id'] ?? null,
                $data['first_name'],
                $data['last_name'],
                $data['email'],
                $data['phone'],
                $data['national_id'] ?? null,
                $data['emergency_contact_name'] ?? null,
                $data['emergency_contact_phone'] ?? null,
                $data['employment_info'] ?? null,
            ],
            'issssssss'
        );

        if ($stmt) {
            return $this->lastInsertId();
        }

        return null;
    }

    /**
     * Update a tenant
     */
    public function update(int $tenantId, array $data): bool
    {
        $sql = "UPDATE tenants 
                SET first_name = ?, last_name = ?, email = ?, phone = ?, national_id = ?,
                    emergency_contact_name = ?, emergency_contact_phone = ?, employment_info = ?, updated_at = NOW()
                WHERE id = ?";

        $stmt = $this->execute(
            $sql,
            [
                $data['first_name'],
                $data['last_name'],
                $data['email'],
                $data['phone'],
                $data['national_id'] ?? null,
                $data['emergency_contact_name'] ?? null,
                $data['emergency_contact_phone'] ?? null,
                $data['employment_info'] ?? null,
                $tenantId,
            ],
            'ssssssssi'
        );

        return (bool) $stmt;
    }

    /**
     * Delete a tenant
     */
    public function delete(int $tenantId): bool
    {
        $sql = "DELETE FROM tenants WHERE id = ?";
        $stmt = $this->execute($sql, [$tenantId], 'i');

        return $stmt && $this->affectedRows() > 0;
    }

    /**
     * Find tenant by email
     */
    public function findByEmail(string $email): ?array
    {
        $sql = "SELECT * FROM tenants WHERE email = ? LIMIT 1";
        return $this->fetch($sql, [$email], 's');
    }

    /**
     * Get tenants summary
     */
    public function getSummary(int $ownerId): array
    {
        $sql = "SELECT 
                    COUNT(DISTINCT t.id) AS total_tenants,
                    SUM(CASE WHEN ten.status = 'active' THEN 1 ELSE 0 END) AS active_tenants
                FROM tenants t
                LEFT JOIN tenancies ten ON t.id = ten.tenant_id
                LEFT JOIN units u ON ten.unit_id = u.id
                LEFT JOIN properties p ON u.property_id = p.id
                WHERE p.owner_id = ?";

        $summary = $this->fetch($sql, [$ownerId], 'i') ?? [];

        return [
            'total_tenants' => (int) ($summary['total_tenants'] ?? 0),
            'active_tenants' => (int) ($summary['active_tenants'] ?? 0),
        ];
    }
}
