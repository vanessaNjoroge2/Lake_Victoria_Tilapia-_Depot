<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/config.php';

/**
 * Enforce role-based access control. Redirects to login if user lacks the required role.
 *
 * @param array $allowed_roles
 * @return void
 */
function require_role(array $allowed_roles): void {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || !in_array($_SESSION['role'], $allowed_roles)) {
        http_response_code(403);
        
        // Use BASE_URL if defined, otherwise fall back to relative path
        $redirectUrl = defined('BASE_URL') 
            ? BASE_URL . '/views/auth/login.php?reason=unauthorized' 
            : '/views/auth/login.php?reason=unauthorized';
            
        header('Location: ' . $redirectUrl);
        exit;
    }
}
