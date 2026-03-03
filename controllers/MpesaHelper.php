<?php
/**
 * M-Pesa Utility Helper Class
 * Provides utility functions for M-Pesa integration
 */

class MpesaHelper
{
    /**
     * Format phone number to M-Pesa standard (254XXXXXXXXX)
     * 
     * @param string $phone Phone number in various formats
     * @return string|null Formatted phone number or null if invalid
     */
    public static function formatPhoneNumber($phone)
    {
        if (empty($phone)) {
            return null;
        }

        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Convert local format (0712345678) to international (254712345678)
        if (strlen($phone) === 10 && substr($phone, 0, 1) === '0') {
            $phone = '254' . substr($phone, 1);
        }

        // Remove + if present at the start
        if (strlen($phone) === 13 && substr($phone, 0, 1) === '+') {
            $phone = substr($phone, 1);
        }

        // Validate final format (should be 12 digits starting with 254)
        if (strlen($phone) === 12 && substr($phone, 0, 3) === '254') {
            return $phone;
        }

        return null;
    }

    /**
     * Validate M-Pesa phone number
     * 
     * @param string $phone Phone number to validate
     * @return bool True if valid, false otherwise
     */
    public static function isValidPhone($phone)
    {
        $formatted = self::formatPhoneNumber($phone);
        return $formatted !== null;
    }

    /**
     * Validate M-Pesa amount
     * 
     * @param mixed $amount Amount to validate
     * @return bool True if valid, false otherwise
     */
    public static function isValidAmount($amount)
    {
        // Convert to float
        $amount = floatval($amount);

        // Must be at least 1 KSH
        if ($amount < 1) {
            return false;
        }

        // Maximum amount (adjust as needed)
        // Safaricom typically allows up to 150,000 KSH
        if ($amount > 150000) {
            return false;
        }

        return true;
    }

    /**
     * Generate timestamp for M-Pesa API
     * 
     * @return string Timestamp in format YYYYMMDDHHmmss
     */
    public static function generateTimestamp()
    {
        return date('YmdHis');
    }

    /**
     * Generate password for STK Push
     * 
     * @param string $shortcode Business shortcode
     * @param string $passkey Lipa Na M-Pesa passkey
     * @param string $timestamp Timestamp
     * @return string Base64 encoded password
     */
    public static function generatePassword($shortcode, $passkey, $timestamp)
    {
        return base64_encode($shortcode . $passkey . $timestamp);
    }

    /**
     * Format M-Pesa receipt number for display
     * 
     * @param string $receipt Receipt number
     * @return string Formatted receipt
     */
    public static function formatReceipt($receipt)
    {
        if (empty($receipt)) {
            return 'N/A';
        }
        return strtoupper($receipt);
    }

    /**
     * Parse M-Pesa transaction date
     * 
     * @param string $mpesaDate Date from M-Pesa (format: 20211231235959)
     * @return string Formatted date
     */
    public static function parseTransactionDate($mpesaDate)
    {
        if (empty($mpesaDate) || strlen($mpesaDate) < 14) {
            return date('Y-m-d H:i:s');
        }

        // Parse YYYYMMDDHHMMSS format
        $year = substr($mpesaDate, 0, 4);
        $month = substr($mpesaDate, 4, 2);
        $day = substr($mpesaDate, 6, 2);
        $hour = substr($mpesaDate, 8, 2);
        $minute = substr($mpesaDate, 10, 2);
        $second = substr($mpesaDate, 12, 2);

        return "$year-$month-$day $hour:$minute:$second";
    }

    /**
     * Get user-friendly payment status
     * 
     * @param string $status Payment status from database
     * @return array Status with label and color
     */
    public static function getPaymentStatusLabel($status)
    {
        $statuses = [
            'pending' => ['label' => 'Pending', 'color' => 'warning', 'icon' => '⏳'],
            'paid' => ['label' => 'Paid', 'color' => 'success', 'icon' => '✓'],
            'failed' => ['label' => 'Failed', 'color' => 'danger', 'icon' => '✗'],
            'cancelled' => ['label' => 'Cancelled', 'color' => 'secondary', 'icon' => '⊗']
        ];

        return $statuses[$status] ?? [
            'label' => ucfirst($status),
            'color' => 'secondary',
            'icon' => '•'
        ];
    }

