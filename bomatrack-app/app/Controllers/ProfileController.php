<?php

require_once __DIR__ . '/../Helpers/AuthHelper.php';

class ProfileController extends BaseController
{
    public function show()
    {
        // Require authentication and email verification
        AuthHelper::requireEmailVerification();
        
        // Get current user for profile data
        $currentUser = AuthHelper::getCurrentUser();
        
        // Render the profile view with user data
        $this->render('users/profile', ['user' => $currentUser]);
    }
}
