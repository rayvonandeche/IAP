<?php 
$title = 'User Profile';
$user = $data['user'] ?? null;
?>

<div class="profile-card">
    <h2>User Profile</h2>
    
    <?php if ($user): ?>
        <div class="profile-section">
            <h3>Account Information</h3>
            <dl class="definition-list">
                <dt>Username</dt>
                <dd><?= htmlspecialchars($user->getUsername()) ?></dd>
                
                <dt>Email</dt>
                <dd>
                    <?= htmlspecialchars($user->getEmail()) ?>
                    <?php if ($user->isEmailVerified()): ?>
                        <span class="verified-badge">✓ Verified</span>
                    <?php else: ?>
                        <span class="unverified-badge">⚠ Not Verified</span>
                    <?php endif; ?>
                </dd>
                
                <?php if ($user->getFirstName() || $user->getLastName()): ?>
                <dt>Full Name</dt>
                <dd><?= htmlspecialchars(trim($user->getFirstName() . ' ' . $user->getLastName())) ?></dd>
                <?php endif; ?>
                
                <?php if ($user->getPhone()): ?>
                <dt>Phone</dt>
                <dd><?= htmlspecialchars($user->getPhone()) ?></dd>
                <?php endif; ?>
                
                <dt>Member Since</dt>
                <dd><?= date('F j, Y', strtotime($user->getCreatedAt())) ?></dd>
                
                <?php if ($user->getLastLoginAt()): ?>
                <dt>Last Login</dt>
                <dd><?= date('F j, Y \a\t g:i A', strtotime($user->getLastLoginAt())) ?></dd>
                <?php endif; ?>
            </dl>
        </div>
        
        <div class="profile-actions">
            <a class="button" href="/dashboard">Back to Dashboard</a>
            <?php if (!$user->isEmailVerified()): ?>
                <a class="button button-warning" href="/resend-verification">Verify Email</a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="error-message">
            <p>Unable to load profile information. Please try logging in again.</p>
            <a class="button" href="/login">Login</a>
        </div>
    <?php endif; ?>
</div>