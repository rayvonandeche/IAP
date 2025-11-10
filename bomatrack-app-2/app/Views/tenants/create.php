<?php $title = 'Add New Tenant'; ?>
<?php
$errors = $errors ?? [];
$old = $old ?? [];
$vacantUnits = $vacantUnits ?? [];
?>

<section class="dashboard-page">
    <header class="dashboard-header">
        <div>
            <p class="muted" style="color: rgba(255,255,255,0.7); margin: 0 0 4px 0;">New Tenant</p>
            <h1>Add Tenant</h1>
            <p style="margin: 0; color: rgba(255,255,255,0.8);">
                Create a tenant profile and optionally assign them to a unit.
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

            <fieldset class="form-section">
                <legend>Unit Assignment (Optional)</legend>
                <p class="muted" style="margin: 0 0 16px 0; font-size: 0.9rem;">
                    Assign this tenant to a vacant unit to create an active tenancy. You can skip this and assign them later.
                </p>

                <?php if (empty($vacantUnits)): ?>
                    <div class="message info">
                        <strong>No vacant units available.</strong><br>
                        All your units are currently occupied or under maintenance. You can still create the tenant profile and assign them to a unit later.
                    </div>
                <?php else: ?>
                    <div class="form-group">
                        <label for="unit_id">Select Unit</label>
                        <select id="unit_id" name="unit_id" onchange="updateRentAmount(this)">
                            <option value="">-- Don't assign to a unit yet --</option>
                            <?php foreach ($vacantUnits as $unit): ?>
                                <option value="<?= (int) $unit['id']; ?>" 
                                        data-rent="<?= htmlspecialchars($unit['rent_amount']); ?>"
                                        data-deposit="<?= htmlspecialchars($unit['deposit_amount']); ?>"
                                        <?= isset($old['unit_id']) && (int) $old['unit_id'] === (int) $unit['id'] ? 'selected' : ''; ?>>
                                    <?= htmlspecialchars($unit['property_name']); ?> - Unit <?= htmlspecialchars($unit['unit_number']); ?> 
                                    (<?= (int) $unit['bedrooms']; ?> bed, KES <?= number_format((float) $unit['rent_amount'], 2); ?>/month)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (!empty($errors['unit_id'])): ?>
                            <span class="error-text"><?= htmlspecialchars($errors['unit_id']); ?></span>
                        <?php endif; ?>
                    </div>

                    <div id="tenancy-details" style="display: none;">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="start_date">Lease Start Date *</label>
                                <input type="date" id="start_date" name="start_date" 
                                       value="<?= htmlspecialchars($old['start_date'] ?? date('Y-m-d')); ?>">
                                <?php if (!empty($errors['start_date'])): ?>
                                    <span class="error-text"><?= htmlspecialchars($errors['start_date']); ?></span>
                                <?php endif; ?>
                            </div>

                            <div class="form-group">
                                <label for="rent_amount">Monthly Rent (KES) *</label>
                                <input type="number" id="rent_amount" name="rent_amount" step="0.01" 
                                       value="<?= htmlspecialchars($old['rent_amount'] ?? ''); ?>" readonly>
                                <?php if (!empty($errors['rent_amount'])): ?>
                                    <span class="error-text"><?= htmlspecialchars($errors['rent_amount']); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="deposit_paid">Deposit Paid (KES)</label>
                                <input type="number" id="deposit_paid" name="deposit_paid" step="0.01" 
                                       value="<?= htmlspecialchars($old['deposit_paid'] ?? ''); ?>">
                                <?php if (!empty($errors['deposit_paid'])): ?>
                                    <span class="error-text"><?= htmlspecialchars($errors['deposit_paid']); ?></span>
                                <?php endif; ?>
                            </div>

                            <div class="form-group">
                                <label for="end_date">Lease End Date (Optional)</label>
                                <input type="date" id="end_date" name="end_date" 
                                       value="<?= htmlspecialchars($old['end_date'] ?? ''); ?>">
                                <small class="muted">Leave empty for month-to-month lease</small>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="lease_terms">Lease Terms & Conditions</label>
                            <textarea id="lease_terms" name="lease_terms" rows="3" 
                                      placeholder="Any special terms, conditions, or agreements..."><?= htmlspecialchars($old['lease_terms'] ?? ''); ?></textarea>
                        </div>
                    </div>

                    <script>
                    function updateRentAmount(select) {
                        const tenancyDetails = document.getElementById('tenancy-details');
                        const rentInput = document.getElementById('rent_amount');
                        const depositInput = document.getElementById('deposit_paid');
                        
                        if (select.value) {
                            const option = select.options[select.selectedIndex];
                            const rent = option.getAttribute('data-rent');
                            const deposit = option.getAttribute('data-deposit');
                            
                            rentInput.value = rent;
                            depositInput.value = deposit;
                            tenancyDetails.style.display = 'block';
                        } else {
                            rentInput.value = '';
                            depositInput.value = '';
                            tenancyDetails.style.display = 'none';
                        }
                    }

                    // Show tenancy details if unit was previously selected
                    document.addEventListener('DOMContentLoaded', function() {
                        const unitSelect = document.getElementById('unit_id');
                        if (unitSelect.value) {
                            updateRentAmount(unitSelect);
                        }
                    });
                    </script>
                <?php endif; ?>
            </fieldset>

            <div class="form-actions">
                <button type="submit" class="button primary">Create Tenant</button>
                <a href="/tenants" class="button button-secondary">Cancel</a>
            </div>
        </form>
    </section>
</section>
