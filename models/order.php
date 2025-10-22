<?php
class Order
{
    private $conn;
    private $table = 'orders';
    private $items_table = 'order_items';

    // Properties
    public $id;
    public $customer_id;
    public $total_amount;
    public $status;
    public $payment_status;
    public $mpesa_receipt;
    public $shipping_address;
    public $phone;
    public $notes;
    public $created_at;
    public $updated_at;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // CREATE - Create new order
    public function create()
    {
        $query = "INSERT INTO {$this->table}
                  SET customer_id = :customer_id,
                      total_amount = :total_amount,
                      shipping_address = :shipping_address,
                      phone = :phone,
                      notes = :notes,
                      status = 'pending',
                      payment_status = 'pending',
                      created_at = NOW()";

        $stmt = $this->conn->prepare($query);

        // Sanitize inputs
        $this->customer_id = htmlspecialchars(strip_tags($this->customer_id));
        $this->total_amount = htmlspecialchars(strip_tags($this->total_amount));
        $this->shipping_address = htmlspecialchars(strip_tags($this->shipping_address));
        $this->phone = htmlspecialchars(strip_tags($this->phone));
        $this->notes = htmlspecialchars(strip_tags($this->notes ?? ''));

        $stmt->bindParam(":customer_id", $this->customer_id);
        $stmt->bindParam(":total_amount", $this->total_amount);
        $stmt->bindParam(":shipping_address", $this->shipping_address);
        $stmt->bindParam(":phone", $this->phone);
        $stmt->bindParam(":notes", $this->notes);

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }

