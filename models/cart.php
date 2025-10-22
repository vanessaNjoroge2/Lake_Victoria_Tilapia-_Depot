<?php
class Cart
{
    private $conn;
    private $table = 'cart';

    // Add property declarations
    public $id;
    public $customer_id;
    public $fish_id;
    public $quantity;
    public $created_at;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function addToCart()
    {
        try {
            // Validate input
            if (empty($this->customer_id) || empty($this->fish_id) || $this->quantity <= 0) {
                return false;
            }

            $checkQuery = "SELECT * FROM " . $this->table . " 
                          WHERE customer_id = :customer_id AND fish_id = :fish_id";

            $checkStmt = $this->conn->prepare($checkQuery);
            $checkStmt->bindParam(":customer_id", $this->customer_id);
            $checkStmt->bindParam(":fish_id", $this->fish_id);
            $checkStmt->execute();

            if ($checkStmt->rowCount() > 0) {
                $query = "UPDATE " . $this->table . " 
                         SET quantity = quantity + :quantity 
                         WHERE customer_id = :customer_id AND fish_id = :fish_id";
            } else {
                $query = "INSERT INTO " . $this->table . " 
                         SET customer_id=:customer_id, fish_id=:fish_id, quantity=:quantity";
            }

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":customer_id", $this->customer_id);
            $stmt->bindParam(":fish_id", $this->fish_id);
            $stmt->bindParam(":quantity", $this->quantity);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Cart Error: " . $e->getMessage());
            return false;
        }
    }

    public function getCartItems($customer_id)
    {
        try {
            if (empty($customer_id)) {
                return [];
            }

            $query = "SELECT c.*, f.name, f.price, f.image_url, f.stock_quantity, 
                             f.weight_range, f.description 
                     FROM " . $this->table . " c 
                     LEFT JOIN fish f ON c.fish_id = f.id 
                     WHERE c.customer_id = :customer_id AND f.is_active = 1";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":customer_id", $customer_id);
            $stmt->execute();

            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Ensure all items have required fields with safe defaults
            foreach ($items as &$item) {
                $item = $this->sanitizeCartItem($item);
            }

            return $items;
        } catch (PDOException $e) {
            error_log("Get Cart Items Error: " . $e->getMessage());
            return [];
        }
    }

    private function sanitizeCartItem($item)
    {
        // Provide safe defaults for missing fields
        return [
            'id' => $item['id'] ?? 0,
            'customer_id' => $item['customer_id'] ?? 0,
            'fish_id' => $item['fish_id'] ?? 0,
            'quantity' => $item['quantity'] ?? 0,
            'name' => $item['name'] ?? 'Product Not Available',
            'price' => $item['price'] ?? 0.00,
            'image_url' => $item['image_url'] ?? 'default-image.jpg',
            'stock_quantity' => $item['stock_quantity'] ?? 0,
            'weight_range' => $item['weight_range'] ?? 'Not specified',
            'description' => $item['description'] ?? 'No description available',
            'created_at' => $item['created_at'] ?? date('Y-m-d H:i:s')
        ];
    }

    public function updateQuantity($customer_id, $fish_id, $quantity)
    {
        try {
            if (empty($customer_id) || empty($fish_id)) {
                return false;
            }

            if ($quantity <= 0) {
                return $this->removeFromCart($customer_id, $fish_id);
            }

            // Check stock availability
            $stockQuery = "SELECT stock_quantity FROM fish WHERE id = :fish_id AND is_active = 1";
            $stockStmt = $this->conn->prepare($stockQuery);
            $stockStmt->bindParam(":fish_id", $fish_id);
            $stockStmt->execute();
            $fish = $stockStmt->fetch(PDO::FETCH_ASSOC);

            if (!$fish || $quantity > $fish['stock_quantity']) {
                return false; // Quantity exceeds available stock
            }

            $query = "UPDATE " . $this->table . " 
                     SET quantity = :quantity 
                     WHERE customer_id = :customer_id AND fish_id = :fish_id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":quantity", $quantity);
            $stmt->bindParam(":customer_id", $customer_id);
            $stmt->bindParam(":fish_id", $fish_id);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Update Quantity Error: " . $e->getMessage());
            return false;
        }
    }

    public function removeFromCart($customer_id, $fish_id)
    {
        try {
            if (empty($customer_id) || empty($fish_id)) {
                return false;
            }

            $query = "DELETE FROM " . $this->table . " 
                     WHERE customer_id = :customer_id AND fish_id = :fish_id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":customer_id", $customer_id);
            $stmt->bindParam(":fish_id", $fish_id);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Remove From Cart Error: " . $e->getMessage());
            return false;
        }
    }

    public function clearCart($customer_id)
    {
        try {
            if (empty($customer_id)) {
                return false;
            }

            $query = "DELETE FROM " . $this->table . " WHERE customer_id = :customer_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":customer_id", $customer_id);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Clear Cart Error: " . $e->getMessage());
            return false;
        }
    }

    public function getCartTotal($customer_id)
    {
        try {
            $items = $this->getCartItems($customer_id);
            $total = 0;

            foreach ($items as $item) {
                $total += ($item['price'] ?? 0) * ($item['quantity'] ?? 0);
            }

            return number_format($total, 2, '.', '');
        } catch (Exception $e) {
            error_log("Get Cart Total Error: " . $e->getMessage());
            return 0.00;
        }
    }

    public function getCartItemCount($customer_id)
    {
        try {
            if (empty($customer_id)) {
                return 0;
            }

            $query = "SELECT SUM(quantity) as total_items FROM " . $this->table . " 
                     WHERE customer_id = :customer_id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":customer_id", $customer_id);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return $result['total_items'] ? (int)$result['total_items'] : 0;
        } catch (PDOException $e) {
            error_log("Get Cart Item Count Error: " . $e->getMessage());
            return 0;
        }
    }

    // New method to check if item exists in cart
    public function itemExists($customer_id, $fish_id)
    {
        try {
            $query = "SELECT COUNT(*) as count FROM " . $this->table . " 
                     WHERE customer_id = :customer_id AND fish_id = :fish_id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":customer_id", $customer_id);
            $stmt->bindParam(":fish_id", $fish_id);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'] > 0;
        } catch (PDOException $e) {
            error_log("Item Exists Check Error: " . $e->getMessage());
            return false;
        }
    }
}
