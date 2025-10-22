<?php

require_once __DIR__ . '/../Helpers/AuthHelper.php';
require_once __DIR__ . '/../Models/DashboardMetrics.php';

class DashboardController extends BaseController
{
    public function showDashboard()
    {
        // Require authentication and email verification
        AuthHelper::requireEmailVerification();
        
        // Get current user for dashboard data
        $currentUser = AuthHelper::getCurrentUser();
        if (!$currentUser) {
            header('Location: /login');
            exit();
        }

    $overview = $recentPayments = $expiringLeases = [];

    $ownerId = $currentUser->getId();
    $metrics = new DashboardMetrics();

    $overview = $metrics->getOverview($ownerId);
    $recentPayments = $metrics->getRecentPayments($ownerId);
    $expiringLeases = $metrics->getExpiringLeases($ownerId);
        
        // Render the dashboard view with user and metric data
        $this->render('dashboard/index', [
            'user' => $currentUser,
            'overview' => $overview,
            'recentPayments' => $recentPayments,
            'expiringLeases' => $expiringLeases,
        ]);
    }
}