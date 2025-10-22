<?php $title = 'Units Management'; ?>
<?php
$formatCurrency = function ($amount): string {
    return 'KES ' . number_format((float) $amount, 2);
};

$units = $units ?? [];
$summary = $summary ?? [];
$properties = $properties ?? [];
$selectedProperty = $selectedProperty ?? null;
$selectedPropertyId = $selectedPropertyId ?? 0;
?>

<section class="dashboard-page">
    <header class="dashboard-header">
        <div>
            <p class="muted" style="color: rgba(255,255,255,0.7); margin: 0 0 4px 0;">
                <?= $selectedProperty ? htmlspecialchars($selectedProperty['name']) : 'All Properties'; ?>
            </p>
            <h1>Units Management</h1>
            <p style="margin: 0; color: rgba(255,255,255,0.8);">
                <?= $selectedProperty ? 'Manage units for this property' : 'View and manage units across all properties'; ?>
            </p>
        </div>
        <div class="dashboard-highlight">
            <span class="highlight-label">Total Units</span>
            <span class="highlight-value"><?= number_format((int) ($summary['total'] ?? 0)); ?></span>
        </div>
    </header>

    <?php if (isset($_GET['created'])): ?>
        <div class="message success">Unit created successfully!</div>
    <?php endif; ?>
    <?php if (isset($_GET['updated'])): ?>
        <div class="message success">Unit updated successfully!</div>
    <?php endif; ?>
    <?php if (isset($_GET['deleted'])): ?>
        <div class="message success">Unit deleted successfully!</div>
    <?php endif; ?>
    <?php if (isset($_GET['error'])): ?>
        <div class="message error">An error occurred. Please try again.</div>
    <?php endif; ?>

    <section class="metrics-grid">
        <article class="metric-card">
            <p class="metric-label">Vacant Units</p>
            <p class="metric-value">
                <?= number_format((int) ($summary['vacant'] ?? 0)); ?>
                <span class="metric-suffix">available</span>
            </p>
        </article>
        <article class="metric-card">
            <p class="metric-label">Occupied Units</p>
            <p class="metric-value">
                <?= number_format((int) ($summary['occupied'] ?? 0)); ?>
                <span class="metric-suffix">rented</span>
            </p>
        </article>
        <article class="metric-card">
            <p class="metric-label">Under Maintenance</p>
            <p class="metric-value">
                <?= number_format((int) ($summary['maintenance'] ?? 0)); ?>
            </p>
        </article>
        <article class="metric-card">
            <p class="metric-label">Avg Rent</p>
            <p class="metric-value">
                <?= $formatCurrency($summary['avg_rent'] ?? 0); ?>
            </p>
        </article>
    </section>

    <section class="panel">
        <header class="panel-header">
            <h2>Filter by Property</h2>
        </header>
        <div class="property-filter">
            <a href="/units" class="filter-tag <?= $selectedPropertyId === 0 ? 'active' : ''; ?>">
                All Properties (<?= count($properties); ?>)
            </a>
            <?php foreach ($properties as $prop): ?>
                <a href="/units?property_id=<?= (int) $prop['id']; ?>" 
                   class="filter-tag <?= $selectedPropertyId === (int) $prop['id'] ? 'active' : ''; ?>">
                    <?= htmlspecialchars($prop['name']); ?>
                </a>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="panel">
        <header class="panel-header">
            <h2>Units</h2>
            <div style="display: flex; gap: 12px;">
                <a href="/units/create<?= $selectedPropertyId > 0 ? '?property_id=' . $selectedPropertyId : ''; ?>" class="button primary">Add Unit</a>
            </div>
        </header>

        <?php if (empty($units)): ?>
            <div class="empty-state">
                <h3>No units found</h3>
                <p>Add units manually or create a property to auto-generate units.</p>
                <a href="/units/create<?= $selectedPropertyId > 0 ? '?property_id=' . $selectedPropertyId : ''; ?>" class="button primary" style="margin-top: 16px;">Add Your First Unit</a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Unit Number</th>
                            <?php if ($selectedPropertyId === 0): ?>
                                <th>Property</th>
                            <?php endif; ?>
                            <th>Bedrooms</th>
                            <th>Bathrooms</th>
                            <th>Rent</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($units as $unit): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($unit['unit_number']); ?></strong>
                                    <?php if (!empty($unit['description'])): ?>
                                        <div class="muted" style="font-size: 0.85rem;"><?= htmlspecialchars($unit['description']); ?></div>
                                    <?php endif; ?>
                                </td>
                                <?php if ($selectedPropertyId === 0): ?>
                                    <td>
                                        <div><?= htmlspecialchars($unit['property_name'] ?? 'Unknown'); ?></div>
                                        <span class="property-type"><?= htmlspecialchars(ucfirst($unit['property_type'] ?? 'other')); ?></span>
                                    </td>
                                <?php endif; ?>
                                <td><?= number_format((int) ($unit['bedrooms'] ?? 0)); ?> bed</td>
                                <td><?= number_format((float) ($unit['bathrooms'] ?? 0), 1); ?> bath</td>
                                <td><?= $formatCurrency($unit['rent_amount'] ?? 0); ?></td>
                                <td>
                                    <?php
                                    $status = $unit['status'] ?? 'vacant';
                                    $statusClass = $status === 'occupied' ? 'completed' : ($status === 'maintenance' ? 'warning' : 'pending');
                                    ?>
                                    <span class="status-pill <?= $statusClass; ?>"><?= htmlspecialchars(ucfirst($status)); ?></span>
                                </td>
                                <td>
                                    <div style="display: flex; gap: 8px;">
                                        <a href="/units/edit?id=<?= (int) $unit['id']; ?>" class="button" style="font-size: 0.85rem; padding: 6px 12px;">Edit</a>
                                        <form method="POST" action="/units/delete" style="display: inline;" onsubmit="return confirm('Delete this unit?');">
                                            <input type="hidden" name="id" value="<?= (int) $unit['id']; ?>">
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
