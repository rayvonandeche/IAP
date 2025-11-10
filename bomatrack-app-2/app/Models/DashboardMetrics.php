<?php

require_once __DIR__ . '/BaseModel.php';

/**
 * DashboardMetrics
 *
 * Provides aggregate statistics for the owner dashboard including
 * property, unit, tenancy, tenant and payment insights.
 */
class DashboardMetrics extends BaseModel
{
    /**
     * Build high-level overview metrics for a property owner
     */
    public function getOverview(int $ownerId): array
    {
        $overview = [
            'properties' => 0,
            'units' => [
                'total' => 0,
                'occupied' => 0,
                'vacant' => 0,
                'maintenance' => 0,
                'occupancy_rate' => 0.0,
            ],
            'tenancies' => [
                'total' => 0,
                'active' => 0,
                'rent_due' => 0.0,
            ],
            'tenants' => 0,
            'financials' => [
                'collected_this_month' => 0.0,
                'overdue_amount' => 0.0,
                'pending_amount' => 0.0,
                'pending_count' => 0,
            ],
        ];

        // Property count
        $propertiesData = $this->fetch(
            "SELECT COUNT(*) AS total_properties FROM properties WHERE owner_id = ?",
            [$ownerId],
            'i'
        );
        if ($propertiesData) {
            $overview['properties'] = (int) ($propertiesData['total_properties'] ?? 0);
        }

        // Units breakdown
        $unitsData = $this->fetch(
            "SELECT 
                COUNT(*) AS total_units,
                SUM(CASE WHEN u.status = 'occupied' THEN 1 ELSE 0 END) AS occupied_units,
                SUM(CASE WHEN u.status = 'vacant' THEN 1 ELSE 0 END) AS vacant_units,
                SUM(CASE WHEN u.status = 'maintenance' THEN 1 ELSE 0 END) AS maintenance_units
            FROM units u
            INNER JOIN properties p ON u.property_id = p.id
            WHERE p.owner_id = ?",
            [$ownerId],
            'i'
        );
        if ($unitsData) {
            $overview['units']['total'] = (int) ($unitsData['total_units'] ?? 0);
            $overview['units']['occupied'] = (int) ($unitsData['occupied_units'] ?? 0);
            $overview['units']['vacant'] = (int) ($unitsData['vacant_units'] ?? 0);
            $overview['units']['maintenance'] = (int) ($unitsData['maintenance_units'] ?? 0);

            $totalUnits = max(1, $overview['units']['total']);
            $overview['units']['occupancy_rate'] = $overview['units']['total'] > 0
                ? round(($overview['units']['occupied'] / $totalUnits) * 100, 1)
                : 0.0;
        }

        // Tenancies summary
        $tenanciesData = $this->fetch(
            "SELECT 
                COUNT(*) AS total_tenancies,
                SUM(CASE WHEN t.status = 'active' THEN 1 ELSE 0 END) AS active_tenancies,
                SUM(CASE WHEN t.status = 'active' THEN t.rent_amount ELSE 0 END) AS active_rent
            FROM tenancies t
            INNER JOIN units u ON t.unit_id = u.id
            INNER JOIN properties p ON u.property_id = p.id
            WHERE p.owner_id = ?",
            [$ownerId],
            'i'
        );
        if ($tenanciesData) {
            $overview['tenancies']['total'] = (int) ($tenanciesData['total_tenancies'] ?? 0);
            $overview['tenancies']['active'] = (int) ($tenanciesData['active_tenancies'] ?? 0);
            $overview['tenancies']['rent_due'] = (float) ($tenanciesData['active_rent'] ?? 0);
        }

        // Unique tenants under the owner
        $tenantData = $this->fetch(
            "SELECT COUNT(DISTINCT te.id) AS total_tenants
            FROM tenants te
            INNER JOIN tenancies t ON te.id = t.tenant_id
            INNER JOIN units u ON t.unit_id = u.id
            INNER JOIN properties p ON u.property_id = p.id
            WHERE p.owner_id = ?",
            [$ownerId],
            'i'
        );
        if ($tenantData) {
            $overview['tenants'] = (int) ($tenantData['total_tenants'] ?? 0);
        }

        // Payments summary
        $paymentsData = $this->fetch(
            "SELECT 
                SUM(CASE WHEN pay.status = 'completed' 
                    AND YEAR(pay.payment_date) = YEAR(CURDATE())
                    AND MONTH(pay.payment_date) = MONTH(CURDATE())
                    THEN pay.amount ELSE 0 END) AS collected_this_month,
                SUM(CASE WHEN pay.status != 'completed' 
                    AND pay.due_date IS NOT NULL 
                    AND pay.due_date < CURDATE()
                    THEN pay.amount ELSE 0 END) AS overdue_amount,
                SUM(CASE WHEN pay.status IN ('pending','failed') THEN pay.amount ELSE 0 END) AS pending_amount,
                SUM(CASE WHEN pay.status IN ('pending','failed') THEN 1 ELSE 0 END) AS pending_count
            FROM payments pay
            INNER JOIN tenancies t ON pay.tenancy_id = t.id
            INNER JOIN units u ON t.unit_id = u.id
            INNER JOIN properties p ON u.property_id = p.id
            WHERE p.owner_id = ?",
            [$ownerId],
            'i'
        );
        if ($paymentsData) {
            $overview['financials']['collected_this_month'] = (float) ($paymentsData['collected_this_month'] ?? 0);
            $overview['financials']['overdue_amount'] = (float) ($paymentsData['overdue_amount'] ?? 0);
            $overview['financials']['pending_amount'] = (float) ($paymentsData['pending_amount'] ?? 0);
            $overview['financials']['pending_count'] = (int) ($paymentsData['pending_count'] ?? 0);
        }

        return $overview;
    }

