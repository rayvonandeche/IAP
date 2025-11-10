<?php

require_once __DIR__ . '/../Helpers/AuthHelper.php';
require_once __DIR__ . '/../Models/Payment.php';
require_once __DIR__ . '/../Models/Tenancy.php';

class PaymentController extends BaseController
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
        $paymentModel = new Payment();

        $payments = $paymentModel->getAllByOwner($ownerId);
        $summary = $paymentModel->getSummary($ownerId);

        $this->render('payments/index', [
            'user' => $currentUser,
            'payments' => $payments,
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

        // Get active tenancies
        $ownerId = $currentUser->getId();
        $db = Database::getInstance();
        $sql = "SELECT ten.id, ten.rent_amount,
                       t.first_name, t.last_name,
                       u.unit_number,
                       p.name AS property_name
                FROM tenancies ten
                INNER JOIN tenants t ON ten.tenant_id = t.id
                INNER JOIN units u ON ten.unit_id = u.id
                INNER JOIN properties p ON u.property_id = p.id
                WHERE p.owner_id = ? AND ten.status = 'active'
                ORDER BY p.name, u.unit_number";
        
        $stmt = $db->getConnection()->prepare($sql);
        $stmt->bind_param('i', $ownerId);
        $stmt->execute();
        $result = $stmt->get_result();
        $activeTenancies = [];
        while ($row = $result->fetch_assoc()) {
            $activeTenancies[] = $row;
        }

        $this->render('payments/create', [
            'user' => $currentUser,
            'errors' => [],
            'old' => [],
            'activeTenancies' => $activeTenancies,
        ]);
    }

    private function handleCreate()
    {
        $errors = [];
        $old = $_POST;

        // Validate
        if (empty($_POST['tenancy_id'])) {
            $errors['tenancy_id'] = 'Please select a tenant';
        }
        if (empty($_POST['amount']) || (float) $_POST['amount'] <= 0) {
            $errors['amount'] = 'Valid amount is required';
        }
        if (empty($_POST['payment_date'])) {
            $errors['payment_date'] = 'Payment date is required';
        }

        if (!empty($errors)) {
            // Re-fetch tenancies for form
            $currentUser = AuthHelper::getCurrentUser();
            $ownerId = $currentUser->getId();
            $db = Database::getInstance();
            $sql = "SELECT ten.id, ten.rent_amount,
                           t.first_name, t.last_name,
                           u.unit_number,
                           p.name AS property_name
                    FROM tenancies ten
                    INNER JOIN tenants t ON ten.tenant_id = t.id
                    INNER JOIN units u ON ten.unit_id = u.id
                    INNER JOIN properties p ON u.property_id = p.id
                    WHERE p.owner_id = ? AND ten.status = 'active'
                    ORDER BY p.name, u.unit_number";
            
            $stmt = $db->getConnection()->prepare($sql);
            $stmt->bind_param('i', $ownerId);
            $stmt->execute();
            $result = $stmt->get_result();
            $activeTenancies = [];
            while ($row = $result->fetch_assoc()) {
                $activeTenancies[] = $row;
            }

            $this->render('payments/create', [
                'user' => AuthHelper::getCurrentUser(),
                'errors' => $errors,
                'old' => $old,
                'activeTenancies' => $activeTenancies,
            ]);
            return;
        }

        // Create payment
        $paymentModel = new Payment();
        $paymentData = [
            'tenancy_id' => (int) $_POST['tenancy_id'],
            'amount' => (float) $_POST['amount'],
            'payment_type' => $_POST['payment_type'] ?? 'rent',
            'payment_method' => $_POST['payment_method'] ?? 'cash',
            'payment_reference' => $_POST['payment_reference'] ?? null,
            'payment_date' => $_POST['payment_date'],
            'status' => 'completed',
            'notes' => $_POST['notes'] ?? null,
        ];

        $paymentId = $paymentModel->create($paymentData);

        if ($paymentId) {
            header('Location: /payments?created=1');
        } else {
            header('Location: /payments/create?error=1');
        }
        exit();
    }

    public function delete()
    {
        AuthHelper::requireEmailVerification();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /payments');
            exit();
        }

        $paymentId = isset($_POST['id']) ? (int) $_POST['id'] : 0;
        if ($paymentId < 1) {
            header('Location: /payments');
            exit();
        }

        $paymentModel = new Payment();
        $success = $paymentModel->delete($paymentId);

        if ($success) {
            header('Location: /payments?deleted=1');
        } else {
            header('Location: /payments?error=delete_failed');
        }
        exit();
    }
}
