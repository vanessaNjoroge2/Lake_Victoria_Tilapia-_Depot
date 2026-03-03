<?php

/**
 * Logout Handler
 * Centralized logout endpoint for all user roles
 * 
 * Purpose:
 * - Securely destroy user session
 * - Clear all session data
 * - Redirect to login page with success message
 * 
 * Usage:
 * - Link to this file from any page: /views/auth/logout.php
 * - Can be accessed via GET request
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include required files
require_once '../../config/config.php';
require_once '../../controllers/AuthController.php';

// Create auth controller instance
$authController = new AuthController();

// Perform logout
$authController->logout();

// Note: The logout() method in AuthController handles:
// 1. Session variable clearing
// 2. Session cookie deletion
// 3. Session destruction
// 4. Setting flash message
// 5. Redirect to login page
// 
// This script will not reach beyond the logout() call due to exit() in the method
