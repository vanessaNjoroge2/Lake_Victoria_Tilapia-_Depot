<?php
require_once '../config/config.php';
require_once '../controllers/AuthController.php';
require_once '../controllers/CartController.php';

$authController = new AuthController();
$authController->requireAuth();

$cartController = new CartController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $fish_id = $_POST['fish_id'] ?? '';
    $customer_id = $_SESSION['user_id'];

    switch ($action) {
        case 'add':
            $quantity = $_POST['quantity'] ?? 1;
            $result = $cartController->addToCart($customer_id, $fish_id, $quantity);
            if ($result === true) {
                $_SESSION['success'] = 'Item added to cart successfully!';
            } else {
                $_SESSION['error'] = $result;
            }
            break;

        case 'update':
            $quantity_change = $_POST['quantity'] ?? 1;
            $result = $cartController->updateQuantity($customer_id, $fish_id, $quantity_change);
            if ($result === true) {
                $_SESSION['success'] = 'Cart updated successfully!';
            } else {
                $_SESSION['error'] = $result;
            }
            break;

        case 'remove':
            $result = $cartController->removeFromCart($customer_id, $fish_id);
            if ($result === true) {
                $_SESSION['success'] = 'Item removed from cart!';
            } else {
                $_SESSION['error'] = 'Failed to remove item from cart.';
            }
            break;
    }

    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit();
}
