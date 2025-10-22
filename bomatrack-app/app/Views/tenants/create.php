<?php $title = 'Add New Tenant'; ?>
<?php
$errors = $errors ?? [];
$old = $old ?? [];
?>

<section class="dashboard-page">
    <header class="dashboard-header">
        <div>
            <p class="muted" style="color: rgba(255,255,255,0.7); margin: 0 0 4px 0;">New Tenant</p>
            <h1>Add Tenant</h1>
            <p style="margin: 0; color: rgba(255,255,255,0.8);">
                Create a tenant profile with contact and emergency information.
            </p>
        </div>
    </header>

    <section class="panel">
        <form method="POST" action="/tenants/create" class="property-form">
            <fieldset class="form-section">
                <legend>Personal Information</legend>

                <div class="form-row">
                    <div class="form-group">
                        <label for="first_name">First Name *</label>
                        <input type="text" id="first_name" name="first_name" 
                               value="<?= htmlspecialchars($old['first_name'] ?? ''); ?>" required>
                        <?php if (!empty($errors['first_name'])): ?>
                            <span class="error-text"><?= htmlspecialchars($errors['first_name']); ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="last_name">Last Name *</label>
                        <input type="text" id="last_name" name="last_name" 
                               value="<?= htmlspecialchars($old['last_name'] ?? ''); ?>" required>
                        <?php if (!empty($errors['last_name'])): ?>
                            <span class="error-text"><?= htmlspecialchars($errors['last_name']); ?></span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="email">Email Address *</label>
                        <input type="email" id="email" name="email" 
                               value="<?= htmlspecialchars($old['email'] ?? ''); ?>" required>
                        <?php if (!empty($errors['email'])): ?>
                            <span class="error-text"><?= htmlspecialchars($errors['email']); ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="phone">Phone Number *</label>
                        <input type="text" id="phone" name="phone" 
                               value="<?= htmlspecialchars($old['phone'] ?? ''); ?>" required>
                        <?php if (!empty($errors['phone'])): ?>
                            <span class="error-text"><?= htmlspecialchars($errors['phone']); ?></span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-group">
                    <label for="national_id">National ID / Passport</label>
                    <input type="text" id="national_id" name="national_id" 
                           value="<?= htmlspecialchars($old['national_id'] ?? ''); ?>">
                </div>
            </fieldset>

            <fieldset class="form-section">
                <legend>Emergency Contact</legend>

                <div class="form-row">
                    <div class="form-group">
                        <label for="emergency_contact_name">Contact Name</label>
                        <input type="text" id="emergency_contact_name" name="emergency_contact_name" 
                               value="<?= htmlspecialchars($old['emergency_contact_name'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="emergency_contact_phone">Contact Phone</label>
                        <input type="text" id="emergency_contact_phone" name="emergency_contact_phone" 
                               value="<?= htmlspecialchars($old['emergency_contact_phone'] ?? ''); ?>">
                    </div>
                </div>
            </fieldset>

            <fieldset class="form-section">
                <legend>Additional Information</legend>

                <div class="form-group">
                    <label for="employment_info">Employment Information</label>
                    <textarea id="employment_info" name="employment_info" rows="3" 
                              placeholder="Employer name, position, etc."><?= htmlspecialchars($old['employment_info'] ?? ''); ?></textarea>
                </div>
            </fieldset>

            <div class="form-actions">
                <button type="submit" class="button primary">Create Tenant</button>
                <a href="/tenants" class="button button-secondary">Cancel</a>
            </div>
        </form>
    </section>
</section>
