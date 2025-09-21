<<?php

$app = new App();

// Set up routes
$router = $app->getRouter();
$router->addRoute('GET', '/', 'DashboardController', 'showDashboard');
$router->addRoute('GET', '/dashboard', 'DashboardController', 'showDashboard');

// Authentication routes
$router->addRoute('GET', '/login', 'AuthController', 'login');
$router->addRoute('POST', '/login', 'AuthController', 'login');
$router->addRoute('GET', '/register', 'AuthController', 'register');
$router->addRoute('POST', '/register', 'AuthController', 'register');
$router->addRoute('GET', '/verify-email', 'AuthController', 'verifyEmail');
$router->addRoute('GET', '/resend-verification', 'AuthController', 'resendVerification');
$router->addRoute('POST', '/resend-verification', 'AuthController', 'resendVerification');
$router->addRoute('GET', '/logout', 'AuthController', 'logout');

// Profile routes
$router->addRoute('GET', '/profile', 'ProfileController', 'show');

// Handle the incoming request
$app->run();