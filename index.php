<?php

/**
 * Lake Victoria Tilapia Depot - Main Entry Point
 * Redirects to landing page
 */

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect to landing page
header('Location: landing.php');
exit();
