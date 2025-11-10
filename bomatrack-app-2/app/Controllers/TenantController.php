<?php

require_once __DIR__ . '/../Helpers/AuthHelper.php';
require_once __DIR__ . '/../Models/Tenant.php';

class TenantController extends BaseController
{
    public function index()
    {
        AuthHelper::requireEmailVerification();

        $currentUser = AuthHelper::getCurrentUser();
        if (!$currentUser) {
            header('Location: /login');
            exit();
        }

        $ownerId = $currentUser->getId();
        $tenantModel = new Tenant();

        $tenants = $tenantModel->getAllByOwner($ownerId);
        $summary = $tenantModel->getSummary($ownerId);

        $this->render('tenants/index', [
            'user' => $currentUser,
            'tenants' => $tenants,
            'summary' => $summary,
        ]);
    }

    public function create()
    {
        AuthHelper::requireEmailVerification();

        $currentUser = AuthHelper::getCurrentUser();
        if (!$currentUser) {
            header('Location: /login');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleCreate();
            return;
        }

        // Get vacant units for assignment
        require_once __DIR__ . '/../Models/Unit.php';
        $unitModel = new Unit();
        $vacantUnits = $unitModel->getVacantUnitsByOwner($currentUser->getId());

        $this->render('tenants/create', [
            'user' => $currentUser,
            'errors' => [],
            'old' => [],
            'vacantUnits' => $vacantUnits,
        ]);
    }

    private function handleCreate()
    {
        $errors = [];
        $old = $_POST;

        // Validate required fields
        if (empty($_POST['first_name'])) {
            $errors['first_name'] = 'First name is required';
        }
        if (empty($_POST['last_name'])) {
            $errors['last_name'] = 'Last name is required';
        }
        if (empty($_POST['email'])) {
            $errors['email'] = 'Email is required';
        } elseif (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Valid email is required';
        }
        if (empty($_POST['phone'])) {
            $errors['phone'] = 'Phone number is required';
        }

        // Validate unit assignment if provided
        $assignUnit = !empty($_POST['unit_id']);
        if ($assignUnit) {
            if (empty($_POST['start_date'])) {
                $errors['start_date'] = 'Start date is required when assigning a unit';
            }
            if (empty($_POST['rent_amount']) || (float) $_POST['rent_amount'] <= 0) {
                $errors['rent_amount'] = 'Valid rent amount is required';
            }
        }

        // Check for duplicate email
        if (empty($errors['email'])) {
            $tenantModel = new Tenant();
            if ($tenantModel->findByEmail($_POST['email'])) {
                $errors['email'] = 'A tenant with this email already exists';
            }
        }

        if (!empty($errors)) {
            // Get vacant units for re-rendering the form
            require_once __DIR__ . '/../Models/Unit.php';
            $unitModel = new Unit();
            $vacantUnits = $unitModel->getVacantUnitsByOwner(AuthHelper::getCurrentUser()->getId());

            $this->render('tenants/create', [
                'user' => AuthHelper::getCurrentUser(),
                'errors' => $errors,
                'old' => $old,
                'vacantUnits' => $vacantUnits,
            ]);
            return;
        }

        // Create tenant
        $tenantModel = new Tenant();
        $tenantData = [
            'user_id' => null, // Can be linked to a user account later
            'first_name' => $_POST['first_name'],
            'last_name' => $_POST['last_name'],
            'email' => $_POST['email'],
            'phone' => $_POST['phone'],
            'national_id' => $_POST['national_id'] ?? null,
            'emergency_contact_name' => $_POST['emergency_contact_name'] ?? null,
            'emergency_contact_phone' => $_POST['emergency_contact_phone'] ?? null,
            'employment_info' => $_POST['employment_info'] ?? null,
        ];

        $tenantId = $tenantModel->create($tenantData);

        if (!$tenantId) {
            header('Location: /tenants/create?error=1');
            exit();
        }

        // Create tenancy if unit was assigned
        if ($assignUnit) {
            require_once __DIR__ . '/../Models/Tenancy.php';
            require_once __DIR__ . '/../Models/Unit.php';
            
            $tenancyModel = new Tenancy();
            $unitModel = new Unit();

            $tenancyData = [
                'tenant_id' => $tenantId,
                'unit_id' => (int) $_POST['unit_id'],
                'start_date' => $_POST['start_date'],
                'end_date' => !empty($_POST['end_date']) ? $_POST['end_date'] : null,
                'rent_amount' => (float) $_POST['rent_amount'],
                'deposit_paid' => !empty($_POST['deposit_paid']) ? (float) $_POST['deposit_paid'] : 0.00,
                'status' => 'active',
                'lease_terms' => $_POST['lease_terms'] ?? null,
            ];

            $tenancyId = $tenancyModel->create($tenancyData);

            if ($tenancyId) {
                // Update unit status to occupied
                $unitModel->update((int) $_POST['unit_id'], [
                    'status' => 'occupied',
                ]);
            }
        }

        header('Location: /tenants?created=1');
        exit();
    }

