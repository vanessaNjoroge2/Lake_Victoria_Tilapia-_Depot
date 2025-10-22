<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Cart.php';
require_once __DIR__ . '/../models/Fish.php';

class CartController
{
    private $db;
    private $cart;
    private $fish;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->cart = new Cart($this->db);
        $this->fish = new Fish($this->db);
    }

    public function addToCart($customer_id, $fish_id, $quantity)
    {
        // Check if fish exists and has sufficient stock
        $fish = $this->fish->getById($fish_id);
        if (!$fish) {
            return "Fish item not found";
        }

        if ($fish['stock_quantity'] < $quantity) {
            return "Insufficient stock. Only {$fish['stock_quantity']} items available.";
        }

        // Set the properties on the cart model
        $this->cart->customer_id = $customer_id;
        $this->cart->fish_id = $fish_id;
        $this->cart->quantity = $quantity;

        if ($this->cart->addToCart()) {
            return true;
        }
        return "Failed to add item to cart";
    }

    public function getCartItems($customer_id)
    {
        return $this->cart->getCartItems($customer_id);
    }

    public function updateQuantity($customer_id, $fish_id, $change)
    {
        // Get current quantity
        $cartItems = $this->cart->getCartItems($customer_id);
        $currentQuantity = 0;

        foreach ($cartItems as $item) {
            if ($item['fish_id'] == $fish_id) {
                $currentQuantity = $item['quantity'];
                break;
            }
        }

        $newQuantity = $currentQuantity + $change;

        // Check stock availability if increasing quantity
        if ($change > 0) {
            $fish = $this->fish->getById($fish_id);
            if ($fish['stock_quantity'] < $newQuantity) {
                return "Insufficient stock. Only {$fish['stock_quantity']} items available.";
            }
        }

        return $this->cart->updateQuantity($customer_id, $fish_id, $newQuantity);
    }

    public function removeFromCart($customer_id, $fish_id)
    {
        return $this->cart->removeFromCart($customer_id, $fish_id);
    }

    public function clearCart($customer_id)
    {
        return $this->cart->clearCart($customer_id);
    }

    public function getCartTotal($customer_id)
    {
        return $this->cart->getCartTotal($customer_id);
    }

    public function getCartItemCount($customer_id)
    {
        return $this->cart->getCartItemCount($customer_id);
    }

    public function validateCartItems($customer_id)
    {
        $cartItems = $this->getCartItems($customer_id);
        $validItems = [];
        $total = 0;

        foreach ($cartItems as $item) {
            $fish = $this->fish->getById($item['fish_id']);
            if ($fish && $fish['is_active'] && $fish['stock_quantity'] >= $item['quantity']) {
                $validItems[] = $item;
                $total += $item['price'] * $item['quantity'];
            }
        }

        return [
            'items' => $validItems,
            'total' => $total
        ];
    }
}
