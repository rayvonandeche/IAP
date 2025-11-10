<?php $title = 'Sign In - BomaTrack'; ?>
<div class="auth-card">
    <h2>Welcome Back</h2>
    <p class="auth-subtitle">Sign in to your BomaTrack account</p>
    
    <?php if (!empty($message)): ?>
        <div class="message success">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>
    
    <?php if (!empty($errors['general'])): ?>
        <div class="message error">
            <?php echo htmlspecialchars($errors['general']); ?>
        </div>
    <?php endif; ?>
    
    <form action="/login" method="POST" class="form">
        <div class="form-group">
            <label for="username">Username or Email</label>
            <input 
                type="text" 
                id="username" 
                name="username" 
                value="<?php echo htmlspecialchars($username ?? ''); ?>"
                required
                placeholder="Enter your username or email"
                class="<?php echo !empty($errors['username']) ? 'error' : ''; ?>"
            >
            <?php if (!empty($errors['username'])): ?>
                <span class="error-text"><?php echo htmlspecialchars($errors['username']); ?></span>
            <?php endif; ?>
        </div>
        
        <div class="form-group">
            <label for="password">Password</label>
            <input 
                type="password" 
                id="password" 
                name="password" 
                required
                placeholder="Enter your password"
                class="<?php echo !empty($errors['password']) ? 'error' : ''; ?>"
            >
            <?php if (!empty($errors['password'])): ?>
                <span class="error-text"><?php echo htmlspecialchars($errors['password']); ?></span>
            <?php endif; ?>
        </div>
        
        <div class="login-options">
            <label class="checkbox-label">
                <input type="checkbox" name="remember_me">
                <span class="checkmark"></span>
                Remember me
            </label>
            
            <a href="/forgot-password" class="forgot-link">Forgot password?</a>
        </div>
        
        <button type="submit" class="button primary full-width">Sign In</button>
    </form>
    
    <div class="auth-footer">
        <div class="verification-notice">
            <p class="muted">Didn't receive your verification email?</p>
            <a href="/resend-verification" class="resend-link">Resend verification email</a>
        </div>
        
        <div class="signup-link">
            <p>Don't have an account? <a href="/register">Create one here</a></p>
        </div>
    </div>
</div>

<style>
.auth-subtitle {
    text-align: center;
    color: #6b7280;
    margin-bottom: 30px;
}

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

.error-text {
    color: #ef4444;
    font-size: 0.875rem;
    margin-top: 4px;
    display: block;
}

input.error {
    border-color: #ef4444;
    background-color: #fef2f2;
}

.login-options {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin: 20px 0;
    flex-wrap: wrap;
    gap: 10px;
}

.checkbox-label {
    display: flex;
    align-items: center;
    cursor: pointer;
    font-size: 0.9rem;
}

.checkbox-label input[type="checkbox"] {
    margin: 0 8px 0 0;
    transform: scale(1.1);
}

.forgot-link {
    color: #3b82f6;
    text-decoration: none;
    font-size: 0.9rem;
}

.forgot-link:hover {
    text-decoration: underline;
}

.button.primary {
    background: #3b82f6;
    color: white;
    padding: 12px 24px;
}

.button.primary:hover {
    background: #2563eb;
}

.button.full-width {
    width: 100%;
    margin: 10px 0;
}

.auth-footer {
    margin-top: 30px;
    text-align: center;
}

.verification-notice {
    padding: 20px 0;
    border-top: 1px solid #e5e7eb;
    margin-bottom: 15px;
}

.verification-notice p {
    margin: 0 0 8px 0;
    font-size: 0.9rem;
}

.resend-link {
    color: #3b82f6;
    text-decoration: none;
    font-size: 0.9rem;
    font-weight: 500;
}

.resend-link:hover {
    text-decoration: underline;
}

.signup-link {
    border-top: 1px solid #e5e7eb;
    padding-top: 20px;
}

.signup-link p {
    color: #6b7280;
    margin: 0;
}

.signup-link a {
    color: #3b82f6;
    text-decoration: none;
    font-weight: 500;
}

.signup-link a:hover {
    text-decoration: underline;
}
</style>