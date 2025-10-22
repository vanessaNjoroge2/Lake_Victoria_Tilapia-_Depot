<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../controllers/AuthController.php';

$authController = new AuthController();

if (isset($_GET['action'])) {
    $action = $_GET['action'];

    switch ($action) {
        case 'logout':
            $authController->logout();
            break;
    }
}
