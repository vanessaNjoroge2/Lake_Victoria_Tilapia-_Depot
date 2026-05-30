<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../controllers/AuditController.php';
require_once __DIR__ . '/../includes/sanitize.php';

// Verify authentication for staff/admin
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['admin', 'staff'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

class DebtController
{
    private $db;

    public function __construct()
    {
        try {
            $database = new Database();
            $this->db = $database->getConnection();
        } catch (Exception $e) {
            error_log("DebtController DB Init Error: " . $e->getMessage());
        }
    }

    public function handleRequest()
    {
        $action = $_GET['action'] ?? '';
        header('Content-Type: application/json');

        if (!$this->db) {
            echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
            exit;
        }

        switch ($action) {
            case 'get_wholesale_customers':
                $this->getWholesaleCustomers();
                break;
            case 'record_payment':
                $this->recordPayment();
                break;
            case 'update_credit_limit':
                $this->updateCreditLimit();
                break;
            default:
                echo json_encode(['success' => false, 'message' => 'Invalid Debt action.']);
                break;
        }
        exit;
    }

    private function getWholesaleCustomers()
    {
        try {
            $query = "SELECT id, full_name, email, phone, credit_limit, outstanding_balance, created_at 
                      FROM users 
                      WHERE role = 'customer' AND customer_type = 'wholesale' 
                      ORDER BY full_name ASC";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode(['success' => true, 'customers' => $customers]);
        } catch (Exception $e) {
            error_log("DebtController getWholesaleCustomers error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Failed to fetch wholesale customers.']);
        }
    }

    private function recordPayment()
    {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            echo json_encode(['success' => false, 'message' => 'Invalid pay payload.']);
            return;
        }

        $customerId = isset($input['customer_id']) ? (int)$input['customer_id'] : 0;
        $amount = isset($input['amount']) ? (float)$input['amount'] : 0.0;
        $notes = trim($input['notes'] ?? '');
        $paymentMethod = trim($input['payment_method'] ?? 'cash');

        if ($customerId <= 0 || $amount <= 0) {
            echo json_encode(['success' => false, 'message' => 'Valid Customer ID and a positive payment amount are required.']);
            return;
        }

        try {
            $this->db->beginTransaction();

            // Fetch customer details with locking
            $q = "SELECT id, full_name, outstanding_balance FROM users WHERE id = :id AND role = 'customer' AND customer_type = 'wholesale' FOR UPDATE";
            $s = $this->db->prepare($q);
            $s->execute([':id' => $customerId]);
            $customer = $s->fetch(PDO::FETCH_ASSOC);

            if (!$customer) {
                throw new Exception("Wholesale customer account not found.");
            }

            $currentBalance = (float)$customer['outstanding_balance'];
            $newBalance = max(0.00, $currentBalance - $amount);

            // Update outstanding balance
            $updQ = "UPDATE users SET outstanding_balance = :bal WHERE id = :id";
            $updStmt = $this->db->prepare($updQ);
            $updStmt->execute([':bal' => $newBalance, ':id' => $customerId]);

            // Save in debt ledger
            $ledgerQ = "INSERT INTO debt_ledger (customer_id, type, amount, balance_after, notes, recorded_by) 
                        VALUES (:cust_id, 'payment', :amount, :bal, :notes, :by)";
            $ledgerStmt = $this->db->prepare($ledgerQ);
            
            $fullNotes = "Payment received via " . strtoupper($paymentMethod) . "." . ($notes ? " Notes: " . $notes : "");
            
            $ledgerStmt->execute([
                ':cust_id' => $customerId,
                ':amount' => $amount,
                ':bal' => $newBalance,
                ':notes' => $fullNotes,
                ':by' => $_SESSION['user_id']
            ]);

            $ledgerId = $this->db->lastInsertId();

            // Log activity
            AuditController::logActivity(
                $_SESSION['user_id'],
                "wholesale_debt_payment",
                "users",
                $customerId,
                ['outstanding_balance' => $currentBalance],
                ['outstanding_balance' => $newBalance, 'payment_amount' => $amount, 'payment_method' => $paymentMethod]
            );

            $this->db->commit();
            echo json_encode([
                'success' => true, 
                'message' => 'Repayment successfully processed.',
                'new_balance' => $newBalance
            ]);

        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Repayment Transaction Failed: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    private function updateCreditLimit()
    {
        // Enforce admin-only for credit limit changes
        if (($_SESSION['role'] ?? '') !== 'admin') {
            echo json_encode(['success' => false, 'message' => 'Access denied. Only administrators can alter customer credit limits.']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            echo json_encode(['success' => false, 'message' => 'Invalid configuration payload.']);
            return;
        }

        $customerId = isset($input['customer_id']) ? (int)$input['customer_id'] : 0;
        $limit = isset($input['credit_limit']) ? (float)$input['credit_limit'] : 0.0;

        if ($customerId <= 0 || $limit < 0) {
            echo json_encode(['success' => false, 'message' => 'Valid Customer ID and positive limit are required.']);
            return;
        }

        try {
            $this->db->beginTransaction();

            $q = "SELECT id, full_name, credit_limit FROM users WHERE id = :id AND role = 'customer' AND customer_type = 'wholesale' FOR UPDATE";
            $s = $this->db->prepare($q);
            $s->execute([':id' => $customerId]);
            $customer = $s->fetch(PDO::FETCH_ASSOC);

            if (!$customer) {
                throw new Exception("Wholesale customer account not found.");
            }

            $oldLimit = (float)$customer['credit_limit'];

            // Update credit limit
            $updQ = "UPDATE users SET credit_limit = :limit WHERE id = :id";
            $updStmt = $this->db->prepare($updQ);
            $updStmt->execute([':limit' => $limit, ':id' => $customerId]);

            // Log activity
            AuditController::logActivity(
                $_SESSION['user_id'],
                "wholesale_credit_limit_updated",
                "users",
                $customerId,
                ['credit_limit' => $oldLimit],
                ['credit_limit' => $limit]
            );

            $this->db->commit();
            echo json_encode(['success' => true, 'message' => 'Credit limit updated successfully.', 'new_limit' => $limit]);

        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Update Credit Limit Failed: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}

// Handle request routing if called directly
if (basename($_SERVER['PHP_SELF']) === 'DebtController.php') {
    $controller = new DebtController();
    $controller->handleRequest();
}
