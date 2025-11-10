<?php
require_once '../app/Core/App.php';
require_once '../app/Core/Router.php';
require_once '../app/Core/Database.php';
require_once '../app/Controllers/BaseController.php';
require_once '../app/Controllers/DashboardController.php';
require_once '../app/Controllers/AuthController.php';
require_once '../app/Controllers/ProfileController.php';
require_once '../app/Controllers/PropertyController.php';
require_once '../app/Controllers/UnitController.php';
require_once '../app/Controllers/TenantController.php';

// Initialize the application
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

// Property routes
$router->addRoute('GET', '/properties', 'PropertyController', 'index');
$router->addRoute('GET', '/properties/create', 'PropertyController', 'create');
$router->addRoute('POST', '/properties/create', 'PropertyController', 'create');
$router->addRoute('GET', '/properties/edit', 'PropertyController', 'edit');
$router->addRoute('POST', '/properties/edit', 'PropertyController', 'edit');
$router->addRoute('POST', '/properties/delete', 'PropertyController', 'delete');

// Unit routes
$router->addRoute('GET', '/units', 'UnitController', 'index');
$router->addRoute('GET', '/units/create', 'UnitController', 'create');
$router->addRoute('POST', '/units/create', 'UnitController', 'create');
$router->addRoute('GET', '/units/edit', 'UnitController', 'edit');
$router->addRoute('POST', '/units/edit', 'UnitController', 'edit');
$router->addRoute('POST', '/units/delete', 'UnitController', 'delete');

// Tenant routes
$router->addRoute('GET', '/tenants', 'TenantController', 'index');
$router->addRoute('GET', '/tenants/create', 'TenantController', 'create');
$router->addRoute('POST', '/tenants/create', 'TenantController', 'create');
$router->addRoute('GET', '/tenants/edit', 'TenantController', 'edit');
$router->addRoute('POST', '/tenants/edit', 'TenantController', 'edit');
$router->addRoute('POST', '/tenants/delete', 'TenantController', 'delete');

// Handle the incoming request
$app->run();
?>