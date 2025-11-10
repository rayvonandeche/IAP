<?php $title = 'Add New Unit'; ?>
<?php
$errors = $errors ?? [];
$old = $old ?? [];
$properties = $properties ?? [];
?>

<section class="dashboard-page">
    <header class="dashboard-header">
        <div>
            <p class="muted" style="color: rgba(255,255,255,0.7); margin: 0 0 4px 0;">New Unit</p>
            <h1>Add Unit</h1>
            <p style="margin: 0; color: rgba(255,255,255,0.8);">
                Create a new unit record for tracking occupancy and rent.
            </p>
        </div>
    </header>

    <section class="panel">
        <form method="POST" action="/units/create" class="property-form">
            <fieldset class="form-section">
                <legend>Unit Details</legend>

                <div class="form-group">
                    <label for="property_id">Property *</label>
                    <select id="property_id" name="property_id" required>
                        <option value="">Select a property</option>
                        <?php foreach ($properties as $property): ?>
                            <option value="<?= (int) $property['id']; ?>" 
                                    <?= ($old['property_id'] ?? '') == $property['id'] ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($property['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (!empty($errors['property_id'])): ?>
                        <span class="error-text"><?= htmlspecialchars($errors['property_id']); ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="unit_number">Unit Number *</label>
                    <input type="text" id="unit_number" name="unit_number" 
                           value="<?= htmlspecialchars($old['unit_number'] ?? ''); ?>" 
                           placeholder="e.g., F1-U01, Apt 101, etc." required>
                    <?php if (!empty($errors['unit_number'])): ?>
                        <span class="error-text"><?= htmlspecialchars($errors['unit_number']); ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select id="status" name="status">
                            <option value="vacant" <?= ($old['status'] ?? 'vacant') === 'vacant' ? 'selected' : ''; ?>>Vacant</option>
                            <option value="occupied" <?= ($old['status'] ?? '') === 'occupied' ? 'selected' : ''; ?>>Occupied</option>
                            <option value="maintenance" <?= ($old['status'] ?? '') === 'maintenance' ? 'selected' : ''; ?>>Maintenance</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="2"><?= htmlspecialchars($old['description'] ?? ''); ?></textarea>
                </div>
            </fieldset>

            <fieldset class="form-section">
                <legend>Unit Specifications</legend>

                <div class="form-row">
                    <div class="form-group">
                        <label for="bedrooms">Bedrooms</label>
                        <input type="number" id="bedrooms" name="bedrooms" min="0" 
                               value="<?= htmlspecialchars($old['bedrooms'] ?? '1'); ?>">
                    </div>

                    <div class="form-group">
                        <label for="bathrooms">Bathrooms</label>
                        <input type="number" step="0.5" id="bathrooms" name="bathrooms" min="0" 
                               value="<?= htmlspecialchars($old['bathrooms'] ?? '1'); ?>">
                    </div>

                    <div class="form-group">
                        <label for="square_feet">Square Feet</label>
                        <input type="number" id="square_feet" name="square_feet" min="0" 
                               value="<?= htmlspecialchars($old['square_feet'] ?? ''); ?>" 
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
                               value="<?= htmlspecialchars($old['rent_amount'] ?? ''); ?>" required>
                        <?php if (!empty($errors['rent_amount'])): ?>
                            <span class="error-text"><?= htmlspecialchars($errors['rent_amount']); ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="deposit_amount">Security Deposit (KES)</label>
                        <input type="number" step="0.01" id="deposit_amount" name="deposit_amount" min="0" 
                               value="<?= htmlspecialchars($old['deposit_amount'] ?? '0'); ?>">
                    </div>
                </div>
            </fieldset>

            <div class="form-actions">
                <button type="submit" class="button primary">Create Unit</button>
                <a href="/units" class="button button-secondary">Cancel</a>
            </div>
        </form>
    </section>
</section>