        return false;
    }

    // ADD ORDER ITEM
    public function addOrderItem($order_id, $fish_id, $quantity, $unit_price)
    {
        $query = "INSERT INTO {$this->items_table} 
                  (order_id, fish_id, quantity, unit_price)
                  VALUES (:order_id, :fish_id, :quantity, :unit_price)";

        $stmt = $this->conn->prepare($query);

        // Sanitize inputs
        $order_id = htmlspecialchars(strip_tags($order_id));
        $fish_id = htmlspecialchars(strip_tags($fish_id));
        $quantity = htmlspecialchars(strip_tags($quantity));
        $unit_price = htmlspecialchars(strip_tags($unit_price));

        $stmt->bindParam(":order_id", $order_id);
        $stmt->bindParam(":fish_id", $fish_id);
        $stmt->bindParam(":quantity", $quantity);
        $stmt->bindParam(":unit_price", $unit_price);

        return $stmt->execute();
    }

    // READ - Get all orders
    public function getAll()
    {
        $query = "SELECT o.*, u.full_name AS customer_name, u.email AS customer_email
                  FROM {$this->table} o
                  LEFT JOIN users u ON o.customer_id = u.id
                  ORDER BY o.created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // READ - Get orders by customer
    public function getByCustomer($customer_id)
    {
        $query = "SELECT o.*,
                         (SELECT COUNT(*) FROM {$this->items_table} oi WHERE oi.order_id = o.id) AS item_count
                  FROM {$this->table} o
                  WHERE o.customer_id = :customer_id
                  ORDER BY o.created_at DESC";

        $stmt = $this->conn->prepare($query);
        $customer_id = htmlspecialchars(strip_tags($customer_id));
        $stmt->bindParam(":customer_id", $customer_id);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // READ - Get order by ID
    public function getById($id)
    {
        $query = "SELECT o.*, u.full_name AS customer_name, u.email AS customer_email
                  FROM {$this->table} o
                  LEFT JOIN users u ON o.customer_id = u.id
                  WHERE o.id = :id";

        $stmt = $this->conn->prepare($query);
        $id = htmlspecialchars(strip_tags($id));
        $stmt->bindParam(":id", $id);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    // READ - Get order items
    public function getOrderItems($order_id)
    {
        $query = "SELECT oi.*, f.name AS fish_name, f.image_url
                  FROM {$this->items_table} oi
                  LEFT JOIN fish f ON oi.fish_id = f.id
                  WHERE oi.order_id = :order_id";

        $stmt = $this->conn->prepare($query);
        $order_id = htmlspecialchars(strip_tags($order_id));
        $stmt->bindParam(":order_id", $order_id);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // UPDATE - Update order status
    public function updateStatus($id, $status)
    {
        $query = "UPDATE {$this->table}
                  SET status = :status, updated_at = NOW()
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $id = htmlspecialchars(strip_tags($id));
        $status = htmlspecialchars(strip_tags($status));

        $stmt->bindParam(":status", $status);
        $stmt->bindParam(":id", $id);

        return $stmt->execute();
    }

    // UPDATE - Update payment status
    public function updatePaymentStatus($id, $payment_status, $mpesa_receipt = null)
    {
        $query = "UPDATE {$this->table}
                  SET payment_status = :payment_status,
                      mpesa_receipt = :mpesa_receipt,
                      updated_at = NOW()
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $id = htmlspecialchars(strip_tags($id));
        $payment_status = htmlspecialchars(strip_tags($payment_status));
        $mpesa_receipt = $mpesa_receipt ? htmlspecialchars(strip_tags($mpesa_receipt)) : null;

        $stmt->bindParam(":payment_status", $payment_status);
        $stmt->bindParam(":mpesa_receipt", $mpesa_receipt);
        $stmt->bindParam(":id", $id);

        return $stmt->execute();
    }

    // ANALYTICS - Sales overview
    public function getSalesAnalytics()
    {
        $query = "SELECT 
                    COUNT(*) AS total_orders,
                    COALESCE(SUM(total_amount), 0) AS total_revenue,
                    COUNT(DISTINCT customer_id) AS total_customers
                  FROM {$this->table}
                  WHERE status != 'cancelled'";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: ['total_orders' => 0, 'total_revenue' => 0, 'total_customers' => 0];
    }

    // ANALYTICS - Monthly sales
    public function getMonthlySales()
    {
        $query = "SELECT 
                    DATE_FORMAT(created_at, '%Y-%m') AS month,
                    COUNT(*) AS order_count,
                    COALESCE(SUM(total_amount), 0) AS revenue
                  FROM {$this->table}
                  WHERE status != 'cancelled'
                  GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                  ORDER BY month DESC
                  LIMIT 12";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // READ - Recent orders
    public function getRecentOrders($limit = 5)
    {
        $query = "SELECT o.*, u.full_name AS customer_name
                  FROM {$this->table} o
                  LEFT JOIN users u ON o.customer_id = u.id
                  ORDER BY o.created_at DESC
                  LIMIT :limit";

        $stmt = $this->conn->prepare($query);
        $limit = (int)htmlspecialchars(strip_tags($limit));
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // READ - Order statistics
    public function getOrderStats()
    {
        $query = "SELECT 
                    COUNT(*) AS total_orders,
                    COALESCE(SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END), 0) AS pending_orders,
                    COALESCE(SUM(CASE WHEN status = 'processing' THEN 1 ELSE 0 END), 0) AS processing_orders,
                    COALESCE(SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END), 0) AS completed_orders,
                    COALESCE(SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END), 0) AS cancelled_orders,
                    COALESCE(SUM(total_amount), 0) AS total_revenue
                  FROM {$this->table}";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: [
            'total_orders' => 0,
            'pending_orders' => 0,
            'processing_orders' => 0,
            'completed_orders' => 0,
            'cancelled_orders' => 0,
            'total_revenue' => 0
        ];
    }

    // DELETE - Delete order and its items
    public function delete($id)
    {
        try {
            $this->conn->beginTransaction();

            // Delete order items first
            $deleteItemsQuery = "DELETE FROM {$this->items_table} WHERE order_id = :order_id";
            $stmt1 = $this->conn->prepare($deleteItemsQuery);
            $id = htmlspecialchars(strip_tags($id));
            $stmt1->bindParam(":order_id", $id);
            $stmt1->execute();

            // Delete the order
            $deleteOrderQuery = "DELETE FROM {$this->table} WHERE id = :id";
            $stmt2 = $this->conn->prepare($deleteOrderQuery);
            $stmt2->bindParam(":id", $id);
            $stmt2->execute();

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("Delete order error: " . $e->getMessage());
            return false;
        }
    }

    // DELETE - Delete order by customer (with restrictions)
    public function deleteOrderByCustomer($order_id, $customer_id)
    {
        try {
            $this->conn->beginTransaction();

            // Verify the order belongs to the customer and is cancellable
            $verifyQuery = "SELECT id, status FROM {$this->table} 
                           WHERE id = :order_id 
                           AND customer_id = :customer_id
                           AND status IN ('pending', 'cancelled')";

            $stmt = $this->conn->prepare($verifyQuery);
            $order_id = htmlspecialchars(strip_tags($order_id));
            $customer_id = htmlspecialchars(strip_tags($customer_id));
            $stmt->bindParam(":order_id", $order_id);
            $stmt->bindParam(":customer_id", $customer_id);
            $stmt->execute();

            $order = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$order) {
                $this->conn->rollBack();
                return false;
            }

            // Delete order items first
            $deleteItemsQuery = "DELETE FROM {$this->items_table} WHERE order_id = :order_id";
            $stmt1 = $this->conn->prepare($deleteItemsQuery);
            $stmt1->bindParam(":order_id", $order_id);

            if (!$stmt1->execute()) {
                $this->conn->rollBack();
                return false;
            }

            // Delete the order
            $deleteOrderQuery = "DELETE FROM {$this->table} WHERE id = :order_id";
            $stmt2 = $this->conn->prepare($deleteOrderQuery);
            $stmt2->bindParam(":order_id", $order_id);

            if (!$stmt2->execute()) {
                $this->conn->rollBack();
                return false;
            }

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("Delete order by customer error: " . $e->getMessage());
            return false;
        }
    }

    // CHECK - Check if order can be deleted by customer
    public function canCustomerDeleteOrder($order_id, $customer_id)
    {
        $query = "SELECT id, status FROM {$this->table} 
                  WHERE id = :order_id 
                  AND customer_id = :customer_id
                  AND status IN ('pending', 'cancelled')";

        $stmt = $this->conn->prepare($query);
        $order_id = htmlspecialchars(strip_tags($order_id));
        $customer_id = htmlspecialchars(strip_tags($customer_id));
        $stmt->bindParam(":order_id", $order_id);
        $stmt->bindParam(":customer_id", $customer_id);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    // SEARCH - Orders by keyword
    public function searchOrders($search_term)
    {
        $query = "SELECT o.*, u.full_name AS customer_name, u.email AS customer_email
                  FROM {$this->table} o
                  LEFT JOIN users u ON o.customer_id = u.id
                  WHERE o.id LIKE :search
                     OR u.full_name LIKE :search
                     OR u.email LIKE :search
                  ORDER BY o.created_at DESC";

        $stmt = $this->conn->prepare($query);
        $search_term = htmlspecialchars(strip_tags($search_term));
        $search = "%$search_term%";
        $stmt->bindParam(":search", $search);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // READ - Orders by status
    public function getOrdersByStatus($status)
    {
        $query = "SELECT o.*, u.full_name AS customer_name, u.email AS customer_email
                  FROM {$this->table} o
                  LEFT JOIN users u ON o.customer_id = u.id
                  WHERE o.status = :status
                  ORDER BY o.created_at DESC";

        $stmt = $this->conn->prepare($query);
        $status = htmlspecialchars(strip_tags($status));
        $stmt->bindParam(":status", $status);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // CANCEL - Cancel order (only if pending)
    public function cancelOrder($order_id, $customer_id)
    {
        $verifyQuery = "SELECT id FROM {$this->table}
                        WHERE id = :order_id
                          AND customer_id = :customer_id
                          AND status = 'pending'";

        $stmt = $this->conn->prepare($verifyQuery);
        $order_id = htmlspecialchars(strip_tags($order_id));
        $customer_id = htmlspecialchars(strip_tags($customer_id));
        $stmt->bindParam(":order_id", $order_id);
        $stmt->bindParam(":customer_id", $customer_id);
        $stmt->execute();

        if ($stmt->rowCount() === 0) {
            return false;
        }

        $updateQuery = "UPDATE {$this->table}
                        SET status = 'cancelled', updated_at = NOW()
                        WHERE id = :order_id";

        $updateStmt = $this->conn->prepare($updateQuery);
        $updateStmt->bindParam(":order_id", $order_id);

        return $updateStmt->execute();
    }

    // DELETE - Delete order (only if cancelled)
    public function deleteOrder($order_id, $customer_id)
    {
        // Verify order belongs to customer and is cancelled
        $verifyQuery = "SELECT id FROM {$this->table}
                        WHERE id = :order_id
                          AND customer_id = :customer_id
                          AND status = 'cancelled'";

        $stmt = $this->conn->prepare($verifyQuery);
        $order_id = htmlspecialchars(strip_tags($order_id));
        $customer_id = htmlspecialchars(strip_tags($customer_id));
        $stmt->bindParam(":order_id", $order_id);
        $stmt->bindParam(":customer_id", $customer_id);
        $stmt->execute();

        if ($stmt->rowCount() === 0) {
            return false; // Order not found or not cancelled
        }

        // Delete order (order_items will be deleted automatically due to ON DELETE CASCADE)
        $deleteQuery = "DELETE FROM {$this->table} WHERE id = :order_id";
        $deleteStmt = $this->conn->prepare($deleteQuery);
        $deleteStmt->bindParam(":order_id", $order_id);

        return $deleteStmt->execute();
    }
}

