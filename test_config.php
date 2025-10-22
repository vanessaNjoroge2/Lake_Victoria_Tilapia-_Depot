<?php
require_once 'config/config.php';
require_once 'config/database.php';

echo "<h2>Configuration Test</h2>";

// Test database connection
try {
    $database = new Database();
    $db = $database->getConnection();
    echo "✅ Database connection: SUCCESS<br>";
} catch (Exception $e) {
    echo "❌ Database connection: FAILED - " . $e->getMessage() . "<br>";
}

// Check M-Pesa config
echo "M-Pesa Consumer Key: " . (defined('MPESA_CONSUMER_KEY') ? 'Set' : 'Not Set') . "<br>";
echo "M-Pesa Shortcode: " . (defined('MPESA_SHORTCODE') ? 'Set' : 'Not Set') . "<br>";

// Test model loading
try {
    require_once 'models/Order.php';
    require_once 'models/Cart.php';
    require_once 'models/User.php';
    echo "✅ Models: LOADED SUCCESSFULLY<br>";
} catch (Exception $e) {
    echo "❌ Models: FAILED - " . $e->getMessage() . "<br>";
}

// Test controller loading
try {
    require_once 'controllers/OrderController.php';
    require_once 'controllers/MpesaController.php';
    echo "✅ Controllers: LOADED SUCCESSFULLY<br>";
} catch (Exception $e) {
    echo "❌ Controllers: FAILED - " . $e->getMessage() . "<br>";
}