    public function edit()
    {
        AuthHelper::requireEmailVerification();

        $currentUser = AuthHelper::getCurrentUser();
        if (!$currentUser) {
            header('Location: /login');
            exit();
        }

        $tenantId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        if ($tenantId < 1) {
            header('Location: /tenants');
            exit();
        }

        $tenantModel = new Tenant();
        $tenant = $tenantModel->findById($tenantId);

        if (!$tenant) {
            header('Location: /tenants');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleEdit($tenantId);
            return;
        }

        $this->render('tenants/edit', [
            'user' => $currentUser,
            'tenant' => $tenant,
            'errors' => [],
        ]);
    }

    private function handleEdit(int $tenantId)
    {
        $errors = [];

        // Validate required fields
        if (empty($_POST['first_name'])) {
            $errors['first_name'] = 'First name is required';
        }
        if (empty($_POST['last_name'])) {
            $errors['last_name'] = 'Last name is required';
        }
        if (empty($_POST['email'])) {
            $errors['email'] = 'Email is required';
        } elseif (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Valid email is required';
        }
        if (empty($_POST['phone'])) {
            $errors['phone'] = 'Phone number is required';
        }

        // Check for duplicate email (excluding current tenant)
        if (empty($errors['email'])) {
            $tenantModel = new Tenant();
            $existing = $tenantModel->findByEmail($_POST['email']);
            if ($existing && (int) $existing['id'] !== $tenantId) {
                $errors['email'] = 'A tenant with this email already exists';
            }
        }

        if (!empty($errors)) {
            $tenantModel = new Tenant();
            $tenant = $tenantModel->findById($tenantId);

            $this->render('tenants/edit', [
                'user' => AuthHelper::getCurrentUser(),
                'tenant' => $tenant,
                'errors' => $errors,
            ]);
            return;
        }

        // Update tenant
        $tenantModel = new Tenant();
        $tenantData = [
            'first_name' => $_POST['first_name'],
            'last_name' => $_POST['last_name'],
            'email' => $_POST['email'],
            'phone' => $_POST['phone'],
            'national_id' => $_POST['national_id'] ?? null,
            'emergency_contact_name' => $_POST['emergency_contact_name'] ?? null,
            'emergency_contact_phone' => $_POST['emergency_contact_phone'] ?? null,
            'employment_info' => $_POST['employment_info'] ?? null,
        ];

        $success = $tenantModel->update($tenantId, $tenantData);

        if ($success) {
            header('Location: /tenants?updated=1');
        } else {
            header('Location: /tenants/edit?id=' . $tenantId . '&error=1');
        }
        exit();
    }

    public function delete()
    {
        AuthHelper::requireEmailVerification();

        $currentUser = AuthHelper::getCurrentUser();
        if (!$currentUser) {
            header('Location: /login');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /tenants');
            exit();
        }

        $tenantId = isset($_POST['id']) ? (int) $_POST['id'] : 0;
        if ($tenantId < 1) {
            header('Location: /tenants');
            exit();
        }

        $tenantModel = new Tenant();
        $success = $tenantModel->delete($tenantId);

        if ($success) {
            header('Location: /tenants?deleted=1');
        } else {
            header('Location: /tenants?error=delete_failed');
        }
        exit();
    }
}
