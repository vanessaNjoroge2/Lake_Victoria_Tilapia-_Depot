<?php
require_once 'config/config.php';
require_once 'config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    echo "Connection successful!\n";
} catch (Exception $e) {
    echo "Connection failed: " . $e->getMessage() . "\n";
}
?>
