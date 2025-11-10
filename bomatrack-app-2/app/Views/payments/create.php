<?php $title = 'Record Payment'; ?>
<?php
$errors = $errors ?? [];
$old = $old ?? [];
$activeTenancies = $activeTenancies ?? [];
?>

<section class="dashboard-page">
    <header class="dashboard-header">
        <div>
            <p class="muted" style="color: rgba(255,255,255,0.7); margin: 0 0 4px 0;">New Payment</p>
            <h1>Record Payment</h1>
            <p style="margin: 0; color: rgba(255,255,255,0.8);">
                Record rent payments and other transactions.
            </p>
        </div>
    </header>

    <section class="panel">
        <form method="POST" action="/payments/create" class="property-form">
            <fieldset class="form-section">
                <legend>Payment Details</legend>

                <?php if (empty($activeTenancies)): ?>
                    <div class="message info">
                        <strong>No active tenancies found.</strong><br>
                        You need to have active tenants assigned to units before recording payments.
                        <a href="/tenants/create" style="color: #0066cc; text-decoration: underline;">Add a tenant</a>
                    </div>
                <?php else: ?>
                    <div class="form-group">
                        <label for="tenancy_id">Tenant / Unit *</label>
                        <select id="tenancy_id" name="tenancy_id" required onchange="updateRentAmount(this)">
                            <option value="">-- Select Tenant --</option>
                            <?php foreach ($activeTenancies as $tenancy): ?>
                                <option value="<?= (int) $tenancy['id']; ?>" 
                                        data-rent="<?= htmlspecialchars($tenancy['rent_amount']); ?>"
                                        <?= isset($old['tenancy_id']) && (int) $old['tenancy_id'] === (int) $tenancy['id'] ? 'selected' : ''; ?>>
                                    <?= htmlspecialchars($tenancy['first_name'] . ' ' . $tenancy['last_name']); ?> 
                                    - <?= htmlspecialchars($tenancy['property_name']); ?> Unit <?= htmlspecialchars($tenancy['unit_number']); ?>
                                    (KES <?= number_format((float) $tenancy['rent_amount'], 2); ?>/month)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (!empty($errors['tenancy_id'])): ?>
                            <span class="error-text"><?= htmlspecialchars($errors['tenancy_id']); ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="amount">Amount (KES) *</label>
                            <input type="number" id="amount" name="amount" step="0.01" 
                                   value="<?= htmlspecialchars($old['amount'] ?? ''); ?>" required>
                            <?php if (!empty($errors['amount'])): ?>
                                <span class="error-text"><?= htmlspecialchars($errors['amount']); ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label for="payment_date">Payment Date *</label>
                            <input type="date" id="payment_date" name="payment_date" 
                                   value="<?= htmlspecialchars($old['payment_date'] ?? date('Y-m-d')); ?>" required>
                            <?php if (!empty($errors['payment_date'])): ?>
                                <span class="error-text"><?= htmlspecialchars($errors['payment_date']); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="payment_type">Payment Type *</label>
                            <select id="payment_type" name="payment_type" required>
                                <option value="rent" <?= isset($old['payment_type']) && $old['payment_type'] === 'rent' ? 'selected' : ''; ?>>Rent</option>
                                <option value="deposit" <?= isset($old['payment_type']) && $old['payment_type'] === 'deposit' ? 'selected' : ''; ?>>Deposit</option>
                                <option value="late_fee" <?= isset($old['payment_type']) && $old['payment_type'] === 'late_fee' ? 'selected' : ''; ?>>Late Fee</option>
                                <option value="maintenance" <?= isset($old['payment_type']) && $old['payment_type'] === 'maintenance' ? 'selected' : ''; ?>>Maintenance</option>
                                <option value="other" <?= isset($old['payment_type']) && $old['payment_type'] === 'other' ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="payment_method">Payment Method *</label>
                            <select id="payment_method" name="payment_method" required>
                                <option value="cash" <?= isset($old['payment_method']) && $old['payment_method'] === 'cash' ? 'selected' : ''; ?>>Cash</option>
                                <option value="mpesa" <?= isset($old['payment_method']) && $old['payment_method'] === 'mpesa' ? 'selected' : ''; ?>>M-Pesa</option>
                                <option value="bank_transfer" <?= isset($old['payment_method']) && $old['payment_method'] === 'bank_transfer' ? 'selected' : ''; ?>>Bank Transfer</option>
                                <option value="cheque" <?= isset($old['payment_method']) && $old['payment_method'] === 'cheque' ? 'selected' : ''; ?>>Cheque</option>
                                <option value="other" <?= isset($old['payment_method']) && $old['payment_method'] === 'other' ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="payment_reference">Reference Number</label>
                        <input type="text" id="payment_reference" name="payment_reference" 
                               value="<?= htmlspecialchars($old['payment_reference'] ?? ''); ?>"
                               placeholder="e.g., M-Pesa code, cheque number">
                    </div>

                    <div class="form-group">
                        <label for="notes">Notes</label>
                        <textarea id="notes" name="notes" rows="3" 
                                  placeholder="Additional notes..."><?= htmlspecialchars($old['notes'] ?? ''); ?></textarea>
                    </div>

                    <script>
                    function updateRentAmount(select) {
                        const amountInput = document.getElementById('amount');
                        
                        if (select.value) {
                            const option = select.options[select.selectedIndex];
                            const rent = option.getAttribute('data-rent');
                            
                            if (!amountInput.value || amountInput.value == '0') {
                                amountInput.value = rent;
                            }
                        }
                    }
                    </script>
                <?php endif; ?>
            </fieldset>

            <div class="form-actions">
                <?php if (!empty($activeTenancies)): ?>
                    <button type="submit" class="button primary">Record Payment</button>
                <?php endif; ?>
                <a href="/payments" class="button button-secondary">Cancel</a>
            </div>
        </form>
    </section>
</section>
