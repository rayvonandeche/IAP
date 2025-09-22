<?php

require_once __DIR__ . '/../Helpers/AuthHelper.php';
require_once __DIR__ .'/BaseController.php';
class DashboardController extends BaseController
{
    public function showDashboard()
    {
        // Require authentication and email verification
        AuthHelper::requireEmailVerification();
        
        // Get current user for dashboard data
        $currentUser = AuthHelper::getCurrentUser();
        
        // Render the dashboard view with user data
        $this->render('dashboard/index', ['user' => $currentUser]);
    }
}