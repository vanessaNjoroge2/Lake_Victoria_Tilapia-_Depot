<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Generate and return a secure CSRF token
 *
 * @return string
 */
function generate_csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify if the provided CSRF token matches the session token
 *
 * @param string $token
 * @return bool
 */
function verify_csrf_token(string $token): bool {
    return isset($_SESSION['csrf_token']) && 
           hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Helper to generate a hidden CSRF token input field for forms
 *
 * @return string
 */
function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(generate_csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
}
