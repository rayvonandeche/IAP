<?php

require_once __DIR__ . '/../Helpers/AuthHelper.php';
require_once __DIR__ . '/../Models/Property.php';
require_once __DIR__ . '/../Models/Unit.php';

class PropertyController extends BaseController
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

        $propertyModel = new Property();
        $properties = $propertyModel->getAllByOwner($ownerId);
        $summary = $propertyModel->getSummary($ownerId);

        $totals = [
            'units_count' => 0,
            'occupied_units' => 0,
            'vacant_units' => 0,
            'maintenance_units' => 0,
            'rent_roll' => 0.0,
        ];

        foreach ($properties as $property) {
            $totals['units_count'] += (int) ($property['units_count'] ?? 0);
            $totals['occupied_units'] += (int) ($property['occupied_units'] ?? 0);
            $totals['vacant_units'] += (int) ($property['vacant_units'] ?? 0);
            $totals['maintenance_units'] += (int) ($property['maintenance_units'] ?? 0);
            $totals['rent_roll'] += (float) ($property['total_rent_roll'] ?? 0);
        }

        $this->render('properties/index', [
            'user' => $currentUser,
            'properties' => $properties,
            'summary' => $summary,
            'totals' => $totals,
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
            $this->handleCreate($currentUser->getId());
            return;
        }

        $this->render('properties/create', [
            'user' => $currentUser,
            'errors' => [],
            'old' => [],
        ]);
    }

    private function handleCreate(int $ownerId)
    {
        $errors = [];
        $old = $_POST;

        // Validate required fields
        if (empty($_POST['name'])) {
            $errors['name'] = 'Property name is required';
        }
        if (empty($_POST['address'])) {
            $errors['address'] = 'Address is required';
        }
        if (empty($_POST['city'])) {
            $errors['city'] = 'City is required';
        }
        if (empty($_POST['state'])) {
            $errors['state'] = 'State/Region is required';
        }
        if (empty($_POST['postal_code'])) {
            $errors['postal_code'] = 'Postal code is required';
        }

        // Validate floor/unit logic
        $numFloors = isset($_POST['num_floors']) ? (int) $_POST['num_floors'] : 0;
        $unitsPerFloor = isset($_POST['units_per_floor']) ? (int) $_POST['units_per_floor'] : 0;

        if ($numFloors < 1) {
            $errors['num_floors'] = 'At least 1 floor is required';
        }
        if ($unitsPerFloor < 1) {
            $errors['units_per_floor'] = 'At least 1 unit per floor is required';
        }

        $totalUnits = $numFloors * $unitsPerFloor;

        if (!empty($errors)) {
            $this->render('properties/create', [
                'user' => AuthHelper::getCurrentUser(),
                'errors' => $errors,
                'old' => $old,
            ]);
            return;
        }

        // Create property
        $propertyModel = new Property();
        $propertyData = [
            'owner_id' => $ownerId,
            'name' => $_POST['name'],
            'address' => $_POST['address'],
            'city' => $_POST['city'],
            'state' => $_POST['state'],
            'postal_code' => $_POST['postal_code'],
            'country' => $_POST['country'] ?? 'Kenya',
            'property_type' => $_POST['property_type'] ?? 'apartment',
            'total_units' => $totalUnits,
            'description' => $_POST['description'] ?? null,
        ];

        $propertyId = $propertyModel->create($propertyData);

        if (!$propertyId) {
            $errors['general'] = 'Failed to create property. Please try again.';
            $this->render('properties/create', [
                'user' => AuthHelper::getCurrentUser(),
                'errors' => $errors,
                'old' => $old,
            ]);
            return;
        }

        // Auto-generate units based on floors and units per floor
        $unitModel = new Unit();
        $units = [];

        $defaultRent = isset($_POST['default_rent']) ? (float) $_POST['default_rent'] : 0;
        $defaultDeposit = isset($_POST['default_deposit']) ? (float) $_POST['default_deposit'] : 0;
        $defaultBedrooms = isset($_POST['default_bedrooms']) ? (int) $_POST['default_bedrooms'] : 1;
        $defaultBathrooms = isset($_POST['default_bathrooms']) ? (float) $_POST['default_bathrooms'] : 1.0;

        for ($floor = 1; $floor <= $numFloors; $floor++) {
            for ($unitNum = 1; $unitNum <= $unitsPerFloor; $unitNum++) {
                // Generate unit number like "F1-U01", "F2-U03", etc.
                $unitNumber = sprintf('F%d-U%02d', $floor, $unitNum);

                $units[] = [
                    'property_id' => $propertyId,
                    'unit_number' => $unitNumber,
                    'bedrooms' => $defaultBedrooms,
                    'bathrooms' => $defaultBathrooms,
                    'square_feet' => null,
                    'rent_amount' => $defaultRent,
                    'deposit_amount' => $defaultDeposit,
                    'status' => 'vacant',
                    'description' => null,
                ];
            }
        }

        $unitModel->bulkCreate($units);

        // Redirect to properties list
        header('Location: /properties?created=1');
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

        $propertyId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        if ($propertyId < 1) {
            header('Location: /properties');
            exit();
        }

        $propertyModel = new Property();
        $property = $propertyModel->findById($propertyId, $currentUser->getId());

        if (!$property) {
            header('Location: /properties');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleEdit($propertyId, $currentUser->getId());
            return;
        }

        $this->render('properties/edit', [
            'user' => $currentUser,
            'property' => $property,
            'errors' => [],
        ]);
    }

    private function handleEdit(int $propertyId, int $ownerId)
    {
        $errors = [];

        // Validate required fields
        if (empty($_POST['name'])) {
            $errors['name'] = 'Property name is required';
        }
        if (empty($_POST['address'])) {
            $errors['address'] = 'Address is required';
        }
        if (empty($_POST['city'])) {
            $errors['city'] = 'City is required';
        }
        if (empty($_POST['state'])) {
            $errors['state'] = 'State/Region is required';
        }
        if (empty($_POST['postal_code'])) {
            $errors['postal_code'] = 'Postal code is required';
        }

        $totalUnits = isset($_POST['total_units']) ? (int) $_POST['total_units'] : 1;

        if (!empty($errors)) {
            $propertyModel = new Property();
            $property = $propertyModel->findById($propertyId, $ownerId);

            $this->render('properties/edit', [
                'user' => AuthHelper::getCurrentUser(),
                'property' => $property,
                'errors' => $errors,
            ]);
            return;
        }

        // Update property
        $propertyModel = new Property();
        $propertyData = [
            'name' => $_POST['name'],
            'address' => $_POST['address'],
            'city' => $_POST['city'],
            'state' => $_POST['state'],
            'postal_code' => $_POST['postal_code'],
            'country' => $_POST['country'] ?? 'Kenya',
            'property_type' => $_POST['property_type'] ?? 'apartment',
            'total_units' => $totalUnits,
            'description' => $_POST['description'] ?? null,
        ];

        $success = $propertyModel->update($propertyId, $ownerId, $propertyData);

        if ($success) {
            header('Location: /properties?updated=1');
        } else {
            header('Location: /properties/edit?id=' . $propertyId . '&error=1');
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
            header('Location: /properties');
            exit();
        }

        $propertyId = isset($_POST['id']) ? (int) $_POST['id'] : 0;
        if ($propertyId < 1) {
            header('Location: /properties');
            exit();
        }

        $propertyModel = new Property();
        $success = $propertyModel->delete($propertyId, $currentUser->getId());

        if ($success) {
            header('Location: /properties?deleted=1');
        } else {
            header('Location: /properties?error=delete_failed');
        }
        exit();
    }
}
