<?php

/**
 * Route Guard Helper
 * 
 * This file provides a simple way to protect pages by role
 * Include this file at the top of any page that requires authentication
 * 
 * Usage Examples:
 * 
 * 1. Protect any page (any logged-in user):
 *    require_once 'path/to/route_guard.php';
 *    guardRoute();
 * 
 * 2. Protect customer-only pages:
 *    require_once 'path/to/route_guard.php';
 *    guardRoute(['customer']);
 * 
 * 3. Protect staff/admin pages:
 *    require_once 'path/to/route_guard.php';
 *    guardRoute(['staff', 'admin']);
 * 
 * 4. Protect admin-only pages:
 *    require_once 'path/to/route_guard.php';
 *    guardRoute(['admin']);
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include auth controller if not already included
if (!class_exists('AuthController')) {
    require_once __DIR__ . '/../controllers/AuthController.php';
}

/**
 * Guard a route - require authentication and optionally specific roles
 * 
 * @param array|null $allowedRoles Array of allowed role names, or null for any authenticated user
 */
function guardRoute($allowedRoles = null)
{
    $authController = new AuthController();

    if ($allowedRoles === null) {
        // Just require authentication, any role is allowed
        $authController->requireAuth();
    } else {
        // Require specific roles
        $authController->requireRole($allowedRoles);
    }
}

/**
 * Check if current user is logged in
 * @return bool
 */
function isUserLoggedIn()
{
    $authController = new AuthController();
    return $authController->isLoggedIn();
}

/**
 * Get current user's role
 * @return string|null
 */
function getCurrentUserRole()
{
    return $_SESSION['role'] ?? null;
}

/**
 * Check if current user has a specific role
 * @param string $role Role to check
 * @return bool
 */
function hasRole($role)
{
    return getCurrentUserRole() === $role;
}

/**
 * Check if current user has any of the specified roles
 * @param array $roles Array of roles to check
 * @return bool
 */
function hasAnyRole($roles)
{
    $currentRole = getCurrentUserRole();
    return in_array($currentRole, $roles);
}
