<?php
class Fish
{
    private $conn;
    private $table = 'fish';

    // Property declarations
    public $id;
    public $name;
    public $description;
    public $price;
    public $image_url;
    public $category;
    public $stock_quantity;
    public $weight_range;
    public $is_active;
    public $created_at;
    public $updated_at;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // CREATE - Add new fish
    public function create()
    {
        $query = "INSERT INTO " . $this->table . " 
                 SET name=:name, description=:description, price=:price, 
                     image_url=:image_url, category=:category, stock_quantity=:stock_quantity, 
                     weight_range=:weight_range, is_active=:is_active";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":price", $this->price);
        $stmt->bindParam(":image_url", $this->image_url);
        $stmt->bindParam(":category", $this->category);
        $stmt->bindParam(":stock_quantity", $this->stock_quantity);
        $stmt->bindParam(":weight_range", $this->weight_range);
        $stmt->bindParam(":is_active", $this->is_active);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // UPDATE - Update fish details
    public function update()
    {
        $query = "UPDATE " . $this->table . " 
                 SET name=:name, description=:description, price=:price, 
                     image_url=:image_url, category=:category, stock_quantity=:stock_quantity, 
                     weight_range=:weight_range, is_active=:is_active, 
                     updated_at = CURRENT_TIMESTAMP 
                 WHERE id=:id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":price", $this->price);
        $stmt->bindParam(":image_url", $this->image_url);
        $stmt->bindParam(":category", $this->category);
        $stmt->bindParam(":stock_quantity", $this->stock_quantity);
        $stmt->bindParam(":weight_range", $this->weight_range);
        $stmt->bindParam(":is_active", $this->is_active);
        $stmt->bindParam(":id", $this->id);

        return $stmt->execute();
    }

    // READ - Get all active fish
    public function getAllActive()
    {
        $query = "SELECT * FROM " . $this->table . " WHERE is_active = 1 ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // READ - Get all fish (including inactive)
    public function getAll()
    {
        $query = "SELECT * FROM " . $this->table . " ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // READ - Get fish by ID
    public function getById($id)
    {
        $query = "SELECT * FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // DELETE - Remove fish
    public function delete($id)
    {
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }

    // SEARCH - Search fish
    public function search($searchTerm)
    {
        $query = "SELECT * FROM " . $this->table . " 
                 WHERE (name LIKE :search OR description LIKE :search OR category LIKE :search) 
                 AND is_active = 1 
                 ORDER BY name";

        $stmt = $this->conn->prepare($query);
        $searchTerm = "%$searchTerm%";
        $stmt->bindParam(":search", $searchTerm);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // UPDATE STOCK - Update fish stock quantity (for orders)
    public function updateStock($id, $quantity)
    {
        $query = "UPDATE " . $this->table . " SET stock_quantity = stock_quantity - :quantity WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":quantity", $quantity);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }

    // GET LOW STOCK - Get items with low stock
    public function getLowStockItems()
    {
        $query = "SELECT * FROM " . $this->table . " WHERE stock_quantity < 10 AND is_active = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // NEW METHODS ADDED:

    // UPDATE FISH STATUS - Activate/Deactivate fish
    public function updateFishStatus($id, $status)
    {
        $query = "UPDATE " . $this->table . " SET is_active = :status, updated_at = CURRENT_TIMESTAMP WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":status", $status);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }

    // UPDATE STOCK QUANTITY - Direct stock update (for management)
    public function updateStockQuantity($id, $new_quantity)
    {
        $query = "UPDATE " . $this->table . " SET stock_quantity = :quantity, updated_at = CURRENT_TIMESTAMP WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":quantity", $new_quantity);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }

    // GET FISH STATS - Get statistics about fish products
    public function getFishStats()
    {
        $query = "SELECT 
                    COUNT(*) as total_fish,
                    SUM(is_active = 1) as active_fish,
                    SUM(is_active = 0) as inactive_fish,
                    SUM(stock_quantity < 10 AND is_active = 1) as low_stock,
                    SUM(stock_quantity = 0 AND is_active = 1) as out_of_stock,
                    AVG(price) as avg_price
                  FROM " . $this->table;

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // GET FISH BY CATEGORY - Get fish grouped by category
    public function getByCategory($category = null)
    {
        if ($category) {
            $query = "SELECT * FROM " . $this->table . " WHERE category = :category AND is_active = 1 ORDER BY name";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":category", $category);
        } else {
            $query = "SELECT * FROM " . $this->table . " WHERE is_active = 1 ORDER BY category, name";
            $stmt = $this->conn->prepare($query);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // GET CATEGORIES - Get all unique categories
    public function getCategories()
    {
        $query = "SELECT DISTINCT category FROM " . $this->table . " WHERE is_active = 1 ORDER BY category";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
    }

    // CHECK STOCK AVAILABILITY - Check if enough stock exists
    public function checkStockAvailability($id, $requested_quantity)
    {
        $query = "SELECT stock_quantity FROM " . $this->table . " WHERE id = :id AND is_active = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        $fish = $stmt->fetch(PDO::FETCH_ASSOC);

        return $fish && $fish['stock_quantity'] >= $requested_quantity;
    }

    // GET RECENTLY ADDED - Get recently added fish products
    public function getRecentlyAdded($limit = 5)
    {
        $query = "SELECT * FROM " . $this->table . " WHERE is_active = 1 ORDER BY created_at DESC LIMIT :limit";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // GET POPULAR PRODUCTS - Get best selling fish (you'll need to implement this with order data)
    public function getPopularProducts($limit = 5)
    {
        // This would typically join with order_items table
        // For now, return recently added as placeholder
        return $this->getRecentlyAdded($limit);
    }
}
