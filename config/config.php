<?php
// Base configuration - UPDATED FOR XAMPP3
define('BASE_URL', 'http://localhost/Lake-victoria-tilapia-depot');
define('SITE_NAME', 'Lake Victoria Tilapia Depot');

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'lake_victoria_tilapia_depot');
define('DB_USER', 'root');
define('DB_PASS', '');

// File upload configuration
define('UPLOAD_PATH', __DIR__ . '/../uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024);
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif']);

// M-Pesa Configuration
define('MPESA_CONSUMER_KEY', 'your_consumer_key_here');
define('MPESA_CONSUMER_SECRET', 'your_consumer_secret_here');
define('MPESA_SHORTCODE', 'your_business_shortcode');
define('MPESA_PASSKEY', 'your_passkey_here');
define('MPESA_ENVIRONMENT', 'sandbox'); // or 'production'
define('MPESA_CALLBACK_URL', BASE_URL . '/callback/mpesa_callback.php');

// Email Configuration (PHPMailer)
define('MAIL_HOST', 'smtp.gmail.com'); // SMTP server
define('MAIL_PORT', 587); // SMTP port (587 for TLS, 465 for SSL)
define('MAIL_USERNAME', 'your_email@gmail.com'); // SMTP username
define('MAIL_PASSWORD', 'your_app_password'); // SMTP password or app password
define('MAIL_FROM_EMAIL', 'noreply@tilapiadepot.com'); // Sender email
define('MAIL_FROM_NAME', 'Lake Victoria Tilapia Depot'); // Sender name
define('MAIL_ENCRYPTION', 'tls'); // Encryption type (tls or ssl)

// SMS Configuration (Africa's Talking API)
define('SMS_API_KEY', 'your_africastalking_api_key');
define('SMS_USERNAME', 'sandbox'); // Use 'sandbox' for testing or your username for production
define('SMS_SHORTCODE', 'TILAPIA'); // Your SMS shortcode/sender ID
define('SMS_ENVIRONMENT', 'sandbox'); // 'sandbox' or 'production'

// Admin Notification Settings
define('ADMIN_EMAIL', 'admin@tilapiadepot.com');
define('ADMIN_PHONE', '+254700000000');

// Notification Settings
define('ENABLE_EMAIL_NOTIFICATIONS', true);
define('ENABLE_SMS_NOTIFICATIONS', true);

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Create uploads directory if it doesn't exist
if (!file_exists(UPLOAD_PATH)) {
    mkdir(UPLOAD_PATH, 0777, true);
}
