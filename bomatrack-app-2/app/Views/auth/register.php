<?php $title = 'Create Account - BomaTrack'; ?>
<div class="auth-card">
    <h2>Create Your BomaTrack Account</h2>
    <p class="auth-subtitle">Join thousands of property managers using BomaTrack</p>
    
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
    
    <form action="/register" method="POST" class="form">
        <div class="name-row">
            <div class="form-group">
                <label for="first_name">First Name</label>
                <input 
                    type="text" 
                    id="first_name" 
                    name="first_name" 
                    value="<?php echo htmlspecialchars($first_name ?? ''); ?>"
                    placeholder="Enter your first name"
                >
                <?php if (!empty($errors['first_name'])): ?>
                    <span class="error-text"><?php echo htmlspecialchars($errors['first_name']); ?></span>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="last_name">Last Name</label>
                <input 
                    type="text" 
                    id="last_name" 
                    name="last_name" 
                    value="<?php echo htmlspecialchars($last_name ?? ''); ?>"
                    placeholder="Enter your last name"
                >
                <?php if (!empty($errors['last_name'])): ?>
                    <span class="error-text"><?php echo htmlspecialchars($errors['last_name']); ?></span>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="form-group">
            <label for="username">Username</label>
            <input 
                type="text" 
                id="username" 
                name="username" 
                value="<?php echo htmlspecialchars($username ?? ''); ?>"
                required
                placeholder="Choose a unique username"
                class="<?php echo !empty($errors['username']) ? 'error' : ''; ?>"
            >
            <?php if (!empty($errors['username'])): ?>
                <span class="error-text"><?php echo htmlspecialchars($errors['username']); ?></span>
            <?php endif; ?>
        </div>
        
        <div class="form-group">
            <label for="email">Email Address</label>
            <input 
                type="email" 
                id="email" 
                name="email" 
                value="<?php echo htmlspecialchars($email ?? ''); ?>"
                required
                placeholder="Enter your email address"
                class="<?php echo !empty($errors['email']) ? 'error' : ''; ?>"
            >
            <?php if (!empty($errors['email'])): ?>
                <span class="error-text"><?php echo htmlspecialchars($errors['email']); ?></span>
            <?php endif; ?>
        </div>
        
        <div class="form-group">
            <label for="password">Password</label>
            <input 
                type="password" 
                id="password" 
                name="password" 
                required
                placeholder="Create a strong password"
                class="<?php echo !empty($errors['password']) ? 'error' : ''; ?>"
            >
            <div class="password-requirements">
                <small>Password must contain at least 8 characters with uppercase, lowercase, and numbers</small>
            </div>
            <?php if (!empty($errors['password'])): ?>
                <span class="error-text"><?php echo htmlspecialchars($errors['password']); ?></span>
            <?php endif; ?>
        </div>
        
        <div class="form-group">
            <label for="confirm_password">Confirm Password</label>
            <input 
                type="password" 
                id="confirm_password" 
                name="confirm_password" 
                required
                placeholder="Repeat your password"
                class="<?php echo !empty($errors['confirm_password']) ? 'error' : ''; ?>"
            >
            <?php if (!empty($errors['confirm_password'])): ?>
                <span class="error-text"><?php echo htmlspecialchars($errors['confirm_password']); ?></span>
            <?php endif; ?>
        </div>
        
        <div class="terms-section">
            <label class="checkbox-label">
                <input type="checkbox" required>
                <span class="checkmark"></span>
                I agree to the <a href="/terms" target="_blank">Terms of Service</a> and <a href="/privacy" target="_blank">Privacy Policy</a>
            </label>
        </div>
        
        <button type="submit" class="button primary full-width">Create Account</button>
    </form>
    
    <div class="auth-footer">
        <p>Already have an account? <a href="/login">Sign in here</a></p>
    </div>
</div>

<style>
.auth-subtitle {
    text-align: center;
    color: #6b7280;
    margin-bottom: 30px;
}

.name-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
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

.form-group {
    position: relative;
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

.password-requirements {
    margin-top: 4px;
}

.password-requirements small {
    color: #6b7280;
    font-size: 0.8rem;
}

.terms-section {
    margin: 20px 0;
}

.checkbox-label {
    display: flex;
    align-items: flex-start;
    cursor: pointer;
    font-size: 0.9rem;
    line-height: 1.4;
}

.checkbox-label input[type="checkbox"] {
    margin: 0 8px 0 0;
    transform: scale(1.1);
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
    margin: 10px 0;
}

.auth-footer {
    text-align: center;
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid #e5e7eb;
}

.auth-footer p {
    color: #6b7280;
    margin: 0;
}

.auth-footer a {
    color: #3b82f6;
    text-decoration: none;
    font-weight: 500;
}

.auth-footer a:hover {
    text-decoration: underline;
}
</style>