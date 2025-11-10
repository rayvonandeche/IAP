<?php
$title = 'Dashboard';

$overview = $overview ?? [];
$recentPayments = $recentPayments ?? [];
$expiringLeases = $expiringLeases ?? [];
$units = $overview['units'] ?? [];
$financials = $overview['financials'] ?? [];
$tenancies = $overview['tenancies'] ?? [];

$displayName = 'Partner';
if (isset($user) && $user) {
    $firstName = method_exists($user, 'getFirstName') ? $user->getFirstName() : null;
    $displayName = $firstName ?: (method_exists($user, 'getUsername') ? $user->getUsername() : 'Partner');
}

$occupancyRate = isset($units['occupancy_rate']) ? (float) $units['occupancy_rate'] : 0.0;
$vacantUnits = isset($units['vacant']) ? (int) $units['vacant'] : 0;
$occupiedUnits = isset($units['occupied']) ? (int) $units['occupied'] : 0;
$maintenanceUnits = isset($units['maintenance']) ? (int) $units['maintenance'] : 0;
$totalUnits = isset($units['total']) ? (int) $units['total'] : 0;

$formatCurrency = function ($amount): string {
    $value = (float) $amount;
    return 'KES ' . number_format($value, 2);
};

$formatDate = function (?string $dateValue): string {
    if (empty($dateValue)) {
        return '—';
    }

    $timestamp = strtotime($dateValue);
    if ($timestamp === false) {
        return '—';
    }

    return date('M j, Y', $timestamp);
};
?>

<div class="dashboard-page">
    <header class="dashboard-header">
        <div>
            <h1>Welcome back, <?= htmlspecialchars($displayName); ?> 👋</h1>
            <p class="muted">Here's how your portfolio is looking today (<?= date('M j, Y'); ?>).</p>
        </div>
        <div class="dashboard-highlight">
            <span class="highlight-label">Active Leases</span>
            <span class="highlight-value"><?= number_format((int) ($tenancies['active'] ?? 0)); ?></span>
        </div>
    </header>

    <section class="metrics-grid">
        <article class="metric-card">
            <p class="metric-label">Properties</p>
            <p class="metric-value"><?= number_format((int) ($overview['properties'] ?? 0)); ?></p>
            <p class="metric-subtitle"><?= number_format($totalUnits); ?> total units</p>
        </article>

        <article class="metric-card">
            <p class="metric-label">Occupancy</p>
            <p class="metric-value"><?= number_format($occupancyRate, 1); ?><span class="metric-suffix">%</span></p>
            <div class="progress">
                <div class="progress-bar" style="width: <?= min(100, max(0, $occupancyRate)); ?>%"></div>
            </div>
            <p class="metric-subtitle"><?= number_format($occupiedUnits); ?> occupied · <?= number_format($vacantUnits); ?> vacant</p>
        </article>

        <article class="metric-card">
            <p class="metric-label">Monthly Rent Roll</p>
            <p class="metric-value"><?= $formatCurrency($tenancies['rent_due'] ?? 0); ?></p>
            <p class="metric-subtitle">Across <?= number_format((int) ($tenancies['total'] ?? 0)); ?> tenancy agreements</p>
        </article>

        <article class="metric-card">
            <p class="metric-label">Tenants</p>
            <p class="metric-value"><?= number_format((int) ($overview['tenants'] ?? 0)); ?></p>
            <p class="metric-subtitle">Including active & historic relationships</p>
        </article>
    </section>

    <section class="panels-grid">
        <article class="panel">
            <header class="panel-header">
                <h2>Unit Availability</h2>
            </header>
            <?php if ($totalUnits > 0): ?>
                <ul class="status-list">
                    <li><span class="status-dot occupied"></span>Occupied <strong><?= number_format($occupiedUnits); ?></strong></li>
                    <li><span class="status-dot vacant"></span>Vacant <strong><?= number_format($vacantUnits); ?></strong></li>
                    <li><span class="status-dot maintenance"></span>Under maintenance <strong><?= number_format($maintenanceUnits); ?></strong></li>
                </ul>
            <?php else: ?>
                <p class="empty-state">No units found yet. Add your first property to start tracking occupancy.</p>
            <?php endif; ?>
        </article>

        <article class="panel">
            <header class="panel-header">
                <h2>Collections Snapshot</h2>
            </header>
            <dl class="stat-list">
                <div>
                    <dt>Collected this month</dt>
                    <dd><?= $formatCurrency($financials['collected_this_month'] ?? 0); ?></dd>
                </div>
                <div>
                    <dt>Overdue balance</dt>
                    <dd class="text-warning"><?= $formatCurrency($financials['overdue_amount'] ?? 0); ?></dd>
                </div>
                <div>
                    <dt>Pending payments</dt>
                    <dd>
                        <?= $formatCurrency($financials['pending_amount'] ?? 0); ?>
                        <span class="muted">(<?= number_format((int) ($financials['pending_count'] ?? 0)); ?> invoices)</span>
                    </dd>
                </div>
            </dl>
        </article>
    </section>

    <section class="panels-grid columns-2">
        <article class="panel">
            <header class="panel-header">
                <h2>Leases nearing renewal</h2>
                <span class="panel-meta">Next 60 days</span>
            </header>
            <?php if (!empty($expiringLeases)): ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>End date</th>
                            <th>Tenant</th>
                            <th>Property / Unit</th>
                            <th>Days left</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($expiringLeases as $lease): ?>
                            <tr>
                                <td><?= htmlspecialchars($formatDate($lease['end_date'] ?? null)); ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($lease['tenant_name']); ?></strong>
                                    <?php if (!empty($lease['tenant_phone'])): ?>
                                        <div class="muted"><?= htmlspecialchars($lease['tenant_phone']); ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars($lease['property_name']); ?>
                                    <div class="muted">Unit <?= htmlspecialchars($lease['unit_number'] ?? '—'); ?></div>
                                </td>
                                <?php $daysLeft = isset($lease['days_remaining']) ? (int) $lease['days_remaining'] : null; ?>
                                <td>
                                    <?php if ($daysLeft !== null): ?>
                                        <span class="status-pill warning"><?= number_format($daysLeft); ?> days</span>
                                    <?php else: ?>
                                        <span class="muted">—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="empty-state">All good! No leases are expiring in the next two months.</p>
            <?php endif; ?>
        </article>

        <article class="panel">
            <header class="panel-header">
                <h2>Recent payments</h2>
                <span class="panel-meta">Last <?= number_format(count($recentPayments)); ?> records</span>
            </header>
            <?php if (!empty($recentPayments)): ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Tenant</th>
                            <th>Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentPayments as $payment): ?>
                            <tr>
                                <td><?= htmlspecialchars($formatDate($payment['payment_date'] ?? null)); ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($payment['tenant_name']); ?></strong>
                                    <div class="muted">
                                        <?= htmlspecialchars($payment['property_name']); ?> · Unit <?= htmlspecialchars($payment['unit_number'] ?? '—'); ?>
                                    </div>
                                </td>
                                <td><?= $formatCurrency($payment['amount']); ?></td>
                                <td><span class="status-pill <?= htmlspecialchars($payment['status']); ?>"><?= htmlspecialchars(ucwords(str_replace('_', ' ', $payment['status']))); ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="empty-state">No payment activity yet. Record a payment once your tenants start paying rent.</p>
            <?php endif; ?>
        </article>
    </section>
</div>