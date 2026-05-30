<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/AuditController.php';

class ReportController
{
    private $db;

    public function __construct()
    {
        try {
            $database = new Database();
            $this->db = $database->getConnection();
            $this->ensureTableExists();
        } catch (Exception $e) {
            error_log("ReportController initialisation error: " . $e->getMessage());
        }
    }

    /**
     * Self-healing function to ensure EOD reconciliation table exists
     */
    private function ensureTableExists()
    {
        if (!$this->db) return;
        try {
            $query = "CREATE TABLE IF NOT EXISTS register_closings (
                id INT AUTO_INCREMENT PRIMARY KEY,
                closing_date DATE UNIQUE NOT NULL,
                cashier_id INT NOT NULL,
                expected_cash DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                actual_cash DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                variance_cash DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                expected_mpesa DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                actual_mpesa DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                variance_mpesa DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                expected_credit DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                actual_credit DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                variance_credit DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                notes TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (cashier_id) REFERENCES users(id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
            $this->db->exec($query);
        } catch (PDOException $e) {
            error_log("Failed to create register_closings table: " . $e->getMessage());
        }
    }

    /**
     * Get core dashboard KPIs for POS reports
     *
     * @param string $startDate 'YYYY-MM-DD'
     * @param string $endDate 'YYYY-MM-DD'
     * @return array
     */
    public function getDashboardKPIs($startDate, $endDate)
    {
        if (!$this->db) return [];

        try {
            // 1. Sales summary (excluding voided)
            $salesQuery = "SELECT 
                            COALESCE(SUM(total), 0) as total_sales,
                            COALESCE(SUM(subtotal), 0) as total_subtotal,
                            COALESCE(SUM(discount), 0) as total_discounts,
                            COUNT(*) as total_orders,
                            COALESCE(SUM(CASE WHEN payment_method = 'cash' THEN total ELSE 0 END), 0) as sales_cash,
                            COALESCE(SUM(CASE WHEN payment_method = 'mpesa' THEN total ELSE 0 END), 0) as sales_mpesa,
                            COALESCE(SUM(CASE WHEN payment_method = 'credit' THEN total ELSE 0 END), 0) as sales_credit
                           FROM pos_sales 
                           WHERE status = 'completed' 
                           AND DATE(created_at) BETWEEN :start_date AND :end_date";
            $stmt = $this->db->prepare($salesQuery);
            $stmt->bindParam(':start_date', $startDate);
            $stmt->bindParam(':end_date', $endDate);
            $stmt->execute();
            $sales = $stmt->fetch(PDO::FETCH_ASSOC);

            // 2. Frying batch summary
            $fryingQuery = "SELECT 
                                COUNT(*) as total_frying_batches,
                                COALESCE(SUM(oil_cost), 0) as total_oil_cost,
                                COALESCE(SUM(fuel_cost), 0) as total_fuel_cost,
                                COALESCE(SUM(labor_cost), 0) as total_labor_cost,
                                COALESCE(SUM(total_frying_cost), 0) as total_frying_expenses,
                                COALESCE(SUM(raw_quantity), 0) as total_raw_pieces,
                                COALESCE(SUM(fried_quantity_actual), 0) as total_fried_pieces
                            FROM frying_batches 
                            WHERE status = 'completed'
                            AND DATE(completed_at) BETWEEN :start_date AND :end_date";
            $stmt = $this->db->prepare($fryingQuery);
            $stmt->bindParam(':start_date', $startDate);
            $stmt->bindParam(':end_date', $endDate);
            $stmt->execute();
            $frying = $stmt->fetch(PDO::FETCH_ASSOC);

            // 3. Wastage summary
            $wastageQuery = "SELECT 
                                COUNT(*) as total_wastage_logs,
                                COALESCE(SUM(quantity), 0) as total_wastage_quantity,
                                COALESCE(SUM(estimated_loss), 0) as total_wastage_loss
                             FROM wastage_log 
                             WHERE DATE(created_at) BETWEEN :start_date AND :end_date";
            $stmt = $this->db->prepare($wastageQuery);
            $stmt->bindParam(':start_date', $startDate);
            $stmt->bindParam(':end_date', $endDate);
            $stmt->execute();
            $wastage = $stmt->fetch(PDO::FETCH_ASSOC);

            // 4. Calculate Gross Profit Margin
            // Estimated cost of products sold
            $costQuery = "SELECT 
                            COALESCE(SUM(i.quantity * f.cost_price), 0) as total_cost_of_goods
                          FROM pos_sale_items i
                          JOIN pos_sales s ON i.sale_id = s.id
                          JOIN fish f ON i.fish_id = f.id
                          WHERE s.status = 'completed' 
                          AND DATE(s.created_at) BETWEEN :start_date AND :end_date";
            $stmt = $this->db->prepare($costQuery);
            $stmt->bindParam(':start_date', $startDate);
            $stmt->bindParam(':end_date', $endDate);
            $stmt->execute();
            $costData = $stmt->fetch(PDO::FETCH_ASSOC);
            $cogs = floatval($costData['total_cost_of_goods']);

            // Frying overhead is counted as operational cost affecting fish gross profit margin
            $fryingExpenses = floatval($frying['total_frying_expenses']);
            $totalCogsWithOverhead = $cogs + $fryingExpenses;

            // Total sales revenue
            $revenue = floatval($sales['total_sales']);
            
            // Total wastage loss reduces gross profit
            $wastageLoss = floatval($wastage['total_wastage_loss']);

            // Gross profit = Revenue - COGS - Frying Expenses - Wastage Loss
            $grossProfit = $revenue - $totalCogsWithOverhead - $wastageLoss;
            $grossMarginPercent = ($revenue > 0) ? ($grossProfit / $revenue) * 100 : 0.00;

            return [
                'sales' => $sales,
                'frying' => $frying,
                'wastage' => $wastage,
                'financials' => [
                    'cogs' => $cogs,
                    'frying_expenses' => $fryingExpenses,
                    'wastage_loss' => $wastageLoss,
                    'revenue' => $revenue,
                    'gross_profit' => $grossProfit,
                    'gross_margin_percent' => $grossMarginPercent
                ]
            ];
        } catch (PDOException $e) {
            error_log("getDashboardKPIs error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Fetch sales breakdowns by size & type class (Raw vs Fried)
     *
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function getSalesBySizeAndType($startDate, $endDate)
    {
        if (!$this->db) return [];
        try {
            $query = "SELECT 
                        i.size,
                        i.type,
                        SUM(i.quantity) as total_qty,
                        SUM(i.line_total) as total_revenue
                      FROM pos_sale_items i
                      JOIN pos_sales s ON i.sale_id = s.id
                      WHERE s.status = 'completed'
                      AND DATE(s.created_at) BETWEEN :start_date AND :end_date
                      GROUP BY i.size, i.type
                      ORDER BY i.type, i.size";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':start_date', $startDate);
            $stmt->bindParam(':end_date', $endDate);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("getSalesBySizeAndType error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get sales and void performance per cashier
     *
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function getCashierPerformance($startDate, $endDate)
    {
        if (!$this->db) return [];
        try {
            $query = "SELECT 
                        u.full_name as cashier_name,
                        u.username as cashier_username,
                        COUNT(CASE WHEN s.status = 'completed' THEN 1 END) as completed_sales_count,
                        COALESCE(SUM(CASE WHEN s.status = 'completed' THEN s.total ELSE 0 END), 0) as total_revenue,
                        COUNT(CASE WHEN s.status = 'voided' THEN 1 END) as voided_sales_count,
                        COALESCE(SUM(CASE WHEN s.status = 'voided' THEN s.total ELSE 0 END), 0) as voided_revenue
                      FROM pos_sales s
                      JOIN users u ON s.cashier_id = u.id
                      WHERE DATE(s.created_at) BETWEEN :start_date AND :end_date
                      GROUP BY s.cashier_id
                      ORDER BY total_revenue DESC";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':start_date', $startDate);
            $stmt->bindParam(':end_date', $endDate);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("getCashierPerformance error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Fetch expected register values for a given date (default today)
     *
     * @param string $date 'YYYY-MM-DD'
     * @return array
     */
    public function getExpectedEOD($date = null)
    {
        if (!$this->db) return [];
        if (!$date) $date = date('Y-m-d');

        try {
            $query = "SELECT 
                        COALESCE(SUM(CASE WHEN payment_method = 'cash' THEN total ELSE 0 END), 0) as expected_cash,
                        COALESCE(SUM(CASE WHEN payment_method = 'mpesa' THEN total ELSE 0 END), 0) as expected_mpesa,
                        COALESCE(SUM(CASE WHEN payment_method = 'credit' THEN total ELSE 0 END), 0) as expected_credit
                      FROM pos_sales
                      WHERE DATE(created_at) = :date AND status = 'completed'";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':date', $date);
            $stmt->execute();
            $totals = $stmt->fetch(PDO::FETCH_ASSOC);

            // Fetch if this closing date has already been reconciled
            $recQuery = "SELECT rc.*, u.full_name as cashier_name 
                         FROM register_closings rc
                         JOIN users u ON rc.cashier_id = u.id
                         WHERE rc.closing_date = :date";
            $stmt2 = $this->db->prepare($recQuery);
            $stmt2->bindParam(':date', $date);
            $stmt2->execute();
            $reconciliation = $stmt2->fetch(PDO::FETCH_ASSOC);

            return [
                'date' => $date,
                'expected_cash' => floatval($totals['expected_cash']),
                'expected_mpesa' => floatval($totals['expected_mpesa']),
                'expected_credit' => floatval($totals['expected_credit']),
                'reconciled' => !empty($reconciliation),
                'reconciliation_details' => $reconciliation ?: null
            ];
        } catch (PDOException $e) {
            error_log("getExpectedEOD error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Save EOD reconciliation daily closing values
     *
     * @param array $data
     * @return bool
     */
    public function saveEODReconciliation($data)
    {
        if (!$this->db) return false;

        try {
            $this->db->beginTransaction();

            // Calculate variances
            $variance_cash = $data['actual_cash'] - $data['expected_cash'];
            $variance_mpesa = $data['actual_mpesa'] - $data['expected_mpesa'];
            $variance_credit = $data['actual_credit'] - $data['expected_credit'];

            $query = "INSERT INTO register_closings 
                      (closing_date, cashier_id, expected_cash, actual_cash, variance_cash, 
                       expected_mpesa, actual_mpesa, variance_mpesa, expected_credit, actual_credit, 
                       variance_credit, notes) 
                      VALUES (:closing_date, :cashier_id, :expected_cash, :actual_cash, :variance_cash, 
                              :expected_mpesa, :actual_mpesa, :variance_mpesa, :expected_credit, :actual_credit, 
                              :variance_credit, :notes)
                      ON DUPLICATE KEY UPDATE 
                        cashier_id = VALUES(cashier_id),
                        expected_cash = VALUES(expected_cash),
                        actual_cash = VALUES(actual_cash),
                        variance_cash = VALUES(variance_cash),
                        expected_mpesa = VALUES(expected_mpesa),
                        actual_mpesa = VALUES(actual_mpesa),
                        variance_mpesa = VALUES(variance_mpesa),
                        expected_credit = VALUES(expected_credit),
                        actual_credit = VALUES(actual_credit),
                        variance_credit = VALUES(variance_credit),
                        notes = VALUES(notes),
                        created_at = CURRENT_TIMESTAMP";

            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':closing_date', $data['closing_date']);
            $stmt->bindParam(':cashier_id', $data['cashier_id']);
            $stmt->bindParam(':expected_cash', $data['expected_cash']);
            $stmt->bindParam(':actual_cash', $data['actual_cash']);
            $stmt->bindParam(':variance_cash', $variance_cash);
            $stmt->bindParam(':expected_mpesa', $data['expected_mpesa']);
            $stmt->bindParam(':actual_mpesa', $data['actual_mpesa']);
            $stmt->bindParam(':variance_mpesa', $variance_mpesa);
            $stmt->bindParam(':expected_credit', $data['expected_credit']);
            $stmt->bindParam(':actual_credit', $data['actual_credit']);
            $stmt->bindParam(':variance_credit', $variance_credit);
            $stmt->bindParam(':notes', $data['notes']);

            $success = $stmt->execute();

            if ($success) {
                // Log to Audit Log
                AuditController::logActivity(
                    $data['cashier_id'],
                    'EOD Register Reconciliation Closed',
                    'register_closings',
                    $this->db->lastInsertId(),
                    null,
                    [
                        'closing_date' => $data['closing_date'],
                        'actual_cash' => $data['actual_cash'],
                        'variance_cash' => $variance_cash,
                        'actual_mpesa' => $data['actual_mpesa'],
                        'variance_mpesa' => $variance_mpesa,
                        'actual_credit' => $data['actual_credit'],
                        'variance_credit' => $variance_credit
                    ]
                );
                $this->db->commit();
                return true;
            }

            $this->db->rollBack();
            return false;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log("saveEODReconciliation error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Retrieve running closing audit history
     *
     * @param int $limit
     * @return array
     */
    public function getEODHistory($limit = 30)
    {
        if (!$this->db) return [];
        try {
            $query = "SELECT rc.*, u.full_name as cashier_name 
                      FROM register_closings rc
                      JOIN users u ON rc.cashier_id = u.id
                      ORDER BY rc.closing_date DESC 
                      LIMIT :limit";
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("getEODHistory error: " . $e->getMessage());
            return [];
        }
    }
}
