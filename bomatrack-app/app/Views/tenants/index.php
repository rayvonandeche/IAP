<?php $title = 'Tenants Management'; ?>
<?php
$tenants = $tenants ?? [];
$summary = $summary ?? [];
?>

<section class="dashboard-page">
    <header class="dashboard-header">
        <div>
            <p class="muted" style="color: rgba(255,255,255,0.7); margin: 0 0 4px 0;">Tenant Database</p>
            <h1>Tenants Management</h1>
            <p style="margin: 0; color: rgba(255,255,255,0.8);">
                Manage tenant profiles, contact info, and emergency details.
            </p>
        </div>
        <div class="dashboard-highlight">
            <span class="highlight-label">Total Tenants</span>
            <span class="highlight-value"><?= number_format((int) ($summary['total_tenants'] ?? 0)); ?></span>
        </div>
    </header>

    <?php if (isset($_GET['created'])): ?>
        <div class="message success">Tenant created successfully!</div>
    <?php endif; ?>
    <?php if (isset($_GET['updated'])): ?>
        <div class="message success">Tenant updated successfully!</div>
    <?php endif; ?>
    <?php if (isset($_GET['deleted'])): ?>
        <div class="message success">Tenant deleted successfully!</div>
    <?php endif; ?>
    <?php if (isset($_GET['error'])): ?>
        <div class="message error">An error occurred. Please try again.</div>
    <?php endif; ?>

    <section class="metrics-grid">
        <article class="metric-card">
            <p class="metric-label">Active Tenancies</p>
            <p class="metric-value">
                <?= number_format((int) ($summary['active_tenants'] ?? 0)); ?>
                <span class="metric-suffix">current</span>
            </p>
        </article>
        <article class="metric-card">
            <p class="metric-label">Tenant Database</p>
            <p class="metric-value">
                <?= number_format((int) ($summary['total_tenants'] ?? 0)); ?>
                <span class="metric-suffix">records</span>
            </p>
        </article>
    </section>

    <section class="panel">
        <header class="panel-header">
            <h2>Tenant Records</h2>
            <div style="display: flex; gap: 12px;">
                <a href="/tenants/create" class="button primary">Add Tenant</a>
            </div>
        </header>

        <?php if (empty($tenants)): ?>
            <div class="empty-state">
                <h3>No tenants yet</h3>
                <p>Add tenant profiles to track contact information and emergency details.</p>
                <a href="/tenants/create" class="button primary" style="margin-top: 16px;">Add Your First Tenant</a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Contact</th>
                            <th>National ID</th>
                            <th>Emergency Contact</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tenants as $tenant): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars(trim($tenant['first_name'] . ' ' . $tenant['last_name'])); ?></strong>
                                </td>
                                <td>
                                    <div><?= htmlspecialchars($tenant['email']); ?></div>
                                    <div class="muted" style="font-size: 0.85rem;"><?= htmlspecialchars($tenant['phone']); ?></div>
                                </td>
                                <td>
                                    <?= htmlspecialchars($tenant['national_id'] ?? '—'); ?>
                                </td>
                                <td>
                                    <?php if (!empty($tenant['emergency_contact_name'])): ?>
                                        <div><?= htmlspecialchars($tenant['emergency_contact_name']); ?></div>
                                        <?php if (!empty($tenant['emergency_contact_phone'])): ?>
                                            <div class="muted" style="font-size: 0.85rem;"><?= htmlspecialchars($tenant['emergency_contact_phone']); ?></div>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ((int) ($tenant['has_active_tenancy'] ?? 0) === 1): ?>
                                        <span class="status-pill completed">Active</span>
                                    <?php else: ?>
                                        <span class="status-pill">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div style="display: flex; gap: 8px;">
                                        <a href="/tenants/edit?id=<?= (int) $tenant['id']; ?>" class="button" style="font-size: 0.85rem; padding: 6px 12px;">Edit</a>
                                        <form method="POST" action="/tenants/delete" style="display: inline;" onsubmit="return confirm('Delete this tenant?');">
                                            <input type="hidden" name="id" value="<?= (int) $tenant['id']; ?>">
                                            <button type="submit" class="button button-danger" style="font-size: 0.85rem; padding: 6px 12px;">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
</section>
