<?php
require_once __DIR__ . '/../../Helpers/AuthHelper.php';

// Check authentication status
$isLoggedIn = AuthHelper::isLoggedIn();
$isEmailVerified = false;
$currentUser = null;

if ($isLoggedIn) {
    $currentUser = AuthHelper::getCurrentUser();
    $isEmailVerified = $currentUser && $currentUser->isEmailVerified();
}
?>

<header class="site-header">
    <div class="container header-inner">
        <h1 class="brand"><a href="/">BomaTrack</a></h1>
        <nav class="nav">
            <ul class="nav-list">
                <?php if (!$isLoggedIn): ?>
                    <!-- Show login/register for non-authenticated users -->
                    <li><a href="/login">Login</a></li>
                    <li><a href="/register">Register</a></li>
                <?php elseif ($isLoggedIn && !$isEmailVerified): ?>
                    <!-- Show limited options for unverified users -->
                    <li><a href="/verify-email" class="text-warning">Verify Email</a></li>
                    <li><a href="/resend-verification">Resend Verification</a></li>
                    <li><a href="/logout">Logout</a></li>
                <?php else: ?>
                    <!-- Show full navigation for verified users -->
                    <li><a href="/dashboard">Dashboard</a></li>
                    <li><a href="/properties">Properties</a></li>
                    <li><a href="/units">Units</a></li>
                    <li><a href="/tenants">Tenants</a></li>
                    <li><a href="/profile">Profile</a></li>
                    <li class="user-info">
                        Welcome, <?= htmlspecialchars($currentUser->getFirstName() ?? $currentUser->getUsername()) ?>
                    </li>
                    <li><a href="/logout">Logout</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
</header>