<?php

/**
 * Lake Victoria Tilapia Depot - Main Entry Point
 * Redirects to login page
 */

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect to login page
header('Location: views/auth/login.php');
exit();
