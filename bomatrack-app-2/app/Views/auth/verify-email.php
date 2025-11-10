<?php $title = $success ? 'Email Verified' : 'Verification Failed'; ?>
<div class="verification-card">
    <?php if ($success): ?>
        <div class="success-icon">✓</div>
        <h2>Email Verified Successfully!</h2>
        <p class="success-message"><?php echo htmlspecialchars($message); ?></p>
        
        <div class="verification-actions">
            <a href="/dashboard" class="button primary">Go to Dashboard</a>
            <a href="/profile/complete" class="button secondary">Complete Profile</a>
        </div>
    <?php else: ?>
        <div class="error-icon">✗</div>
        <h2>Verification Failed</h2>
        <p class="error-message"><?php echo htmlspecialchars($message); ?></p>
        
        <div class="verification-actions">
            <a href="/resend-verification" class="button primary">Resend Verification Email</a>
            <a href="/login" class="button secondary">Back to Login</a>
        </div>
    <?php endif; ?>
    
    <div class="help-section">
        <h3>Need Help?</h3>
        <p>If you continue to experience issues with email verification, please contact our support team.</p>
        <p><strong>Email:</strong> support@bomatrack.com</p>
    </div>
</div>

<style>
.verification-card {
    max-width: 500px;
    margin: 40px auto;
    padding: 40px;
    text-align: center;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
}

.success-icon, .error-icon {
    width: 80px;
    height: 80px;
    margin: 0 auto 20px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 40px;
    font-weight: bold;
    color: white;
}

.success-icon {
    background: #10b981;
}

.error-icon {
    background: #ef4444;
}

.success-message {
    color: #10b981;
    font-weight: 500;
    margin-bottom: 30px;
}

.error-message {
    color: #ef4444;
    font-weight: 500;
    margin-bottom: 30px;
}

.verification-actions {
    display: flex;
    gap: 12px;
    justify-content: center;
    margin-bottom: 30px;
    flex-wrap: wrap;
}

.button.primary {
    background: #3b82f6;
    color: white;
}

.button.secondary {
    background: #6b7280;
    color: white;
}

.button.primary:hover {
    background: #2563eb;
}

.button.secondary:hover {
    background: #4b5563;
}

.help-section {
    border-top: 1px solid #e5e7eb;
    padding-top: 20px;
    margin-top: 30px;
    color: #6b7280;
    font-size: 0.9rem;
}

.help-section h3 {
    color: #374151;
    margin-bottom: 10px;
}
</style>