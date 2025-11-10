<?php $title = 'Resend Verification Email'; ?>
<div class="auth-card">
    <h2>Resend Verification Email</h2>
    
    <?php if (!empty($message)): ?>
        <div class="message <?php echo $success ? 'success' : 'error'; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>
    
    <?php if (!$success): ?>
        <p class="muted">Enter your email address to receive a new verification email.</p>
        
        <form action="/resend-verification" method="POST" class="form">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    value="<?php echo htmlspecialchars($email ?? ''); ?>"
                    required
                    placeholder="Enter your email address"
                >
            </div>
            
            <button type="submit" class="button primary full-width">Resend Verification Email</button>
        </form>
    <?php else: ?>
        <div class="success-actions">
            <a href="/login" class="button primary">Back to Login</a>
        </div>
    <?php endif; ?>
    
    <div class="auth-links">
        <p>Remember your login details? <a href="/login">Sign in</a></p>
        <p>Need to create an account? <a href="/register">Register</a></p>
    </div>
</div>

<style>
.message {
    padding: 12px 16px;
    border-radius: 8px;
    margin-bottom: 20px;
    font-weight: 500;
}

.message.success {
    background: #d1fae5;
    color: #065f46;
    border: 1px solid #a7f3d0;
}

.message.error {
    background: #fee2e2;
    color: #991b1b;
    border: 1px solid #fecaca;
}

.button.primary {
    background: #3b82f6;
    color: white;
}

.button.primary:hover {
    background: #2563eb;
}

.button.full-width {
    width: 100%;
}

.success-actions {
    text-align: center;
    margin: 20px 0;
}

.auth-links {
    text-align: center;
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid #e5e7eb;
}

.auth-links p {
    margin: 8px 0;
    color: #6b7280;
    font-size: 0.9rem;
}

.auth-links a {
    color: #3b82f6;
    text-decoration: none;
}

.auth-links a:hover {
    text-decoration: underline;
}
</style>