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

class PosController
{
    private $db;

    public function __construct()
    {
        try {
            $database = new Database();
            $this->db = $database->getConnection();
        } catch (Exception $e) {
            error_log("PosController DB Init Error: " . $e->getMessage());
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
            case 'get_products':
                $this->getProducts();
                break;
            case 'search_customers':
                $this->searchCustomers();
                break;
            case 'checkout':
                $this->checkout();
                break;
            case 'void_sale':
                $this->voidSale();
                break;
            case 'trigger_mpesa':
                $this->triggerMpesaPush();
                break;
            case 'check_mpesa_status':
                $this->checkMpesaStatus();
                break;
            default:
                echo json_encode(['success' => false, 'message' => 'Invalid POS action.']);
                break;
        }
        exit;
    }

    private function getProducts()
    {
        try {
            $query = "SELECT id, name, size, type, cost_price, retail_price, wholesale_price, stock_qty, low_stock_threshold, unit, image_url 
                      FROM fish 
                      WHERE is_active = 1 
                      ORDER BY name ASC, size ASC";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode(['success' => true, 'products' => $products]);
        } catch (Exception $e) {
            error_log("POS getProducts error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Failed to fetch products.']);
        }
    }

    private function searchCustomers()
    {
        try {
            $term = trim($_GET['query'] ?? '');
            if (empty($term)) {
                echo json_encode(['success' => true, 'customers' => []]);
                return;
            }

            $query = "SELECT id, full_name, phone, role, customer_type, credit_limit, outstanding_balance 
                      FROM users 
                      WHERE role = 'customer' 
                        AND (full_name LIKE :term OR phone LIKE :term OR email LIKE :term) 
                      LIMIT 10";
            
            $stmt = $this->db->prepare($query);
            $searchTerm = "%{$term}%";
            $stmt->execute([':term' => $searchTerm]);
            $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode(['success' => true, 'customers' => $customers]);
        } catch (Exception $e) {
            error_log("POS searchCustomers error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Failed to search customers.']);
        }
    }

    private function checkout()
    {
        $input = json_decode(file_get_contents('php://input'), true);

        if (!$input) {
            echo json_encode(['success' => false, 'message' => 'Invalid checkout payload.']);
            return;
        }

        $customerId = isset($input['customer_id']) && $input['customer_id'] !== '' ? (int)$input['customer_id'] : null;
        $customerName = trim($input['customer_name'] ?? '');
        $customerPhone = trim($input['customer_phone'] ?? '');
        $customerType = $input['customer_type'] === 'wholesale' ? 'wholesale' : 'retail';
        
        $items = $input['items'] ?? [];
        $subtotal = (float)($input['subtotal'] ?? 0);
        $discount = (float)($input['discount'] ?? 0);
        $total = (float)($input['total'] ?? 0);
        $amountTendered = isset($input['amount_tendered']) ? (float)$input['amount_tendered'] : null;
        $changeGiven = isset($input['change_given']) ? (float)$input['change_given'] : null;
        
        $paymentMethod = $input['payment_method'] ?? 'cash';
        $mpesaRef = trim($input['mpesa_ref'] ?? '');
        $notes = trim($input['notes'] ?? '');

        if (empty($items)) {
            echo json_encode(['success' => false, 'message' => 'Cart is empty.']);
            return;
        }

        if (!in_array($paymentMethod, ['cash', 'mpesa', 'credit'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid payment method.']);
            return;
        }

        // Enforce credit constraints
        if ($paymentMethod === 'credit') {
            if (!$customerId) {
                echo json_encode(['success' => false, 'message' => 'An active customer account is required for credit payments.']);
                return;
            }
            if ($customerType !== 'wholesale') {
                echo json_encode(['success' => false, 'message' => 'Only wholesale customers are allowed to purchase on credit.']);
                return;
            }
        }

        try {
            $this->db->beginTransaction();

            $customerRow = null;
            if ($customerId) {
                $q = "SELECT id, full_name, phone, customer_type, credit_limit, outstanding_balance FROM users WHERE id = :id FOR UPDATE";
                $s = $this->db->prepare($q);
                $s->execute([':id' => $customerId]);
                $customerRow = $s->fetch(PDO::FETCH_ASSOC);

                if (!$customerRow) {
                    throw new Exception("Selected customer account does not exist.");
                }

                // Ifpaying on credit, check limit
                if ($paymentMethod === 'credit') {
                    $newBalance = (float)$customerRow['outstanding_balance'] + $total;
                    $creditLimit = (float)$customerRow['credit_limit'];
                    if ($newBalance > $creditLimit) {
                        throw new Exception("Credit limit exceeded! Customer outstanding balance would be Ksh " . number_format($newBalance, 2) . ", which exceeds their limit of Ksh " . number_format($creditLimit, 2) . ".");
                    }
                }
            }

            // Verify and adjust stock for each item
            $verifiedItems = [];
            foreach ($items as $item) {
                $fishId = (int)$item['fish_id'];
                $quantity = (int)$item['quantity'];

                if ($quantity <= 0) {
                    throw new Exception("Invalid item quantity.");
                }

                // Fetch fish row with write lock
                $fq = "SELECT id, name, size, type, retail_price, wholesale_price, stock_qty, stock_quantity, cost_price FROM fish WHERE id = :id FOR UPDATE";
                $fs = $this->db->prepare($fq);
                $fs->execute([':id' => $fishId]);
                $fishRow = $fs->fetch(PDO::FETCH_ASSOC);

                if (!$fishRow) {
                    throw new Exception("Product with ID {$fishId} not found in inventory.");
                }

                $availableStock = (int)$fishRow['stock_qty'];
                if ($availableStock < $quantity) {
                    throw new Exception("Insufficient stock for product '{$fishRow['name']} ({$fishRow['size']})'. Requested: {$quantity}, Available: {$availableStock}. Stock is NOT allowed to fall below zero.");
                }

                $priceToCharge = ($customerType === 'wholesale') ? (float)$fishRow['wholesale_price'] : (float)$fishRow['retail_price'];
                $lineTotal = $priceToCharge * $quantity;

                // Adjust inventory stock
                $newStock = $availableStock - $quantity;
                $updStock = "UPDATE fish SET stock_qty = :new_stock, stock_quantity = :new_stock WHERE id = :id";
                $updStmt = $this->db->prepare($updStock);
                $updStmt->execute([':new_stock' => $newStock, ':id' => $fishId]);

                $verifiedItems[] = [
                    'fish_id' => $fishId,
                    'fish_name' => $fishRow['name'],
                    'size' => $fishRow['size'],
                    'type' => $fishRow['type'],
                    'quantity' => $quantity,
                    'unit_price' => $priceToCharge,
                    'line_total' => $lineTotal
                ];
            }

            // Generate unique POS sale reference
            $saleRef = 'POS-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));

            // Save sale record
            $insSale = "INSERT INTO pos_sales (sale_ref, cashier_id, customer_id, customer_name, customer_phone, customer_type, subtotal, discount, total, amount_tendered, change_given, payment_method, mpesa_ref, notes) 
                        VALUES (:ref, :cashier, :customer, :c_name, :c_phone, :c_type, :subtotal, :discount, :total, :tendered, :change, :method, :mpesa, :notes)";
            
            $stmtSale = $this->db->prepare($insSale);
            $stmtSale->execute([
                ':ref' => $saleRef,
                ':cashier' => $_SESSION['user_id'],
                ':customer' => $customerId,
                ':c_name' => $customerId ? $customerRow['full_name'] : $customerName,
                ':c_phone' => $customerId ? $customerRow['phone'] : $customerPhone,
                ':c_type' => $customerType,
                ':subtotal' => $subtotal,
                ':discount' => $discount,
                ':total' => $total,
                ':tendered' => $amountTendered,
                ':change' => $changeGiven,
                ':method' => $paymentMethod,
                ':mpesa' => $mpesaRef,
                ':notes' => $notes
            ]);

            $saleId = $this->db->lastInsertId();

            // Save sale items
            $insItem = "INSERT INTO pos_sale_items (sale_id, fish_id, fish_name, size, type, quantity, unit_price, line_total) 
                        VALUES (:sale_id, :fish_id, :fish_name, :size, :type, :qty, :price, :total)";
            $stmtItem = $this->db->prepare($insItem);

            foreach ($verifiedItems as $vItem) {
                $stmtItem->execute([
                    ':sale_id' => $saleId,
                    ':fish_id' => $vItem['fish_id'],
                    ':fish_name' => $vItem['fish_name'],
                    ':size' => $vItem['size'],
                    ':type' => $vItem['type'],
                    ':qty' => $vItem['quantity'],
                    ':price' => $vItem['unit_price'],
                    ':total' => $vItem['line_total']
                ]);
            }

            // Enforce credit balance updates and debt ledger logs
            if ($paymentMethod === 'credit' && $customerId) {
                $newOutstanding = (float)$customerRow['outstanding_balance'] + $total;
                $updCust = "UPDATE users SET outstanding_balance = :bal WHERE id = :id";
                $updCustStmt = $this->db->prepare($updCust);
                $updCustStmt->execute([':bal' => $newOutstanding, ':id' => $customerId]);

                // Insert into debt ledger
                $insLedger = "INSERT INTO debt_ledger (customer_id, sale_id, type, amount, balance_after, notes, recorded_by) 
                              VALUES (:cust_id, :sale_id, 'debt', :amount, :bal, :notes, :by)";
                $insLStmt = $this->db->prepare($insLedger);
                $insLStmt->execute([
                    ':cust_id' => $customerId,
                    ':sale_id' => $saleId,
                    ':amount' => $total,
                    ':bal' => $newOutstanding,
                    ':notes' => "POS Credit Sale Reference: " . $saleRef,
                    ':by' => $_SESSION['user_id']
                ]);
            }

            // Log activity to audit log
            AuditController::logActivity(
                $_SESSION['user_id'], 
                "pos_checkout_success", 
                "pos_sales", 
                $saleId, 
                null, 
                ['sale_ref' => $saleRef, 'total' => $total, 'payment_method' => $paymentMethod]
            );

            $this->db->commit();
            echo json_encode(['success' => true, 'message' => 'Checkout completed successfully.', 'sale_id' => $saleId, 'sale_ref' => $saleRef]);

        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Checkout Transaction Failed: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    private function voidSale()
    {
        // Only administrators are allowed to void sales
        if (($_SESSION['role'] ?? '') !== 'admin') {
            echo json_encode(['success' => false, 'message' => 'Access denied. Only administrators are allowed to void POS sales.']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $saleId = isset($input['sale_id']) ? (int)$input['sale_id'] : 0;
        $voidReason = trim($input['reason'] ?? '');

        if ($saleId <= 0 || empty($voidReason)) {
            echo json_encode(['success' => false, 'message' => 'Valid Sale ID and a reason for voiding are required.']);
            return;
        }

        try {
            $this->db->beginTransaction();

            // Fetch sale details
            $q = "SELECT * FROM pos_sales WHERE id = :id FOR UPDATE";
            $s = $this->db->prepare($q);
            $s->execute([':id' => $saleId]);
            $sale = $s->fetch(PDO::FETCH_ASSOC);

            if (!$sale) {
                throw new Exception("POS sale record not found.");
            }

            if ($sale['status'] === 'voided') {
                throw new Exception("This sale record has already been voided.");
            }

            // 1. Return stock quantity back to fish inventory
            $iq = "SELECT * FROM pos_sale_items WHERE sale_id = :sale_id";
            $is = $this->db->prepare($iq);
            $is->execute([':sale_id' => $saleId]);
            $items = $is->fetchAll(PDO::FETCH_ASSOC);

            foreach ($items as $item) {
                $fishId = (int)$item['fish_id'];
                $quantity = (int)$item['quantity'];

                $updStock = "UPDATE fish SET stock_qty = stock_qty + :qty, stock_quantity = stock_quantity + :qty WHERE id = :id";
                $updStmt = $this->db->prepare($updStock);
                $updStmt->execute([':qty' => $quantity, ':id' => $fishId]);
            }

            // 2. Adjust outstanding balance if paid on credit
            if ($sale['payment_method'] === 'credit' && !empty($sale['customer_id'])) {
                $custQ = "SELECT id, outstanding_balance FROM users WHERE id = :id FOR UPDATE";
                $custS = $this->db->prepare($custQ);
                $custS->execute([':id' => $sale['customer_id']]);
                $customer = $custS->fetch(PDO::FETCH_ASSOC);

                if ($customer) {
                    $newOutstanding = max(0.00, (float)$customer['outstanding_balance'] - (float)$sale['total']);
                    
                    $updCust = "UPDATE users SET outstanding_balance = :bal WHERE id = :id";
                    $updCustStmt = $this->db->prepare($updCust);
                    $updCustStmt->execute([':bal' => $newOutstanding, ':id' => $sale['customer_id']]);

                    // Insert into debt ledger as a balancing payment entry
                    $insLedger = "INSERT INTO debt_ledger (customer_id, sale_id, type, amount, balance_after, notes, recorded_by) 
                                  VALUES (:cust_id, :sale_id, 'payment', :amount, :bal, :notes, :by)";
                    $insLStmt = $this->db->prepare($insLedger);
                    $insLStmt->execute([
                        ':cust_id' => $sale['customer_id'],
                        ':sale_id' => $saleId,
                        ':amount' => (float)$sale['total'],
                        ':bal' => $newOutstanding,
                        ':notes' => "POS VOID balancing: " . $sale['sale_ref'] . " Reason: " . $voidReason,
                        ':by' => $_SESSION['user_id']
                    ]);
                }
            }

            // 3. Mark sale as voided
            $updSale = "UPDATE pos_sales SET status = 'voided', notes = CONCAT(notes, '\n[VOIDED] Reason: ', :reason) WHERE id = :id";
            $updSaleStmt = $this->db->prepare($updSale);
            $updSaleStmt->execute([':reason' => $voidReason, ':id' => $saleId]);

            // Log activity to audit log
            AuditController::logActivity(
                $_SESSION['user_id'], 
                "pos_sale_voided", 
                "pos_sales", 
                $saleId, 
                $sale, 
                ['status' => 'voided', 'reason' => $voidReason]
            );

            $this->db->commit();
            echo json_encode(['success' => true, 'message' => 'Sale voided successfully and inventory stock replenished.']);

        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Void POS Sale Failed: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    private function triggerMpesaPush()
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $phone = trim($input['phone'] ?? '');
        $amount = (float)($input['amount'] ?? 0);
        $description = trim($input['description'] ?? 'Tilapia POS Payment');

        if (empty($phone) || $amount <= 0) {
            echo json_encode(['success' => false, 'message' => 'Phone number and positive amount are required.']);
            return;
        }

        try {
            require_once __DIR__ . '/../controllers/MpesaController.php';
            $mpesa = new MpesaController();
            
            // Create a dummy order ID reference for the POS transition
            $dummyOrderId = mt_rand(100000, 999999);
            
            $response = $mpesa->initiateSTKPush($phone, $amount, $dummyOrderId, $description);
            
            if (isset($response['ResponseCode']) && $response['ResponseCode'] == '0') {
                echo json_encode([
                    'success' => true, 
                    'message' => 'STK Push sent successfully. Please complete payment on your phone.',
                    'checkout_request_id' => $response['CheckoutRequestID'] ?? ''
                ]);
            } else {
                $err = $response['error'] ?? $response['ResponseDescription'] ?? 'Failed to initiate STK push.';
                echo json_encode(['success' => false, 'message' => $err]);
            }
        } catch (Exception $e) {
            error_log("POS Mpesa Error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    private function checkMpesaStatus()
    {
        $checkoutId = trim($_GET['checkout_request_id'] ?? '');
        if (empty($checkoutId)) {
            echo json_encode(['success' => false, 'message' => 'Checkout Request ID is required.']);
            return;
        }

        try {
            // Check in local database for the callback response from payment_transactions / orders
            $query = "SELECT * FROM payment_requests WHERE checkout_request_id = :id ORDER BY created_at DESC LIMIT 1";
            $stmt = $this->db->prepare($query);
            $stmt->execute([':id' => $checkoutId]);
            $request = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$request) {
                echo json_encode(['success' => false, 'message' => 'No payment record found for this request.']);
                return;
            }

            $orderId = $request['order_id'];
            
            // Check transactions table for actual callback details
            $q = "SELECT * FROM payment_transactions WHERE order_id = :order_id ORDER BY created_at DESC LIMIT 1";
            $s = $this->db->prepare($q);
            $s->execute([':order_id' => $orderId]);
            $tx = $s->fetch(PDO::FETCH_ASSOC);

            if ($tx) {
                if ($tx['status'] === 'success') {
                    echo json_encode([
                        'success' => true, 
                        'status' => 'paid', 
                        'mpesa_receipt' => $tx['mpesa_receipt'],
                        'message' => 'Payment completed successfully!'
                    ]);
                } else {
                    echo json_encode([
                        'success' => true, 
                        'status' => 'failed', 
                        'message' => 'Payment failed: ' . ($tx['failure_reason'] ?? 'User cancelled or timeout')
                    ]);
                }
            } else {
                // Still waiting
                echo json_encode(['success' => true, 'status' => 'pending', 'message' => 'Waiting for user to enter PIN.']);
            }

        } catch (Exception $e) {
            error_log("POS Check Mpesa Status error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error checking payment status.']);
        }
    }
}

// Handle request routing if called directly
$controller = new PosController();
$controller->handleRequest();