    /**
     * Sanitize callback data
     * 
     * @param array $data Callback data from M-Pesa
     * @return array Sanitized data
     */
    public static function sanitizeCallbackData($data)
    {
        if (!is_array($data)) {
            return [];
        }

        // Recursively sanitize array
        array_walk_recursive($data, function(&$value) {
            if (is_string($value)) {
                $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
            }
        });

        return $data;
    }

    /**
     * Check if environment is production
     * 
     * @return bool True if production, false if sandbox
     */
    public static function isProduction()
    {
        return defined('MPESA_ENVIRONMENT') && MPESA_ENVIRONMENT === 'production';
    }

    /**
     * Get API base URL based on environment
     * 
     * @return string Base URL for M-Pesa API
     */
    public static function getApiBaseUrl()
    {
        return self::isProduction()
            ? 'https://api.safaricom.co.ke'
            : 'https://sandbox.safaricom.co.ke';
    }

    /**
     * Validate M-Pesa configuration
     * 
     * @return array Validation result with errors
     */
    public static function validateConfiguration()
    {
        $errors = [];

        if (!defined('MPESA_CONSUMER_KEY') || MPESA_CONSUMER_KEY === 'your_consumer_key_here') {
            $errors[] = 'Consumer Key not configured';
        }

        if (!defined('MPESA_CONSUMER_SECRET') || MPESA_CONSUMER_SECRET === 'your_consumer_secret_here') {
            $errors[] = 'Consumer Secret not configured';
        }

        if (!defined('MPESA_SHORTCODE') || MPESA_SHORTCODE === 'your_business_shortcode') {
            $errors[] = 'Shortcode not configured';
        }

        if (!defined('MPESA_PASSKEY') || MPESA_PASSKEY === 'your_passkey_here') {
            $errors[] = 'Passkey not configured';
        }

        if (!defined('MPESA_CALLBACK_URL')) {
            $errors[] = 'Callback URL not configured';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Log M-Pesa transaction for debugging
     * 
     * @param string $type Type of transaction (request/callback)
     * @param mixed $data Data to log
     * @param string $orderId Order ID
     */
    public static function logTransaction($type, $data, $orderId = null)
    {
        $logFile = __DIR__ . '/../logs/mpesa_' . date('Y-m-d') . '.log';
        
        // Create logs directory if it doesn't exist
        $logDir = dirname($logFile);
        if (!file_exists($logDir)) {
            mkdir($logDir, 0777, true);
        }

        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'type' => $type,
            'order_id' => $orderId,
            'data' => $data
        ];

        $logMessage = json_encode($logEntry, JSON_PRETTY_PRINT) . "\n\n";
        file_put_contents($logFile, $logMessage, FILE_APPEND);
    }

    /**
     * Format amount for display
     * 
     * @param float $amount Amount to format
     * @return string Formatted amount with currency
     */
    public static function formatAmount($amount)
    {
        return 'KSH ' . number_format($amount, 2);
    }

    /**
     * Generate account reference for STK Push
     * 
     * @param int $orderId Order ID
     * @return string Account reference
     */
    public static function generateAccountReference($orderId)
    {
        return 'ORD-' . str_pad($orderId, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Get error message for M-Pesa response code
     * 
     * @param string $responseCode Response code from M-Pesa
     * @return string User-friendly error message
     */
    public static function getErrorMessage($responseCode)
    {
        $errors = [
            '0' => 'Success',
            '1' => 'Insufficient funds in your M-Pesa account',
            '2' => 'Less than minimum transaction value',
            '3' => 'More than maximum transaction value',
            '4' => 'Would exceed daily transfer limit',
            '5' => 'Would exceed minimum balance',
            '6' => 'Unresolved primary party',
            '7' => 'Unresolved receiver party',
            '8' => 'Would exceed maximum balance',
            '11' => 'Debit account invalid',
            '12' => 'Credit account invalid',
            '13' => 'Unresolved debit account',
            '14' => 'Unresolved credit account',
            '15' => 'Duplicate detected',
            '17' => 'Internal failure',
            '20' => 'Unresolved initiator',
            '26' => 'Traffic blocking condition in place',
            '1032' => 'Request cancelled by user',
            '1037' => 'Timeout - DS request timed out',
            '2001' => 'Invalid initiator information',
            '9999' => 'Request processing failed'
        ];

        return $errors[$responseCode] ?? 'Unknown error occurred';
    }
}
