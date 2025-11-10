<?php $title = 'Edit Unit'; ?>
<?php
$errors = $errors ?? [];
$unit = $unit ?? [];
$property = $property ?? [];
?>

<section class="dashboard-page">
    <header class="dashboard-header">
        <div>
            <p class="muted" style="color: rgba(255,255,255,0.7); margin: 0 0 4px 0;">Edit Unit</p>
            <h1><?= htmlspecialchars($unit['unit_number'] ?? 'Unit'); ?></h1>
            <p style="margin: 0; color: rgba(255,255,255,0.8);">
                Property: <?= htmlspecialchars($property['name'] ?? 'Unknown'); ?>
            </p>
        </div>
    </header>

    <section class="panel">
        <form method="POST" action="/units/edit?id=<?= (int) ($unit['id'] ?? 0); ?>" class="property-form">
            <fieldset class="form-section">
                <legend>Unit Details</legend>

                <div class="form-group">
                    <label for="unit_number">Unit Number *</label>
                    <input type="text" id="unit_number" name="unit_number" 
                           value="<?= htmlspecialchars($unit['unit_number'] ?? ''); ?>" required>
                    <?php if (!empty($errors['unit_number'])): ?>
                        <span class="error-text"><?= htmlspecialchars($errors['unit_number']); ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select id="status" name="status">
                            <option value="vacant" <?= ($unit['status'] ?? 'vacant') === 'vacant' ? 'selected' : ''; ?>>Vacant</option>
                            <option value="occupied" <?= ($unit['status'] ?? '') === 'occupied' ? 'selected' : ''; ?>>Occupied</option>
                            <option value="maintenance" <?= ($unit['status'] ?? '') === 'maintenance' ? 'selected' : ''; ?>>Maintenance</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="2"><?= htmlspecialchars($unit['description'] ?? ''); ?></textarea>
                </div>
            </fieldset>

            <fieldset class="form-section">
                <legend>Unit Specifications</legend>

                <div class="form-row">
                    <div class="form-group">
                        <label for="bedrooms">Bedrooms</label>
                        <input type="number" id="bedrooms" name="bedrooms" min="0" 
                               value="<?= htmlspecialchars($unit['bedrooms'] ?? '1'); ?>">
                    </div>

                    <div class="form-group">
                        <label for="bathrooms">Bathrooms</label>
                        <input type="number" step="0.5" id="bathrooms" name="bathrooms" min="0" 
                               value="<?= htmlspecialchars($unit['bathrooms'] ?? '1'); ?>">
                    </div>

                    <div class="form-group">
                        <label for="square_feet">Square Feet</label>
                        <input type="number" id="square_feet" name="square_feet" min="0" 
                               value="<?= htmlspecialchars($unit['square_feet'] ?? ''); ?>" 
                               placeholder="Optional">
                    </div>
                </div>
            </fieldset>

            <fieldset class="form-section">
                <legend>Financial Details</legend>

                <div class="form-row">
                    <div class="form-group">
                        <label for="rent_amount">Monthly Rent (KES) *</label>
                        <input type="number" step="0.01" id="rent_amount" name="rent_amount" min="0" 
                               value="<?= htmlspecialchars($unit['rent_amount'] ?? ''); ?>" required>
                        <?php if (!empty($errors['rent_amount'])): ?>
                            <span class="error-text"><?= htmlspecialchars($errors['rent_amount']); ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="deposit_amount">Security Deposit (KES)</label>
                        <input type="number" step="0.01" id="deposit_amount" name="deposit_amount" min="0" 
                               value="<?= htmlspecialchars($unit['deposit_amount'] ?? '0'); ?>">
                    </div>
                </div>
            </fieldset>

            <div class="form-actions">
                <button type="submit" class="button primary">Update Unit</button>
                <a href="/units?property_id=<?= (int) ($property['id'] ?? 0); ?>" class="button button-secondary">Cancel</a>
            </div>
        </form>
    </section>

    <section class="panel danger-zone">
        <h2>Danger Zone</h2>
        <p>Deleting this unit cannot be undone.</p>
        <form method="POST" action="/units/delete" onsubmit="return confirm('Are you sure you want to delete this unit? This cannot be undone.');">
            <input type="hidden" name="id" value="<?= (int) ($unit['id'] ?? 0); ?>">
            <button type="submit" class="button button-danger">Delete Unit</button>
        </form>
    </section>
</section>