    /**
     * Return the most recent payment activities for the owner
     */
    public function getRecentPayments(int $ownerId, int $limit = 5): array
    {
        $payments = $this->fetchAll(
            "SELECT 
                pay.id,
                pay.amount,
                pay.payment_date,
                pay.due_date,
                pay.status,
                pay.payment_type,
                pay.payment_method,
                pay.payment_reference,
                ten.first_name,
                ten.last_name,
                p.name AS property_name,
                u.unit_number
            FROM payments pay
            INNER JOIN tenancies t ON pay.tenancy_id = t.id
            INNER JOIN tenants ten ON t.tenant_id = ten.id
            INNER JOIN units u ON t.unit_id = u.id
            INNER JOIN properties p ON u.property_id = p.id
            WHERE p.owner_id = ?
            ORDER BY pay.payment_date DESC, pay.created_at DESC
            LIMIT ?",
            [$ownerId, $limit],
            'ii'
        );

        return array_map(function ($row) {
            return [
                'id' => (int) ($row['id'] ?? 0),
                'amount' => isset($row['amount']) ? (float) $row['amount'] : 0.0,
                'payment_date' => $row['payment_date'] ?? null,
                'due_date' => $row['due_date'] ?? null,
                'status' => $row['status'] ?? 'pending',
                'payment_type' => $row['payment_type'] ?? 'rent',
                'payment_method' => $row['payment_method'] ?? 'cash',
                'payment_reference' => $row['payment_reference'] ?? null,
                'tenant_name' => trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? '')),
                'property_name' => $row['property_name'] ?? '',
                'unit_number' => $row['unit_number'] ?? '',
            ];
        }, $payments ?? []);
    }

    /**
     * Fetch leases that are approaching their end date
     */
    public function getExpiringLeases(int $ownerId, int $days = 60): array
    {
        $endThreshold = (new DateTime("+$days days"))->format('Y-m-d');

        $leases = $this->fetchAll(
            "SELECT 
                t.id,
                t.end_date,
                t.rent_amount,
                ten.first_name,
                ten.last_name,
                ten.phone,
                p.name AS property_name,
                u.unit_number,
                DATEDIFF(t.end_date, CURDATE()) AS days_remaining
            FROM tenancies t
            INNER JOIN tenants ten ON t.tenant_id = ten.id
            INNER JOIN units u ON t.unit_id = u.id
            INNER JOIN properties p ON u.property_id = p.id
            WHERE p.owner_id = ?
              AND t.status = 'active'
              AND t.end_date IS NOT NULL
              AND t.end_date BETWEEN CURDATE() AND ?
            ORDER BY t.end_date ASC
            LIMIT 5",
            [$ownerId, $endThreshold],
            'is'
        );

        return array_map(function ($row) {
            return [
                'id' => (int) ($row['id'] ?? 0),
                'end_date' => $row['end_date'] ?? null,
                'rent_amount' => isset($row['rent_amount']) ? (float) $row['rent_amount'] : 0.0,
                'tenant_name' => trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? '')),
                'tenant_phone' => $row['phone'] ?? null,
                'property_name' => $row['property_name'] ?? '',
                'unit_number' => $row['unit_number'] ?? '',
                'days_remaining' => isset($row['days_remaining']) ? (int) $row['days_remaining'] : null,
            ];
        }, $leases ?? []);
    }
}
