<?php $title = 'Payments'; ?>
<?php
$formatCurrency = function ($amount): string {
    return 'KES ' . number_format((float) $amount, 2);
};

$payments = $payments ?? [];
$summary = $summary ?? [];
?>

<section class="dashboard-page">
    <header class="dashboard-header">
        <div>
            <p class="muted" style="color: rgba(255,255,255,0.7); margin: 0 0 4px 0;">Payment Records</p>
            <h1>Payments</h1>
            <p style="margin: 0; color: rgba(255,255,255,0.8);">
                Track rent payments and balances for all tenants.
            </p>
        </div>
        <div class="dashboard-highlight">
            <span class="highlight-label">Total Collected</span>
            <span class="highlight-value"><?= $formatCurrency($summary['total_collected'] ?? 0); ?></span>
        </div>
    </header>

    <?php if (isset($_GET['created'])): ?>
        <div class="message success">Payment recorded successfully!</div>
    <?php endif; ?>
    <?php if (isset($_GET['deleted'])): ?>
        <div class="message success">Payment deleted successfully!</div>
    <?php endif; ?>
    <?php if (isset($_GET['error'])): ?>
        <div class="message error">An error occurred. Please try again.</div>
    <?php endif; ?>

    <section class="metrics-grid">
        <article class="metric-card">
            <p class="metric-label">Total Collected</p>
            <p class="metric-value">
                <?= $formatCurrency($summary['total_collected'] ?? 0); ?>
            </p>
        </article>
        <article class="metric-card">
            <p class="metric-label">Rent Collected</p>
            <p class="metric-value">
                <?= $formatCurrency($summary['rent_collected'] ?? 0); ?>
            </p>
        </article>
        <article class="metric-card">
            <p class="metric-label">Total Payments</p>
            <p class="metric-value">
                <?= number_format((int) ($summary['total_payments'] ?? 0)); ?>
                <span class="metric-suffix">records</span>
            </p>
        </article>
        <article class="metric-card">
            <p class="metric-label">Pending</p>
            <p class="metric-value">
                <?= $formatCurrency($summary['pending_amount'] ?? 0); ?>
            </p>
        </article>
    </section>

    <section class="panel">
        <header class="panel-header">
            <h2>Payment Records</h2>
            <a href="/payments/create" class="button primary">Record Payment</a>
        </header>

        <?php if (empty($payments)): ?>
            <div class="empty-state">
                <h3>No payments yet</h3>
                <p>Start recording rent payments and other transactions here.</p>
                <a href="/payments/create" class="button primary" style="margin-top: 16px;">Record First Payment</a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Tenant</th>
                            <th>Unit</th>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($payments as $payment): ?>
                            <tr>
                                <td>
                                    <?= date('M d, Y', strtotime($payment['payment_date'])); ?>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars(trim($payment['first_name'] . ' ' . $payment['last_name'])); ?></strong>
                                    <div class="muted" style="font-size: 0.85rem;"><?= htmlspecialchars($payment['email']); ?></div>
                                </td>
                                <td>
                                    <div><?= htmlspecialchars($payment['property_name']); ?></div>
                                    <div class="muted" style="font-size: 0.85rem;">Unit <?= htmlspecialchars($payment['unit_number']); ?></div>
                                </td>
                                <td>
                                    <span class="property-type"><?= htmlspecialchars(ucfirst($payment['payment_type'])); ?></span>
                                </td>
                                <td>
                                    <strong><?= $formatCurrency($payment['amount']); ?></strong>
                                </td>
                                <td>
                                    <?= htmlspecialchars(ucfirst($payment['payment_method'])); ?>
                                    <?php if (!empty($payment['payment_reference'])): ?>
                                        <div class="muted" style="font-size: 0.85rem;"><?= htmlspecialchars($payment['payment_reference']); ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $statusClass = $payment['status'] === 'completed' ? 'completed' : 'pending';
                                    ?>
                                    <span class="status-pill <?= $statusClass; ?>"><?= htmlspecialchars(ucfirst($payment['status'])); ?></span>
                                </td>
                                <td>
                                    <form method="POST" action="/payments/delete" style="display: inline;" onsubmit="return confirm('Delete this payment record?');">
                                        <input type="hidden" name="id" value="<?= (int) $payment['id']; ?>">
                                        <button type="submit" class="button button-danger" style="font-size: 0.85rem; padding: 6px 12px;">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
</section>
