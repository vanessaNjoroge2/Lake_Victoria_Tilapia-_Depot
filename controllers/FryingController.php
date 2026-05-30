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

class FryingController
{
    private $db;

    public function __construct()
    {
        try {
            $database = new Database();
            $this->db = $database->getConnection();
        } catch (Exception $e) {
            error_log("FryingController DB Init Error: " . $e->getMessage());
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
            case 'get_batches':
                $this->getBatches();
                break;
            case 'start_batch':
                $this->startBatch();
                break;
            case 'complete_batch':
                $this->completeBatch();
                break;
            case 'cancel_batch':
                $this->cancelBatch();
                break;
            default:
                echo json_encode(['success' => false, 'message' => 'Invalid Frying action.']);
                break;
        }
        exit;
    }

    private function getBatches()
    {
        try {
            $query = "SELECT b.*, 
                             r.name as raw_name, r.size as raw_size,
                             f.name as fried_name, f.size as fried_size,
                             u.full_name as started_by_name
                      FROM frying_batches b
                      JOIN fish r ON b.raw_fish_id = r.id
                      JOIN fish f ON b.fried_fish_id = f.id
                      JOIN users u ON b.started_by = u.id
                      ORDER BY b.started_at DESC";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $batches = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode(['success' => true, 'batches' => $batches]);
        } catch (Exception $e) {
            error_log("FryingController getBatches error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Failed to fetch batches.']);
        }
    }

    private function startBatch()
    {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            echo json_encode(['success' => false, 'message' => 'Invalid request payload.']);
            return;
        }

        $rawFishId = (int)($input['raw_fish_id'] ?? 0);
        $rawQty = (int)($input['raw_quantity'] ?? 0);
        $expectedQty = (int)($input['expected_quantity'] ?? $rawQty);
        $oilCost = (float)($input['oil_cost'] ?? 0.0);
        $fuelCost = (float)($input['fuel_cost'] ?? 0.0);
        $laborCost = (float)($input['labor_cost'] ?? 0.0);
        $totalCost = $oilCost + $fuelCost + $laborCost;
        $notes = trim($input['notes'] ?? '');

        if ($rawFishId <= 0 || $rawQty <= 0) {
            echo json_encode(['success' => false, 'message' => 'Valid Raw Fish product and positive quantity are required.']);
            return;
        }

        try {
            $this->db->beginTransaction();

            // 1. Fetch raw fish with lock
            $qRaw = "SELECT id, name, size, type, stock_qty FROM fish WHERE id = :id FOR UPDATE";
            $sRaw = $this->db->prepare($qRaw);
            $sRaw->execute([':id' => $rawFishId]);
            $rawFish = $sRaw->fetch(PDO::FETCH_ASSOC);

            if (!$rawFish) {
                throw new Exception("Raw fish product not found.");
            }

            if ($rawFish['type'] !== 'raw') {
                throw new Exception("Product selected is not classified as Raw fish.");
            }

            $currentStock = (int)$rawFish['stock_qty'];
            if ($currentStock < $rawQty) {
                throw new Exception("Insufficient raw stock for frying! Available: {$currentStock}, Requested: {$rawQty}. Stock is not allowed to drop below zero.");
            }

            // 2. Identify the matching Fried fish product of the same size
            $qFried = "SELECT id, name FROM fish WHERE LOWER(TRIM(size)) = LOWER(TRIM(:size)) AND type = 'fried' LIMIT 1";
            $sFried = $this->db->prepare($qFried);
            $sFried->execute([':size' => $rawFish['size']]);
            $friedFish = $sFried->fetch(PDO::FETCH_ASSOC);

            if (!$friedFish) {
                // Determine names and prices to automatically register the fried product
                $friedName = $rawFish['name'];
                $friedName = preg_replace('/\b(raw)\b/i', '', $friedName);
                $friedName = str_replace(['()', '( )'], '', $friedName);
                if (stripos($friedName, 'fresh') !== false) {
                    $friedName = preg_replace('/\bfresh\b/i', 'Fried', $friedName);
                } else {
                    $friedName = 'Fried ' . $friedName;
                }
                $friedName = trim(preg_replace('/\s+/', ' ', $friedName));

                // Insert new fried product
                $insFriedQ = "INSERT INTO fish (name, size, type, cost_price, retail_price, wholesale_price, price, description, category, stock_quantity, stock_qty, low_stock_threshold, unit, weight_range, is_active) 
                              VALUES (:name, :size, 'fried', :cost, :retail, :wholesale, :price, :desc, :category, 0, 0, 10, :unit, :weight, 1)";
                $insFriedStmt = $this->db->prepare($insFriedQ);
                
                $desc = "Fried counterpart of " . $rawFish['name'];
                $retail = (float)($rawFish['retail_price'] ?? $rawFish['price'] ?? 0.0);
                $cost = (float)($rawFish['cost_price'] ?? 0.0);
                $wholesale = (float)($rawFish['wholesale_price'] ?? $retail * 0.90);
                
                $insFriedStmt->execute([
                    ':name' => $friedName,
                    ':size' => $rawFish['size'],
                    ':cost' => $cost,
                    ':retail' => $retail,
                    ':wholesale' => $wholesale,
                    ':price' => $retail,
                    ':desc' => $desc,
                    ':category' => $rawFish['category'] ?? 'Tilapia',
                    ':unit' => $rawFish['unit'] ?? 'piece',
                    ':weight' => $rawFish['weight_range'] ?? ''
                ]);
                
                $friedFishId = (int)$this->db->lastInsertId();
            } else {
                $friedFishId = (int)$friedFish['id'];
            }

            // 3. Deduct stock from raw fish
            $newRawStock = $currentStock - $rawQty;
            $updRaw = "UPDATE fish SET stock_qty = :new_stock, stock_quantity = :new_stock WHERE id = :id";
            $updRawStmt = $this->db->prepare($updRaw);
            $updRawStmt->execute([':new_stock' => $newRawStock, ':id' => $rawFishId]);

            // 4. Generate batch reference
            $batchRef = 'FRY-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));

            // 5. Insert batch row in frying_batches with estimated overheads
            $insQ = "INSERT INTO frying_batches (batch_ref, started_by, raw_fish_id, fried_fish_id, raw_quantity, fried_quantity_expected, oil_cost, fuel_cost, labor_cost, total_frying_cost, status, notes) 
                     VALUES (:ref, :user, :raw_id, :fried_id, :raw_qty, :exp_qty, :oil, :fuel, :labor, :tot_cost, 'in_progress', :notes)";
            $insStmt = $this->db->prepare($insQ);
            $insStmt->execute([
                ':ref' => $batchRef,
                ':user' => $_SESSION['user_id'],
                ':raw_id' => $rawFishId,
                ':fried_id' => $friedFishId,
                ':raw_qty' => $rawQty,
                ':exp_qty' => $expectedQty,
                ':oil' => $oilCost,
                ':fuel' => $fuelCost,
                ':labor' => $laborCost,
                ':tot_cost' => $totalCost,
                ':notes' => $notes
            ]);

            $batchId = $this->db->lastInsertId();

            // Log activity
            AuditController::logActivity(
                $_SESSION['user_id'],
                "frying_batch_started",
                "frying_batches",
                $batchId,
                null,
                ['batch_ref' => $batchRef, 'raw_quantity' => $rawQty]
            );

            $this->db->commit();
            echo json_encode([
                'success' => true,
                'message' => 'Frying batch started successfully and raw fish inventory adjusted.',
                'batch_id' => $batchId,
                'batch_ref' => $batchRef
            ]);

        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Start Frying Batch Failed: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    private function completeBatch()
    {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            echo json_encode(['success' => false, 'message' => 'Invalid request payload.']);
            return;
        }

        $batchId = (int)($input['batch_id'] ?? 0);
        $actualQty = (int)($input['actual_quantity'] ?? 0);
        $oilCost = (float)($input['oil_cost'] ?? 0.0);
        $fuelCost = (float)($input['fuel_cost'] ?? 0.0);
        $laborCost = (float)($input['labor_cost'] ?? 0.0);
        $notes = trim($input['notes'] ?? '');

        if ($batchId <= 0 || $actualQty < 0) {
            echo json_encode(['success' => false, 'message' => 'Valid Batch ID and positive actual fried quantity are required.']);
            return;
        }

        try {
            $this->db->beginTransaction();

            // 1. Fetch batch row with lock
            $q = "SELECT * FROM frying_batches WHERE id = :id FOR UPDATE";
            $stmt = $this->db->prepare($q);
            $stmt->execute([':id' => $batchId]);
            $batch = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$batch) {
                throw new Exception("Frying batch not found.");
            }

            if ($batch['status'] !== 'in_progress') {
                throw new Exception("Only 'in_progress' frying batches can be completed.");
            }

            $friedFishId = (int)$batch['fried_fish_id'];
            $totalCost = $oilCost + $fuelCost + $laborCost;

            // 2. Increase stock of fried fish
            $updFried = "UPDATE fish SET stock_qty = stock_qty + :qty, stock_quantity = stock_quantity + :qty WHERE id = :id";
            $updFriedStmt = $this->db->prepare($updFried);
            $updFriedStmt->execute([':qty' => $actualQty, ':id' => $friedFishId]);

            // 3. Update frying batch record
            $updBatch = "UPDATE frying_batches 
                         SET fried_quantity_actual = :act_qty, 
                             oil_cost = :oil, 
                             fuel_cost = :fuel, 
                             labor_cost = :labor, 
                             total_frying_cost = :tot_cost, 
                             status = 'completed', 
                             completed_at = CURRENT_TIMESTAMP, 
                             notes = CONCAT(notes, '\n', :notes) 
                         WHERE id = :id";
            
            $updBatchStmt = $this->db->prepare($updBatch);
            $updBatchStmt->execute([
                ':act_qty' => $actualQty,
                ':oil' => $oilCost,
                ':fuel' => $fuelCost,
                ':labor' => $laborCost,
                ':tot_cost' => $totalCost,
                ':notes' => $notes,
                ':id' => $batchId
            ]);

            // Log activity
            AuditController::logActivity(
                $_SESSION['user_id'],
                "frying_batch_completed",
                "frying_batches",
                $batchId,
                $batch,
                ['status' => 'completed', 'actual_quantity' => $actualQty, 'total_cost' => $totalCost]
            );

            $this->db->commit();
            echo json_encode(['success' => true, 'message' => 'Frying batch completed successfully. Fried stock inflated!']);

        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Complete Frying Batch Failed: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    private function cancelBatch()
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $batchId = (int)($input['batch_id'] ?? 0);
        $reason = trim($input['reason'] ?? '');

        if ($batchId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Valid Batch ID is required.']);
            return;
        }

        try {
            $this->db->beginTransaction();

            // 1. Fetch batch row
            $q = "SELECT * FROM frying_batches WHERE id = :id FOR UPDATE";
            $stmt = $this->db->prepare($q);
            $stmt->execute([':id' => $batchId]);
            $batch = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$batch) {
                throw new Exception("Frying batch not found.");
            }

            if ($batch['status'] !== 'in_progress') {
                throw new Exception("Only 'in_progress' frying batches can be cancelled.");
            }

            // 2. Return raw stock back to raw fish product
            $rawFishId = (int)$batch['raw_fish_id'];
            $rawQty = (int)$batch['raw_quantity'];

            $updRaw = "UPDATE fish SET stock_qty = stock_qty + :qty, stock_quantity = stock_quantity + :qty WHERE id = :id";
            $updRawStmt = $this->db->prepare($updRaw);
            $updRawStmt->execute([':qty' => $rawQty, ':id' => $rawFishId]);

            // 3. Mark batch as cancelled
            $updBatch = "UPDATE frying_batches 
                         SET status = 'cancelled', 
                             notes = CONCAT(notes, '\n[CANCELLED] Reason: ', :reason) 
                         WHERE id = :id";
            $updBatchStmt = $this->db->prepare($updBatch);
            $updBatchStmt->execute([':reason' => $reason, ':id' => $batchId]);

            // Log activity
            AuditController::logActivity(
                $_SESSION['user_id'],
                "frying_batch_cancelled",
                "frying_batches",
                $batchId,
                $batch,
                ['status' => 'cancelled', 'reason' => $reason]
            );

            $this->db->commit();
            echo json_encode(['success' => true, 'message' => 'Frying batch cancelled successfully and raw fish inventory replenished.']);

        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Cancel Frying Batch Failed: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}

// Handle direct call routing
if (basename($_SERVER['PHP_SELF']) === 'FryingController.php') {
    $controller = new FryingController();
    $controller->handleRequest();
}
