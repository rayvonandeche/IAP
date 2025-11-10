<?php $title = 'Properties'; ?>
<?php
$formatCurrency = function ($amount): string {
    return 'KES ' . number_format((float) $amount, 2);
};

$formatDate = function (?string $value): string {
    if (!$value) {
        return '—';
    }
    $timestamp = strtotime($value);
    if ($timestamp === false) {
        return '—';
    }
    return date('M j, Y', $timestamp);
};

$properties = $properties ?? [];
$summary = $summary ?? [];
$totals = $totals ?? [];

$totalProperties = $summary['total_properties'] ?? 0;
$declaredUnits = $summary['declared_units'] ?? 0;
$unitsInSystem = $totals['units_count'] ?? 0;
$occupiedUnits = $totals['occupied_units'] ?? 0;
$vacantUnits = $totals['vacant_units'] ?? 0;
$maintenanceUnits = $totals['maintenance_units'] ?? 0;
$rentRoll = $totals['rent_roll'] ?? 0.0;
?>

<section class="dashboard-page">
    <header class="dashboard-header">
        <div>
            <p class="muted" style="color: rgba(255,255,255,0.7); margin: 0 0 4px 0;">Portfolio</p>
            <h1>Property Overview</h1>
            <p style="margin: 0; color: rgba(255,255,255,0.8);">
                Track every property, its units, occupancy and rent roll in one place.
            </p>
        </div>
        <div class="dashboard-highlight">
            <span class="highlight-label">Active Properties</span>
            <span class="highlight-value"><?= number_format((int) $totalProperties); ?></span>
        </div>
    </header>

    <section class="metrics-grid">
        <article class="metric-card">
            <p class="metric-label">Declared Units</p>
            <p class="metric-value">
                <?= number_format((int) $declaredUnits); ?>
                <span class="metric-suffix">total capacity</span>
            </p>
            <p class="metric-subtitle">As recorded during property onboarding.</p>
        </article>
        <article class="metric-card">
            <p class="metric-label">Units On Record</p>
            <p class="metric-value">
                <?= number_format((int) $unitsInSystem); ?>
            </p>
            <p class="metric-subtitle">Units created in the system across all properties.</p>
        </article>
        <article class="metric-card">
            <p class="metric-label">Total Rent Roll</p>
            <p class="metric-value">
                <?= $formatCurrency($rentRoll); ?>
            </p>
            <p class="metric-subtitle">Based on unit rent amounts in the system.</p>
        </article>
    </section>

    <section class="panels-grid columns-2">
        <article class="panel">
            <header class="panel-header">
                <h2>Occupancy Snapshot</h2>
            </header>
            <ul class="status-list">
                <li>
                    <span class="status-dot occupied"></span>
                    <span>Occupied Units</span>
                    <strong><?= number_format((int) $occupiedUnits); ?></strong>
                </li>
                <li>
                    <span class="status-dot vacant"></span>
                    <span>Vacant Units</span>
                    <strong><?= number_format((int) $vacantUnits); ?></strong>
                </li>
                <li>
                    <span class="status-dot maintenance"></span>
                    <span>Under Maintenance</span>
                    <strong><?= number_format((int) $maintenanceUnits); ?></strong>
                </li>
            </ul>
        </article>
        <article class="panel">
            <header class="panel-header">
                <h2>Quick Tips</h2>
            </header>
            <div class="stat-list" style="gap: 16px;">
                <div>
                    <dt>Add New Property</dt>
                    <dd class="muted">Use the upcoming “Add Property” button to keep your portfolio updated.</dd>
                </div>
                <div>
                    <dt>Sync Unit Counts</dt>
                    <dd class="muted">Ensure every declared unit has a matching unit profile for accurate occupancy.</dd>
                </div>
                <div>
                    <dt>Boost Descriptions</dt>
                    <dd class="muted">Great descriptions help potential tenants understand each property quickly.</dd>
                </div>
            </div>
        </article>
    </section>

    <section class="panel">
        <header class="panel-header">
            <h2>Properties</h2>
            <div style="display: flex; gap: 12px; align-items: center;">
                <span class="panel-meta">Sorted by most recently created</span>
                <a href="/properties/create" class="button primary">Add Property</a>
            </div>
        </header>

        <?php if (isset($_GET['created'])): ?>
            <div class="message success">Property created successfully with auto-generated units!</div>
        <?php endif; ?>
        <?php if (isset($_GET['updated'])): ?>
            <div class="message success">Property updated successfully!</div>
        <?php endif; ?>
        <?php if (isset($_GET['deleted'])): ?>
            <div class="message success">Property deleted successfully!</div>
        <?php endif; ?>
        <?php if (isset($_GET['error'])): ?>
            <div class="message error">An error occurred. Please try again.</div>
        <?php endif; ?>

        <?php if (empty($properties)): ?>
            <div class="empty-state">
                <h3>No properties yet</h3>
                <p>Add your first property to begin tracking units, tenancies and payments here.</p>
                <a href="/properties/create" class="button primary" style="margin-top: 16px;">Create Your First Property</a>
            </div>
        <?php else: ?>
            <div class="property-grid">
                <?php foreach ($properties as $property): ?>
                    <article class="property-card">
                        <header class="property-card__header">
                            <div>
                                <h3><?= htmlspecialchars($property['name']); ?></h3>
                                <p class="muted">
                                    <?= htmlspecialchars($property['city'] . ', ' . $property['state'] . ', ' . $property['country']); ?>
                                </p>
                            </div>
                            <span class="property-type"><?= htmlspecialchars(ucfirst($property['property_type'] ?? 'other')); ?></span>
                        </header>

                        <div class="property-card__body">
                            <p><?= nl2br(htmlspecialchars($property['description'] ?? 'No description provided yet.')); ?></p>

                            <dl class="property-stats">
                                <div>
                                    <dt>Total Units</dt>
                                    <dd><?= number_format((int) ($property['total_units'] ?? 0)); ?></dd>
                                </div>
                                <div>
                                    <dt>Tracked Units</dt>
                                    <dd><?= number_format((int) ($property['units_count'] ?? 0)); ?></dd>
                                </div>
                                <div>
                                    <dt>Rent Roll</dt>
                                    <dd><?= $formatCurrency($property['total_rent_roll'] ?? 0); ?></dd>
                                </div>
                                <div>
                                    <dt>Avg Rent</dt>
                                    <dd><?= $formatCurrency($property['avg_rent'] ?? 0); ?></dd>
                                </div>
                            </dl>

                            <ul class="property-status">
                                <li>
                                    <span class="status-dot occupied"></span>
                                    Occupied: <strong><?= number_format((int) ($property['occupied_units'] ?? 0)); ?></strong>
                                </li>
                                <li>
                                    <span class="status-dot vacant"></span>
                                    Vacant: <strong><?= number_format((int) ($property['vacant_units'] ?? 0)); ?></strong>
                                </li>
                                <li>
                                    <span class="status-dot maintenance"></span>
                                    Maintenance: <strong><?= number_format((int) ($property['maintenance_units'] ?? 0)); ?></strong>
                                </li>
                            </ul>
                        </div>

                        <footer class="property-card__footer">
                            <span class="muted">Created <?= htmlspecialchars($formatDate($property['created_at'] ?? null)); ?></span>
                            <div class="property-actions">
                                <a class="button" href="/properties/edit?id=<?= (int) $property['id']; ?>">Edit</a>
                                <a class="button button-warning" href="/units?property_id=<?= (int) $property['id']; ?>">View Units</a>
                            </div>
                        </footer>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</section>
