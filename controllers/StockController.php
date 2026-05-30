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

class StockController
{
    private $db;

    public function __construct()
    {
        try {
            $database = new Database();
            $this->db = $database->getConnection();
        } catch (Exception $e) {
            error_log("StockController DB Init Error: " . $e->getMessage());
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
            case 'get_deliveries':
                $this->getDeliveries();
                break;
            case 'record_delivery':
                $this->recordDelivery();
                break;
            case 'get_wastage':
                $this->getWastage();
                break;
            case 'record_wastage':
                $this->recordWastage();
                break;
            case 'get_fish_products':
                $this->getFishProducts();
                break;
            default:
                echo json_encode(['success' => false, 'message' => 'Invalid Stock action.']);
                break;
        }
        exit;
    }

    private function getDeliveries()
    {
        try {
            $query = "SELECT d.*, u.full_name as received_by_name 
                      FROM stock_deliveries d
                      JOIN users u ON d.received_by = u.id
                      ORDER BY d.created_at DESC";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $deliveries = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Fetch items for each delivery
            foreach ($deliveries as &$delivery) {
                $qItems = "SELECT di.*, f.name as fish_name, f.size, f.type, f.unit
                           FROM stock_delivery_items di
                           JOIN fish f ON di.fish_id = f.id
                           WHERE di.delivery_id = :id";
                $sItems = $this->db->prepare($qItems);
                $sItems->execute([':id' => $delivery['id']]);
                $delivery['items'] = $sItems->fetchAll(PDO::FETCH_ASSOC);
            }

            echo json_encode(['success' => true, 'deliveries' => $deliveries]);
        } catch (Exception $e) {
            error_log("StockController getDeliveries error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Failed to fetch stock deliveries.']);
        }
    }

    private function recordDelivery()
    {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            echo json_encode(['success' => false, 'message' => 'Invalid request payload.']);
            return;
        }

        $supplierName = trim(sanitize_string($input['supplier_name'] ?? ''));
        $supplierPhone = trim(sanitize_string($input['supplier_phone'] ?? ''));
        $deliveryDate = sanitize_string($input['delivery_date'] ?? date('Y-m-d'));
        $notes = trim(sanitize_string($input['notes'] ?? ''));
        $items = $input['items'] ?? [];

        if (empty($supplierName)) {
            echo json_encode(['success' => false, 'message' => 'Supplier name is required.']);
            return;
        }

        if (empty($items) || !is_array($items)) {
            echo json_encode(['success' => false, 'message' => 'Delivery must contain at least one fish item.']);
            return;
        }

        try {
            $this->db->beginTransaction();

            $totalCost = 0.0;
            $itemsToSave = [];

            // 1. Validate all items before making changes
            foreach ($items as $item) {
                $fishId = (int)($item['fish_id'] ?? 0);
                $qty = (int)($item['quantity_received'] ?? 0);
                $cost = (float)($item['cost_per_unit'] ?? 0.0);

                if ($fishId <= 0 || $qty <= 0 || $cost < 0) {
                    throw new Exception("Invalid fish product, quantity or cost details provided in the delivery list.");
                }

                // Check if fish exists
                $qFish = "SELECT name, stock_qty FROM fish WHERE id = :id FOR UPDATE";
                $sFish = $this->db->prepare($qFish);
                $sFish->execute([':id' => $fishId]);
                $fish = $sFish->fetch(PDO::FETCH_ASSOC);

                if (!$fish) {
                    throw new Exception("Fish product ID {$fishId} not found in inventory.");
                }

                $lineCost = $qty * $cost;
                $totalCost += $lineCost;

                $itemsToSave[] = [
                    'fish_id' => $fishId,
                    'quantity_received' => $qty,
                    'cost_per_unit' => $cost,
                    'line_cost' => $lineCost,
                    'fish_name' => $fish['name']
                ];
            }

            // 2. Insert master delivery record
            $deliveryRef = 'DEL-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));
            $insQ = "INSERT INTO stock_deliveries (delivery_ref, supplier_name, supplier_phone, received_by, total_cost, notes, delivery_date)
                     VALUES (:ref, :supplier, :phone, :user_id, :total_cost, :notes, :d_date)";
            
            $insStmt = $this->db->prepare($insQ);
            $insStmt->execute([
                ':ref' => $deliveryRef,
                ':supplier' => $supplierName,
                ':phone' => $supplierPhone,
                ':user_id' => $_SESSION['user_id'],
                ':total_cost' => $totalCost,
                ':notes' => $notes,
                ':d_date' => $deliveryDate
            ]);

            $deliveryId = $this->db->lastInsertId();

            // 3. Save items and update inventory stock double columns
            foreach ($itemsToSave as $item) {
                // Insert delivery item
                $insItemQ = "INSERT INTO stock_delivery_items (delivery_id, fish_id, quantity_received, cost_per_unit, line_cost)
                             VALUES (:d_id, :f_id, :qty, :cost, :l_cost)";
                $insItemStmt = $this->db->prepare($insItemQ);
                $insItemStmt->execute([
                    ':d_id' => $deliveryId,
                    ':f_id' => $item['fish_id'],
                    ':qty' => $item['quantity_received'],
                    ':cost' => $item['cost_per_unit'],
                    ':l_cost' => $item['line_cost']
                ]);

                // Update stock in fish table - update both stock_qty and stock_quantity
                $updStockQ = "UPDATE fish 
                              SET stock_qty = stock_qty + :qty, 
                                  stock_quantity = stock_quantity + :qty,
                                  cost_price = :cost, -- Auto-update cost price to latest delivery cost
                                  updated_at = CURRENT_TIMESTAMP 
                              WHERE id = :id";
                $updStockStmt = $this->db->prepare($updStockQ);
                $updStockStmt->execute([
                    ':qty' => $item['quantity_received'],
                    ':cost' => $item['cost_per_unit'],
                    ':id' => $item['fish_id']
                ]);
            }

            // Log activity in audit log
            AuditController::logActivity(
                $_SESSION['user_id'],
                "stock_delivery_recorded",
                "stock_deliveries",
                $deliveryId,
                null,
                ['delivery_ref' => $deliveryRef, 'supplier' => $supplierName, 'total_cost' => $totalCost]
            );

            $this->db->commit();
            echo json_encode([
                'success' => true,
                'message' => "Delivery recorded successfully and inventory stock replenished.",
                'delivery_id' => $deliveryId,
                'delivery_ref' => $deliveryRef
            ]);

        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Record Stock Delivery Failed: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    private function getWastage()
    {
        try {
            $query = "SELECT w.*, f.name as fish_name, f.size, f.type, f.unit, u.full_name as recorded_by_name 
                      FROM wastage_log w
                      JOIN fish f ON w.fish_id = f.id
                      JOIN users u ON w.recorded_by = u.id
                      ORDER BY w.created_at DESC";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $wastage = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode(['success' => true, 'wastage' => $wastage]);
        } catch (Exception $e) {
            error_log("StockController getWastage error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Failed to fetch wastage logs.']);
        }
    }

    private function recordWastage()
    {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            echo json_encode(['success' => false, 'message' => 'Invalid request payload.']);
            return;
        }

        $fishId = (int)($input['fish_id'] ?? 0);
        $quantity = (int)($input['quantity'] ?? 0);
        $reason = trim(sanitize_string($input['reason'] ?? ''));

        if ($fishId <= 0 || $quantity <= 0) {
            echo json_encode(['success' => false, 'message' => 'Valid fish product and positive quantity are required.']);
            return;
        }

        if (empty($reason)) {
            echo json_encode(['success' => false, 'message' => 'Reason for wastage is required.']);
            return;
        }

        try {
            $this->db->beginTransaction();

            // 1. Fetch fish details with lock
            $qFish = "SELECT id, name, stock_qty, cost_price, retail_price FROM fish WHERE id = :id FOR UPDATE";
            $sFish = $this->db->prepare($qFish);
            $sFish->execute([':id' => $fishId]);
            $fish = $sFish->fetch(PDO::FETCH_ASSOC);

            if (!$fish) {
                throw new Exception("Fish product not found.");
            }

            $currentStock = (int)$fish['stock_qty'];
            if ($currentStock < $quantity) {
                throw new Exception("Cannot waste more stock than is available! Available stock is: {$currentStock}. Stock is not allowed to drop below zero.");
            }

            // 2. Calculate financial loss based on cost_price (or retail_price if cost_price is 0)
            $costPrice = (float)$fish['cost_price'];
            if ($costPrice <= 0.0) {
                $costPrice = (float)$fish['retail_price'] * 0.70; // Fallback estimate
            }
            $estimatedLoss = $quantity * $costPrice;

            // 3. Deduct stock from inventory
            $newStock = $currentStock - $quantity;
            $updStockQ = "UPDATE fish SET stock_qty = :new_stock, stock_quantity = :new_stock, updated_at = CURRENT_TIMESTAMP WHERE id = :id";
            $updStockStmt = $this->db->prepare($updStockQ);
            $updStockStmt->execute([':new_stock' => $newStock, ':id' => $fishId]);

            // 4. Log in wastage_log
            $insQ = "INSERT INTO wastage_log (fish_id, quantity, reason, estimated_loss, recorded_by)
                     VALUES (:fish_id, :qty, :reason, :loss, :user_id)";
            $insStmt = $this->db->prepare($insQ);
            $insStmt->execute([
                ':fish_id' => $fishId,
                ':qty' => $quantity,
                ':reason' => $reason,
                ':loss' => $estimatedLoss,
                ':user_id' => $_SESSION['user_id']
            ]);

            $wastageId = $this->db->lastInsertId();

            // Log activity in audit log
            AuditController::logActivity(
                $_SESSION['user_id'],
                "stock_wastage_recorded",
                "wastage_log",
                $wastageId,
                null,
                ['fish_name' => $fish['name'], 'quantity' => $quantity, 'estimated_loss' => $estimatedLoss, 'reason' => $reason]
            );

            $this->db->commit();
            echo json_encode([
                'success' => true,
                'message' => "Wastage recorded successfully. Stock adjusted.",
                'wastage_id' => $wastageId,
                'estimated_loss' => $estimatedLoss
            ]);

        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Record Stock Wastage Failed: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    private function getFishProducts()
    {
        try {
            $query = "SELECT id, name, size, type, stock_qty, cost_price, retail_price, wholesale_price, unit 
                      FROM fish 
                      WHERE is_active = 1 
                      ORDER BY type, name, size";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode(['success' => true, 'products' => $products]);
        } catch (Exception $e) {
            error_log("StockController getFishProducts error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Failed to fetch fish products.']);
        }
    }
}

// Handle direct call routing
if (basename($_SERVER['PHP_SELF']) === 'StockController.php') {
    $controller = new StockController();
    $controller->handleRequest();
}
