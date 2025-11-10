<?php $title = 'Edit Tenant'; ?>
<?php
$errors = $errors ?? [];
$tenant = $tenant ?? [];
?>

<section class="dashboard-page">
    <header class="dashboard-header">
        <div>
            <p class="muted" style="color: rgba(255,255,255,0.7); margin: 0 0 4px 0;">Edit Tenant</p>
            <h1><?= htmlspecialchars(trim(($tenant['first_name'] ?? '') . ' ' . ($tenant['last_name'] ?? ''))); ?></h1>
            <p style="margin: 0; color: rgba(255,255,255,0.8);">
                Update tenant profile and contact information.
            </p>
        </div>
    </header>

    <section class="panel">
        <form method="POST" action="/tenants/edit?id=<?= (int) ($tenant['id'] ?? 0); ?>" class="property-form">
            <fieldset class="form-section">
                <legend>Personal Information</legend>

                <div class="form-row">
                    <div class="form-group">
                        <label for="first_name">First Name *</label>
                        <input type="text" id="first_name" name="first_name" 
                               value="<?= htmlspecialchars($tenant['first_name'] ?? ''); ?>" required>
                        <?php if (!empty($errors['first_name'])): ?>
                            <span class="error-text"><?= htmlspecialchars($errors['first_name']); ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="last_name">Last Name *</label>
                        <input type="text" id="last_name" name="last_name" 
                               value="<?= htmlspecialchars($tenant['last_name'] ?? ''); ?>" required>
                        <?php if (!empty($errors['last_name'])): ?>
                            <span class="error-text"><?= htmlspecialchars($errors['last_name']); ?></span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="email">Email Address *</label>
                        <input type="email" id="email" name="email" 
                               value="<?= htmlspecialchars($tenant['email'] ?? ''); ?>" required>
                        <?php if (!empty($errors['email'])): ?>
                            <span class="error-text"><?= htmlspecialchars($errors['email']); ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="phone">Phone Number *</label>
                        <input type="text" id="phone" name="phone" 
                               value="<?= htmlspecialchars($tenant['phone'] ?? ''); ?>" required>
                        <?php if (!empty($errors['phone'])): ?>
                            <span class="error-text"><?= htmlspecialchars($errors['phone']); ?></span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-group">
                    <label for="national_id">National ID / Passport</label>
                    <input type="text" id="national_id" name="national_id" 
                           value="<?= htmlspecialchars($tenant['national_id'] ?? ''); ?>">
                </div>
            </fieldset>

            <fieldset class="form-section">
                <legend>Emergency Contact</legend>

                <div class="form-row">
                    <div class="form-group">
                        <label for="emergency_contact_name">Contact Name</label>
                        <input type="text" id="emergency_contact_name" name="emergency_contact_name" 
                               value="<?= htmlspecialchars($tenant['emergency_contact_name'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="emergency_contact_phone">Contact Phone</label>
                        <input type="text" id="emergency_contact_phone" name="emergency_contact_phone" 
                               value="<?= htmlspecialchars($tenant['emergency_contact_phone'] ?? ''); ?>">
                    </div>
                </div>
            </fieldset>

            <fieldset class="form-section">
                <legend>Additional Information</legend>

                <div class="form-group">
                    <label for="employment_info">Employment Information</label>
                    <textarea id="employment_info" name="employment_info" rows="3" 
                              placeholder="Employer name, position, etc."><?= htmlspecialchars($tenant['employment_info'] ?? ''); ?></textarea>
                </div>
            </fieldset>

            <div class="form-actions">
                <button type="submit" class="button primary">Update Tenant</button>
                <a href="/tenants" class="button button-secondary">Cancel</a>
            </div>
        </form>
    </section>

    <section class="panel danger-zone">
        <h2>Danger Zone</h2>
        <p>Deleting this tenant will remove their profile. Active tenancies may need to be handled separately.</p>
        <form method="POST" action="/tenants/delete" onsubmit="return confirm('Are you sure you want to delete this tenant? This cannot be undone.');">
            <input type="hidden" name="id" value="<?= (int) ($tenant['id'] ?? 0); ?>">
            <button type="submit" class="button button-danger">Delete Tenant</button>
        </form>
    </section>
</section>
