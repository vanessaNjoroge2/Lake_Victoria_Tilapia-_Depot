<?php

/**
 * Authentication Handler
 * Legacy handler for authentication actions
 * 
 * Note: For better organization, use the dedicated logout.php file instead
 * This file is kept for backward compatibility with existing logout links
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../controllers/AuthController.php';

$authController = new AuthController();

if (isset($_GET['action'])) {
    $action = $_GET['action'];

    switch ($action) {
        case 'logout':
            // Perform logout
            $authController->logout();
            break;

        default:
            // Unknown action - redirect to home
            header("Location: " . BASE_URL . "/landing.php");
            exit();
    }
} else {
    // No action specified - redirect to home
    header("Location: " . BASE_URL . "/landing.php");
    exit();
}
