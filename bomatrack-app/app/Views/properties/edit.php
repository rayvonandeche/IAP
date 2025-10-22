<?php $title = 'Edit Property'; ?>
<?php
$errors = $errors ?? [];
$property = $property ?? [];
?>

<section class="dashboard-page">
    <header class="dashboard-header">
        <div>
            <p class="muted" style="color: rgba(255,255,255,0.7); margin: 0 0 4px 0;">Edit Property</p>
            <h1><?= htmlspecialchars($property['name'] ?? 'Property'); ?></h1>
            <p style="margin: 0; color: rgba(255,255,255,0.8);">
                Update property details. Note: Editing won't regenerate units automatically.
            </p>
        </div>
    </header>

    <?php if (!empty($errors['general'])): ?>
        <div class="message error">
            <?= htmlspecialchars($errors['general']); ?>
        </div>
    <?php endif; ?>

    <section class="panel">
        <form method="POST" action="/properties/edit?id=<?= (int) ($property['id'] ?? 0); ?>" class="property-form">
            <fieldset class="form-section">
                <legend>Basic Information</legend>

                <div class="form-group">
                    <label for="name">Property Name *</label>
                    <input type="text" id="name" name="name" value="<?= htmlspecialchars($property['name'] ?? ''); ?>" required>
                    <?php if (!empty($errors['name'])): ?>
                        <span class="error-text"><?= htmlspecialchars($errors['name']); ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="property_type">Property Type</label>
                        <select id="property_type" name="property_type">
                            <option value="apartment" <?= ($property['property_type'] ?? 'apartment') === 'apartment' ? 'selected' : ''; ?>>Apartment</option>
                            <option value="house" <?= ($property['property_type'] ?? '') === 'house' ? 'selected' : ''; ?>>House</option>
                            <option value="commercial" <?= ($property['property_type'] ?? '') === 'commercial' ? 'selected' : ''; ?>>Commercial</option>
                            <option value="other" <?= ($property['property_type'] ?? '') === 'other' ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="total_units">Total Units (Declared)</label>
                        <input type="number" id="total_units" name="total_units" min="1" value="<?= htmlspecialchars($property['total_units'] ?? '1'); ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="3"><?= htmlspecialchars($property['description'] ?? ''); ?></textarea>
                </div>
            </fieldset>

            <fieldset class="form-section">
                <legend>Location</legend>

                <div class="form-group">
                    <label for="address">Street Address *</label>
                    <input type="text" id="address" name="address" value="<?= htmlspecialchars($property['address'] ?? ''); ?>" required>
                    <?php if (!empty($errors['address'])): ?>
                        <span class="error-text"><?= htmlspecialchars($errors['address']); ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="city">City *</label>
                        <input type="text" id="city" name="city" value="<?= htmlspecialchars($property['city'] ?? ''); ?>" required>
                        <?php if (!empty($errors['city'])): ?>
                            <span class="error-text"><?= htmlspecialchars($errors['city']); ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="state">State/Region *</label>
                        <input type="text" id="state" name="state" value="<?= htmlspecialchars($property['state'] ?? ''); ?>" required>
                        <?php if (!empty($errors['state'])): ?>
                            <span class="error-text"><?= htmlspecialchars($errors['state']); ?></span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="postal_code">Postal Code *</label>
                        <input type="text" id="postal_code" name="postal_code" value="<?= htmlspecialchars($property['postal_code'] ?? ''); ?>" required>
                        <?php if (!empty($errors['postal_code'])): ?>
                            <span class="error-text"><?= htmlspecialchars($errors['postal_code']); ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="country">Country</label>
                        <input type="text" id="country" name="country" value="<?= htmlspecialchars($property['country'] ?? 'Kenya'); ?>">
                    </div>
                </div>
            </fieldset>

            <div class="form-actions">
                <button type="submit" class="button primary">Update Property</button>
                <a href="/properties" class="button button-secondary">Cancel</a>
            </div>
        </form>
    </section>

    <section class="panel danger-zone">
        <h2>Danger Zone</h2>
        <p>Deleting this property will also delete all associated units. This action cannot be undone.</p>
        <form method="POST" action="/properties/delete" onsubmit="return confirm('Are you sure you want to delete this property and all its units? This cannot be undone.');">
            <input type="hidden" name="id" value="<?= (int) ($property['id'] ?? 0); ?>">
            <button type="submit" class="button button-danger">Delete Property</button>
        </form>
    </section>
</section>
