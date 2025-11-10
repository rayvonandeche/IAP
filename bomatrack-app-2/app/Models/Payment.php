<?php

require_once __DIR__ . '/BaseModel.php';

/**
 * Payment model
 *
 * Handles payment records for tenancies.
 */
class Payment extends BaseModel
{
    /**
     * Create a new payment
     */
    public function create(array $data): ?int
    {
        $sql = "INSERT INTO payments 
                (tenancy_id, amount, payment_type, payment_method, payment_reference, 
                 payment_date, due_date, status, notes, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";

        $stmt = $this->execute(
            $sql,
            [
                $data['tenancy_id'],
                $data['amount'],
                $data['payment_type'] ?? 'rent',
                $data['payment_method'] ?? 'cash',
                $data['payment_reference'] ?? null,
                $data['payment_date'],
                $data['due_date'] ?? null,
                $data['status'] ?? 'completed',
                $data['notes'] ?? null,
            ],
            'idsssssss'
        );

        if ($stmt) {
            return $this->lastInsertId();
        }

        return null;
    }

    /**
     * Get all payments for a property owner
     */
    public function getAllByOwner(int $ownerId): array
    {
        $sql = "SELECT p.*, 
                       t.first_name, t.last_name, t.email,
                       u.unit_number, 
                       pr.name AS property_name,
                       ten.rent_amount AS monthly_rent
                FROM payments p
                INNER JOIN tenancies ten ON p.tenancy_id = ten.id
                INNER JOIN tenants t ON ten.tenant_id = t.id
                INNER JOIN units u ON ten.unit_id = u.id
                INNER JOIN properties pr ON u.property_id = pr.id
                WHERE pr.owner_id = ?
                ORDER BY p.payment_date DESC, p.created_at DESC";
        
        return $this->fetchAll($sql, [$ownerId], 'i');
    }

    /**
     * Get payments for a specific tenancy
     */
    public function getByTenancy(int $tenancyId): array
    {
        $sql = "SELECT * FROM payments 
                WHERE tenancy_id = ? 
                ORDER BY payment_date DESC";
        
        return $this->fetchAll($sql, [$tenancyId], 'i');
    }

    /**
     * Get payment summary for owner
     */
    public function getSummary(int $ownerId): array
    {
        $sql = "SELECT 
                    COUNT(*) AS total_payments,
                    SUM(CASE WHEN p.status = 'completed' THEN p.amount ELSE 0 END) AS total_collected,
                    SUM(CASE WHEN p.status = 'pending' THEN p.amount ELSE 0 END) AS pending_amount,
                    SUM(CASE WHEN p.payment_type = 'rent' AND p.status = 'completed' THEN p.amount ELSE 0 END) AS rent_collected
                FROM payments p
                INNER JOIN tenancies ten ON p.tenancy_id = ten.id
                INNER JOIN units u ON ten.unit_id = u.id
                INNER JOIN properties pr ON u.property_id = pr.id
                WHERE pr.owner_id = ?";
        
        $result = $this->fetch($sql, [$ownerId], 'i') ?? [];
        
        return [
            'total_payments' => (int) ($result['total_payments'] ?? 0),
            'total_collected' => (float) ($result['total_collected'] ?? 0),
            'pending_amount' => (float) ($result['pending_amount'] ?? 0),
            'rent_collected' => (float) ($result['rent_collected'] ?? 0),
        ];
    }

    /**
     * Get balance for a tenancy (total rent due vs paid)
     */
    public function getTenancyBalance(int $tenancyId): array
    {
        // Get tenancy details
        $tenancySql = "SELECT rent_amount, start_date FROM tenancies WHERE id = ?";
        $tenancy = $this->fetch($tenancySql, [$tenancyId], 'i');
        
        if (!$tenancy) {
            return ['balance' => 0, 'total_paid' => 0, 'total_due' => 0];
        }

        // Calculate months since start
        $startDate = new DateTime($tenancy['start_date']);
        $now = new DateTime();
        $interval = $startDate->diff($now);
        $monthsElapsed = ($interval->y * 12) + $interval->m + 1; // +1 for current month

        $totalDue = $tenancy['rent_amount'] * $monthsElapsed;

        // Get total paid
        $paymentSql = "SELECT SUM(amount) as total_paid 
                       FROM payments 
                       WHERE tenancy_id = ? AND payment_type = 'rent' AND status = 'completed'";
        $paymentResult = $this->fetch($paymentSql, [$tenancyId], 'i');
        $totalPaid = (float) ($paymentResult['total_paid'] ?? 0);

        return [
            'total_due' => $totalDue,
            'total_paid' => $totalPaid,
            'balance' => $totalDue - $totalPaid,
            'months_elapsed' => $monthsElapsed,
        ];
    }

    /**
     * Find payment by ID
     */
    public function findById(int $paymentId): ?array
    {
        $sql = "SELECT * FROM payments WHERE id = ? LIMIT 1";
        return $this->fetch($sql, [$paymentId], 'i');
    }

    /**
     * Update payment
     */
    public function update(int $paymentId, array $data): bool
    {
        $sql = "UPDATE payments 
                SET amount = ?, payment_type = ?, payment_method = ?, 
                    payment_reference = ?, payment_date = ?, status = ?, notes = ?, updated_at = NOW()
                WHERE id = ?";

        $stmt = $this->execute(
            $sql,
            [
                $data['amount'],
                $data['payment_type'],
                $data['payment_method'],
                $data['payment_reference'] ?? null,
                $data['payment_date'],
                $data['status'],
                $data['notes'] ?? null,
                $paymentId,
            ],
            'dssssssi'
        );

        return (bool) $stmt;
    }

    /**
     * Delete payment
     */
    public function delete(int $paymentId): bool
    {
        $sql = "DELETE FROM payments WHERE id = ?";
        $stmt = $this->execute($sql, [$paymentId], 'i');

        return $stmt && $this->affectedRows() > 0;
    }
}
