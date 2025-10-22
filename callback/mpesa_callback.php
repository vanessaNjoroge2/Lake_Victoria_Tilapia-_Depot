<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../controllers/MpesaController.php';

// Log the callback for debugging
file_put_contents('mpesa_callback.log', date('Y-m-d H:i:s') . " - " . file_get_contents('php://input') . "\n", FILE_APPEND);

$callback_data = file_get_contents('php://input');

$mpesaController = new MpesaController();
$result = $mpesaController->handleCallback($callback_data);

// Return response to M-Pesa
header('Content-Type: application/json');
echo json_encode(['ResultCode' => 0, 'ResultDesc' => 'Callback processed successfully']);
