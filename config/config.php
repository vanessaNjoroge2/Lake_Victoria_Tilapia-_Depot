<?php
// Set timezone - CRITICAL for link expiry
date_default_timezone_set('Africa/Nairobi');

// Base configuration - UPDATED FOR XAMPP3
define('BASE_URL', 'http://localhost/lake-victoria-tilapia-depot');
define('SITE_NAME', 'Lake Victoria Tilapia Depot');

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'lake_victoria_tilapia_depot');
define('DB_USER', 'root');
define('DB_PASS', 'medicheck123'); // Updated with correct password found in system config

// File upload configuration
define('UPLOAD_PATH', __DIR__ . '/../uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024);
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif']);

// M-Pesa Configuration
// NOTE: Get these from https://developer.safaricom.co.ke/
// For sandbox testing, use sandbox credentials
// For production, use your actual M-Pesa credentials
define('MPESA_CONSUMER_KEY', getenv('MPESA_CONSUMER_KEY') ?: 'your_consumer_key_here');
define('MPESA_CONSUMER_SECRET', getenv('MPESA_CONSUMER_SECRET') ?: 'your_consumer_secret_here');
define('MPESA_SHORTCODE', getenv('MPESA_SHORTCODE') ?: '174379');
define('MPESA_PASSKEY', getenv('MPESA_PASSKEY') ?: 'bfb279f9aa9bdbcf158e97dd1a503b6e'); // Sandbox default passkey
define('MPESA_ENVIRONMENT', getenv('MPESA_ENVIRONMENT') ?: 'sandbox'); // or 'production'
define('MPESA_CALLBACK_URL', BASE_URL . '/callback/mpesa_callback.php');

// Email Configuration (PHPMailer)
define('MAIL_HOST', 'smtp.gmail.com'); // SMTP server
define('MAIL_PORT', 465); // SMTP port (587 for TLS, 465 for SSL)
define('MAIL_USERNAME', 'vanessawanjiru2023@gmail.com'); // The email you generated the app password for
define('MAIL_PASSWORD', 'bccxaxqgifijgabc'); // Your Gmail app password
define('MAIL_FROM_EMAIL', 'vanessawanjiru2023@gmail.com'); // Gmail usually requires this to match your username
define('MAIL_FROM_NAME', 'Lake Victoria Tilapia Depot'); // Sender name
define('MAIL_ENCRYPTION', 'ssl'); // Encryption type (tls or ssl)

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
