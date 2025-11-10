<?php $title = 'Registration Successful'; ?>
<div class="auth-card">
    <div class="success-icon">
        <svg width="48" height="48" fill="#10b981" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
        </svg>
    </div>

    <h2>Welcome to Bomatrack, <?php echo htmlspecialchars($username); ?>!</h2>
    
    <p class="success-message">
        Your account has been created successfully. We've sent a verification email to 
        <strong><?php echo htmlspecialchars($email); ?></strong>
    </p>

    <div class="info-box">
        <h3>What's next?</h3>
        <ol>
            <li>Check your email inbox (and spam folder)</li>
            <li>Click the verification link in the email</li>
            <li>Return here to login and start collaborating!</li>
        </ol>
    </div>

    <div class="action-buttons">
        <a href="/login" class="button">Go to Login</a>
        <a href="/" class="button secondary">Back to Home</a>
    </div>

    <p class="muted">
        Didn't receive the email? 
        <form method="POST" action="/resend-verification" style="display: inline;">
            <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
            <button type="submit" class="link-button">Send again</button>
        </form>
    </p>
</div>

<style>
    .success-icon {
        text-align: center;
        margin-bottom: 16px;
    }
    
    .success-message {
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
        color: #166534;
        padding: 12px;
        border-radius: 8px;
        margin-bottom: 20px;
    }
    
    .info-box {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        padding: 16px;
        border-radius: 8px;
        margin-bottom: 20px;
    }
    
    .info-box h3 {
        margin-top: 0;
        color: #374151;
    }
    
    .info-box ol {
        margin-bottom: 0;
        padding-left: 20px;
    }
    
    .info-box li {
        margin-bottom: 8px;
    }
    
    .action-buttons {
        display: flex;
        gap: 12px;
        margin-bottom: 20px;
    }
    
    .button.secondary {
        background: #6b7280;
    }
    
    .button.secondary:hover {
        background: #4b5563;
    }
    
    .link-button {
        background: none;
        border: none;
        color: #3b82f6;
        text-decoration: underline;
        cursor: pointer;
        padding: 0;
        font-size: inherit;
    }
    
    .link-button:hover {
        color: #2563eb;
    }
</style>