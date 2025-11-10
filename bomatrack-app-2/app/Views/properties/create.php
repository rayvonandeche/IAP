<?php $title = 'Add New Property'; ?>
<?php
$errors = $errors ?? [];
$old = $old ?? [];
?>

<section class="dashboard-page">
    <header class="dashboard-header">
        <div>
            <p class="muted" style="color: rgba(255,255,255,0.7); margin: 0 0 4px 0;">New Property</p>
            <h1>Add Property</h1>
            <p style="margin: 0; color: rgba(255,255,255,0.8);">
                Fill in the property details. Units will be auto-generated based on floor configuration.
            </p>
        </div>
    </header>

    <?php if (!empty($errors['general'])): ?>
        <div class="message error">
            <?= htmlspecialchars($errors['general']); ?>
        </div>
    <?php endif; ?>

    <section class="panel">
        <form method="POST" action="/properties/create" class="property-form">
            <fieldset class="form-section">
                <legend>Basic Information</legend>

                <div class="form-group">
                    <label for="name">Property Name *</label>
                    <input type="text" id="name" name="name" value="<?= htmlspecialchars($old['name'] ?? ''); ?>" required>
                    <?php if (!empty($errors['name'])): ?>
                        <span class="error-text"><?= htmlspecialchars($errors['name']); ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="property_type">Property Type</label>
                        <select id="property_type" name="property_type">
                            <option value="apartment" <?= ($old['property_type'] ?? 'apartment') === 'apartment' ? 'selected' : ''; ?>>Apartment</option>
                            <option value="house" <?= ($old['property_type'] ?? '') === 'house' ? 'selected' : ''; ?>>House</option>
                            <option value="commercial" <?= ($old['property_type'] ?? '') === 'commercial' ? 'selected' : ''; ?>>Commercial</option>
                            <option value="other" <?= ($old['property_type'] ?? '') === 'other' ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="3"><?= htmlspecialchars($old['description'] ?? ''); ?></textarea>
                </div>
            </fieldset>

            <fieldset class="form-section">
                <legend>Location</legend>

                <div class="form-group">
                    <label for="address">Street Address *</label>
                    <input type="text" id="address" name="address" value="<?= htmlspecialchars($old['address'] ?? ''); ?>" required>
                    <?php if (!empty($errors['address'])): ?>
                        <span class="error-text"><?= htmlspecialchars($errors['address']); ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="city">City *</label>
                        <input type="text" id="city" name="city" value="<?= htmlspecialchars($old['city'] ?? ''); ?>" required>
                        <?php if (!empty($errors['city'])): ?>
                            <span class="error-text"><?= htmlspecialchars($errors['city']); ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="state">State/Region *</label>
                        <input type="text" id="state" name="state" value="<?= htmlspecialchars($old['state'] ?? ''); ?>" required>
                        <?php if (!empty($errors['state'])): ?>
                            <span class="error-text"><?= htmlspecialchars($errors['state']); ?></span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="postal_code">Postal Code *</label>
                        <input type="text" id="postal_code" name="postal_code" value="<?= htmlspecialchars($old['postal_code'] ?? ''); ?>" required>
                        <?php if (!empty($errors['postal_code'])): ?>
                            <span class="error-text"><?= htmlspecialchars($errors['postal_code']); ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="country">Country</label>
                        <input type="text" id="country" name="country" value="<?= htmlspecialchars($old['country'] ?? 'Kenya'); ?>">
                    </div>
                </div>
            </fieldset>

            <fieldset class="form-section">
                <legend>Unit Configuration</legend>
                <p class="form-help">Define floors and units per floor. Units will be auto-generated with format F1-U01, F2-U03, etc.</p>

                <div class="form-row">
                    <div class="form-group">
                        <label for="num_floors">Number of Floors *</label>
                        <input type="number" id="num_floors" name="num_floors" min="1" value="<?= htmlspecialchars($old['num_floors'] ?? '1'); ?>" required>
                        <?php if (!empty($errors['num_floors'])): ?>
                            <span class="error-text"><?= htmlspecialchars($errors['num_floors']); ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="units_per_floor">Units per Floor *</label>
                        <input type="number" id="units_per_floor" name="units_per_floor" min="1" value="<?= htmlspecialchars($old['units_per_floor'] ?? '1'); ?>" required>
                        <?php if (!empty($errors['units_per_floor'])): ?>
                            <span class="error-text"><?= htmlspecialchars($errors['units_per_floor']); ?></span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="total-units-preview">
                    <strong>Total Units:</strong> <span id="total-units-display">1</span>
                </div>
            </fieldset>

            <fieldset class="form-section">
                <legend>Default Unit Settings</legend>
                <p class="form-help">These values will be applied to all auto-generated units. You can edit individual units later.</p>

                <div class="form-row">
                    <div class="form-group">
                        <label for="default_rent">Default Rent (KES) *</label>
                        <input type="number" step="0.01" id="default_rent" name="default_rent" min="0" value="<?= htmlspecialchars($old['default_rent'] ?? '0'); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="default_deposit">Default Deposit (KES)</label>
                        <input type="number" step="0.01" id="default_deposit" name="default_deposit" min="0" value="<?= htmlspecialchars($old['default_deposit'] ?? '0'); ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="default_bedrooms">Default Bedrooms</label>
                        <input type="number" id="default_bedrooms" name="default_bedrooms" min="0" value="<?= htmlspecialchars($old['default_bedrooms'] ?? '1'); ?>">
                    </div>

                    <div class="form-group">
                        <label for="default_bathrooms">Default Bathrooms</label>
                        <input type="number" step="0.5" id="default_bathrooms" name="default_bathrooms" min="0" value="<?= htmlspecialchars($old['default_bathrooms'] ?? '1'); ?>">
                    </div>
                </div>
            </fieldset>

            <div class="form-actions">
                <button type="submit" class="button primary">Create Property & Generate Units</button>
                <a href="/properties" class="button button-secondary">Cancel</a>
            </div>
        </form>
    </section>
</section>

<script>
// Update total units preview dynamically
document.addEventListener('DOMContentLoaded', function() {
    const floorsInput = document.getElementById('num_floors');
    const unitsInput = document.getElementById('units_per_floor');
    const totalDisplay = document.getElementById('total-units-display');

    function updateTotal() {
        const floors = parseInt(floorsInput.value) || 0;
        const units = parseInt(unitsInput.value) || 0;
        totalDisplay.textContent = floors * units;
    }

    floorsInput.addEventListener('input', updateTotal);
    unitsInput.addEventListener('input', updateTotal);
    updateTotal();
});
</script>
