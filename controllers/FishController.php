<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Fish.php';

class FishController
{
    private $db;
    private $fish;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->fish = new Fish($this->db);
    }

    public function getActiveFish()
    {
        return $this->fish->getAllActive();
    }

    public function getAllFish()
    {
        return $this->fish->getAll();
    }

    public function getLowStockItems()
    {
        return $this->fish->getLowStockItems();
    }

    public function searchFish($searchTerm)
    {
        return $this->fish->search($searchTerm);
    }

    // NEW METHODS ADDED:

    public function toggleFishStatus($fish_id)
    {
        try {
            // Get current status
            $fish = $this->fish->getById($fish_id);
            if ($fish) {
                $new_status = $fish['is_active'] ? 0 : 1;
                return $this->fish->updateFishStatus($fish_id, $new_status);
            }
            return false;
        } catch (Exception $e) {
            error_log("Toggle fish status error: " . $e->getMessage());
            return false;
        }
    }

    public function deleteFish($fish_id)
    {
        try {
            return $this->fish->delete($fish_id);
        } catch (Exception $e) {
            error_log("Delete fish error: " . $e->getMessage());
            return false;
        }
    }

    public function getFishById($id)
    {
        try {
            return $this->fish->getById($id);
        } catch (Exception $e) {
            error_log("Get fish by ID error: " . $e->getMessage());
            return null;
        }
    }

    public function createFish($data)
    {
        try {
            $this->fish->name = $data['name'];
            $this->fish->description = $data['description'];
            $this->fish->price = $data['price'];
            $this->fish->image_url = $data['image_url'] ?? 'default_fish.png';
            $this->fish->category = $data['category'] ?? 'Tilapia';
            $this->fish->stock_quantity = $data['stock_quantity'];
            $this->fish->weight_range = $data['weight_range'];
            $this->fish->is_active = $data['is_active'] ?? 1;

            return $this->fish->create();
        } catch (Exception $e) {
            error_log("Create fish error: " . $e->getMessage());
            return false;
        }
    }

    public function updateFish($id, $data)
    {
        try {
            $this->fish->id = $id;
            $this->fish->name = $data['name'];
            $this->fish->description = $data['description'];
            $this->fish->price = $data['price'];
            $this->fish->image_url = $data['image_url'];
            $this->fish->category = $data['category'];
            $this->fish->stock_quantity = $data['stock_quantity'];
            $this->fish->weight_range = $data['weight_range'];
            $this->fish->is_active = $data['is_active'];

            return $this->fish->update();
        } catch (Exception $e) {
            error_log("Update fish error: " . $e->getMessage());
            return false;
        }
    }

    public function updateStockQuantity($fish_id, $new_quantity)
    {
        try {
            return $this->fish->updateStockQuantity($fish_id, $new_quantity);
        } catch (Exception $e) {
            error_log("Update stock quantity error: " . $e->getMessage());
            return false;
        }
    }

    public function getFishStats()
    {
        try {
            return $this->fish->getFishStats();
        } catch (Exception $e) {
            error_log("Get fish stats error: " . $e->getMessage());
            return ['total_fish' => 0, 'active_fish' => 0, 'low_stock' => 0];
        }
    }
}
