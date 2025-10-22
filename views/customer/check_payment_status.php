<?php
session_start();
require_once '../../config/database.php';
require_once '../../controllers/OrderController.php';

header('Content-Type: application/json');

if (!isset($_GET['order_id'])) {
    echo json_encode(['success' => false, 'message' => 'Order ID required']);
    exit();
}

$order_id = $_GET['order_id'];
$orderController = new OrderController();
$payment_status = $orderController->getOrderPaymentStatus($order_id);

if ($payment_status && $payment_status['status'] === 'success') {
    // Clear cart and session data
    $database = new Database();
    $db = $database->getConnection();
    $cart = new Cart($db);
    $cart->clearCart($_SESSION['user_id']);

    unset($_SESSION['checkout_data']);

    echo json_encode(['success' => true, 'message' => 'Payment completed']);
} else {
    echo json_encode(['success' => false, 'message' => 'Payment pending']);
}
