<?php

require_once __DIR__ . '/../Helpers/AuthHelper.php';
require_once __DIR__ . '/../Models/Unit.php';
require_once __DIR__ . '/../Models/Property.php';

class UnitController extends BaseController
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
        $propertyId = isset($_GET['property_id']) ? (int) $_GET['property_id'] : 0;

        $propertyModel = new Property();
        $unitModel = new Unit();

        // If property_id is specified, filter by that property
        if ($propertyId > 0) {
            $property = $propertyModel->findById($propertyId, $ownerId);
            if (!$property) {
                header('Location: /units');
                exit();
            }

            $units = $unitModel->getAllByProperty($propertyId);
            $properties = [$property];
        } else {
            // Show all units across all properties
            $properties = $propertyModel->getAllByOwner($ownerId);
            $units = [];
            
            foreach ($properties as $property) {
                $propertyUnits = $unitModel->getAllByProperty($property['id']);
                foreach ($propertyUnits as $unit) {
                    $unit['property_name'] = $property['name'];
                    $unit['property_type'] = $property['property_type'];
                    $units[] = $unit;
                }
            }
        }

        // Calculate summary stats
        $summary = [
            'total' => count($units),
            'vacant' => 0,
            'occupied' => 0,
            'maintenance' => 0,
            'total_rent' => 0.0,
            'avg_rent' => 0.0,
        ];

        foreach ($units as $unit) {
            if (($unit['status'] ?? '') === 'vacant') {
                $summary['vacant']++;
            } elseif (($unit['status'] ?? '') === 'occupied') {
                $summary['occupied']++;
            } elseif (($unit['status'] ?? '') === 'maintenance') {
                $summary['maintenance']++;
            }
            $summary['total_rent'] += (float) ($unit['rent_amount'] ?? 0);
        }

        if ($summary['total'] > 0) {
            $summary['avg_rent'] = $summary['total_rent'] / $summary['total'];
        }

        $this->render('units/index', [
            'user' => $currentUser,
            'units' => $units,
            'summary' => $summary,
            'properties' => $properties,
            'selectedPropertyId' => $propertyId,
            'selectedProperty' => $propertyId > 0 ? ($property ?? null) : null,
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

        $propertyId = isset($_GET['property_id']) ? (int) $_GET['property_id'] : 0;
        
        $propertyModel = new Property();
        $properties = $propertyModel->getAllByOwner($currentUser->getId());

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleCreate($currentUser->getId());
            return;
        }

        $this->render('units/create', [
            'user' => $currentUser,
            'properties' => $properties,
            'errors' => [],
            'old' => ['property_id' => $propertyId],
        ]);
    }

    private function handleCreate(int $ownerId)
    {
        $errors = [];
        $old = $_POST;

        // Validate required fields
        if (empty($_POST['property_id'])) {
            $errors['property_id'] = 'Property is required';
        }
        if (empty($_POST['unit_number'])) {
            $errors['unit_number'] = 'Unit number is required';
        }
        if (empty($_POST['rent_amount']) || (float)$_POST['rent_amount'] < 0) {
            $errors['rent_amount'] = 'Valid rent amount is required';
        }

        $propertyId = (int) ($_POST['property_id'] ?? 0);
        
        // Verify property ownership
        $propertyModel = new Property();
        $property = $propertyModel->findById($propertyId, $ownerId);
        
        if (!$property) {
            $errors['property_id'] = 'Invalid property selected';
        }

        if (!empty($errors)) {
            $properties = $propertyModel->getAllByOwner($ownerId);
            $this->render('units/create', [
                'user' => AuthHelper::getCurrentUser(),
                'properties' => $properties,
                'errors' => $errors,
                'old' => $old,
            ]);
            return;
        }

        // Create unit
        $unitModel = new Unit();
        $unitData = [
            'property_id' => $propertyId,
            'unit_number' => $_POST['unit_number'],
            'bedrooms' => (int) ($_POST['bedrooms'] ?? 1),
            'bathrooms' => (float) ($_POST['bathrooms'] ?? 1.0),
            'square_feet' => !empty($_POST['square_feet']) ? (int) $_POST['square_feet'] : null,
            'rent_amount' => (float) $_POST['rent_amount'],
            'deposit_amount' => (float) ($_POST['deposit_amount'] ?? 0),
            'status' => $_POST['status'] ?? 'vacant',
            'description' => $_POST['description'] ?? null,
        ];

        $unitId = $unitModel->create($unitData);

        if ($unitId) {
            header('Location: /units?property_id=' . $propertyId . '&created=1');
        } else {
            header('Location: /units/create?property_id=' . $propertyId . '&error=1');
        }
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

        $unitId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        if ($unitId < 1) {
            header('Location: /units');
            exit();
        }

        $unitModel = new Unit();
        $unit = $unitModel->findById($unitId);

        if (!$unit) {
            header('Location: /units');
            exit();
        }

        // Verify property ownership
        $propertyModel = new Property();
        $property = $propertyModel->findById($unit['property_id'], $currentUser->getId());
        
        if (!$property) {
            header('Location: /units');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleEdit($unitId, $currentUser->getId());
            return;
        }

        $this->render('units/edit', [
            'user' => $currentUser,
            'unit' => $unit,
            'property' => $property,
            'errors' => [],
        ]);
    }

    private function handleEdit(int $unitId, int $ownerId)
    {
        $errors = [];

        // Validate required fields
        if (empty($_POST['unit_number'])) {
            $errors['unit_number'] = 'Unit number is required';
        }
        if (empty($_POST['rent_amount']) || (float)$_POST['rent_amount'] < 0) {
            $errors['rent_amount'] = 'Valid rent amount is required';
        }

        if (!empty($errors)) {
            $unitModel = new Unit();
            $unit = $unitModel->findById($unitId);
            
            $propertyModel = new Property();
            $property = $propertyModel->findById($unit['property_id'], $ownerId);

            $this->render('units/edit', [
                'user' => AuthHelper::getCurrentUser(),
                'unit' => $unit,
                'property' => $property,
                'errors' => $errors,
            ]);
            return;
        }

        // Update unit
        $unitModel = new Unit();
        $unitData = [
            'unit_number' => $_POST['unit_number'],
            'bedrooms' => (int) ($_POST['bedrooms'] ?? 1),
            'bathrooms' => (float) ($_POST['bathrooms'] ?? 1.0),
            'square_feet' => !empty($_POST['square_feet']) ? (int) $_POST['square_feet'] : null,
            'rent_amount' => (float) $_POST['rent_amount'],
            'deposit_amount' => (float) ($_POST['deposit_amount'] ?? 0),
            'status' => $_POST['status'] ?? 'vacant',
            'description' => $_POST['description'] ?? null,
        ];

        $success = $unitModel->update($unitId, $unitData);

        if ($success) {
            $unit = $unitModel->findById($unitId);
            header('Location: /units?property_id=' . $unit['property_id'] . '&updated=1');
        } else {
            header('Location: /units/edit?id=' . $unitId . '&error=1');
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
            header('Location: /units');
            exit();
        }

        $unitId = isset($_POST['id']) ? (int) $_POST['id'] : 0;
        if ($unitId < 1) {
            header('Location: /units');
            exit();
        }

        $unitModel = new Unit();
        $unit = $unitModel->findById($unitId);

        if (!$unit) {
            header('Location: /units?error=not_found');
            exit();
        }

        // Verify property ownership
        $propertyModel = new Property();
        $property = $propertyModel->findById($unit['property_id'], $currentUser->getId());
        
        if (!$property) {
            header('Location: /units?error=unauthorized');
            exit();
        }

        $success = $unitModel->delete($unitId);

        if ($success) {
            header('Location: /units?property_id=' . $unit['property_id'] . '&deleted=1');
        } else {
            header('Location: /units?property_id=' . $unit['property_id'] . '&error=delete_failed');
        }
        exit();
    }
}
