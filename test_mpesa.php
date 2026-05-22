<?php

/**
 * M-Pesa Configuration Verification Script
 * Run this script to verify your M-Pesa configuration is correct
 * Access via: http://localhost/lake-victoria-tilapia-depot/test_mpesa.php
 */

// Prevent direct access in production
if (getenv('MPESA_ENVIRONMENT') === 'production' && !isset($_GET['verify_key'])) {
    die('This script should not be accessed in production environments.');
}

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/controllers/MpesaController.php';
require_once __DIR__ . '/controllers/MpesaHelper.php';

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>M-Pesa Configuration Verification</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            max-width: 900px;
            width: 100%;
            padding: 40px;
        }

        h1 {
            color: #333;
            margin-bottom: 30px;
            text-align: center;
        }

        .check-section {
            margin-bottom: 25px;
            border-left: 4px solid #f0f0f0;
            padding-left: 20px;
        }

        .check-title {
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
            font-size: 16px;
        }

        .check-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 12px;
            padding: 10px;
            background: #f9f9f9;
            border-radius: 5px;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            margin-right: 12px;
            flex-shrink: 0;
            font-weight: bold;
            color: white;
            font-size: 14px;
        }

        .status-badge.success {
            background: #10b981;
        }

        .status-badge.warning {
            background: #f59e0b;
        }

        .status-badge.error {
            background: #ef4444;
        }

        .check-content {
            flex: 1;
        }

        .check-label {
            font-weight: 500;
            color: #333;
            margin-bottom: 3px;
        }

        .check-value {
            color: #666;
            font-size: 13px;
            font-family: monospace;
            word-break: break-all;
        }

        .test-section {
            margin-top: 30px;
            padding-top: 30px;
            border-top: 2px solid #e5e7eb;
        }

        .test-input-group {
            margin-bottom: 15px;
        }

        .test-input-group label {
            display: block;
            font-weight: 500;
            margin-bottom: 5px;
            color: #333;
        }

        .test-input-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }

        .test-button {
            background: #667eea;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            margin-top: 20px;
        }

        .test-button:hover {
            background: #5568d3;
        }

        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
        }

        .alert.info {
            background: #e0e7ff;
            border: 1px solid #c7d2fe;
            color: #3730a3;
        }

        .alert.warning {
            background: #fef3c7;
            border: 1px solid #fde68a;
            color: #92400e;
        }

        .alert.error {
            background: #fee2e2;
            border: 1px solid #fecaca;
            color: #991b1b;
        }

        .alert.success {
            background: #d1fae5;
            border: 1px solid #a7f3d0;
            color: #065f46;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>🔍 M-Pesa Configuration Verification</h1>

        <div class="check-section">
            <div class="check-title">Configuration Status</div>

            <!-- Consumer Key Check -->
            <div class="check-item">
                <div class="status-badge <?php echo (MPESA_CONSUMER_KEY !== 'your_consumer_key_here' && !empty(MPESA_CONSUMER_KEY)) ? 'success' : 'error'; ?>">
                    <?php echo (MPESA_CONSUMER_KEY !== 'your_consumer_key_here' && !empty(MPESA_CONSUMER_KEY)) ? '✓' : '✗'; ?>
                </div>
                <div class="check-content">
                    <div class="check-label">Consumer Key</div>
                    <div class="check-value">
                        <?php
                        if (MPESA_CONSUMER_KEY === 'your_consumer_key_here') {
                            echo '<span style="color: #ef4444;">NOT CONFIGURED</span>';
                        } else {
                            echo '*** (configured - length: ' . strlen(MPESA_CONSUMER_KEY) . ' chars)';
                        }
                        ?>
                    </div>
                </div>
            </div>

            <!-- Consumer Secret Check -->
            <div class="check-item">
                <div class="status-badge <?php echo (MPESA_CONSUMER_SECRET !== 'your_consumer_secret_here' && !empty(MPESA_CONSUMER_SECRET)) ? 'success' : 'error'; ?>">
                    <?php echo (MPESA_CONSUMER_SECRET !== 'your_consumer_secret_here' && !empty(MPESA_CONSUMER_SECRET)) ? '✓' : '✗'; ?>
                </div>
                <div class="check-content">
                    <div class="check-label">Consumer Secret</div>
                    <div class="check-value">
                        <?php
                        if (MPESA_CONSUMER_SECRET === 'your_consumer_secret_here') {
                            echo '<span style="color: #ef4444;">NOT CONFIGURED</span>';
                        } else {
                            echo '*** (configured - length: ' . strlen(MPESA_CONSUMER_SECRET) . ' chars)';
                        }
                        ?>
                    </div>
                </div>
            </div>

            <!-- Passkey Check -->
            <div class="check-item">
                <div class="status-badge <?php echo (MPESA_PASSKEY !== 'your_passkey_here' && !empty(MPESA_PASSKEY)) ? 'success' : 'error'; ?>">
                    <?php echo (MPESA_PASSKEY !== 'your_passkey_here' && !empty(MPESA_PASSKEY)) ? '✓' : '✗'; ?>
                </div>
                <div class="check-content">
                    <div class="check-label">Lipa Na M-Pesa Passkey</div>
                    <div class="check-value">
                        <?php
                        if (MPESA_PASSKEY === 'your_passkey_here') {
                            echo '<span style="color: #ef4444;">NOT CONFIGURED</span>';
                        } else {
                            echo '*** (configured - length: ' . strlen(MPESA_PASSKEY) . ' chars)';
                        }
                        ?>
                    </div>
                </div>
            </div>

            <!-- Shortcode Check -->
            <div class="check-item">
                <div class="status-badge success">✓</div>
                <div class="check-content">
                    <div class="check-label">Business Shortcode</div>
                    <div class="check-value"><?php echo MPESA_SHORTCODE; ?></div>
                </div>
            </div>

            <!-- Environment Check -->
            <div class="check-item">
                <div class="status-badge success">✓</div>
                <div class="check-content">
                    <div class="check-label">Environment</div>
                    <div class="check-value"><?php echo MPESA_ENVIRONMENT; ?></div>
                </div>
            </div>

            <!-- Callback URL Check -->
            <div class="check-item">
                <div class="status-badge success">✓</div>
                <div class="check-content">
                    <div class="check-label">Callback URL</div>
                    <div class="check-value"><?php echo MPESA_CALLBACK_URL; ?></div>
                </div>
            </div>
        </div>

        <!-- Database Check -->
        <div class="check-section">
            <div class="check-title">Database Connection</div>
            <div class="check-item">
                <div class="status-badge <?php
                                            try {
                                                $db = new Database();
                                                $conn = $db->getConnection();
                                                echo $conn ? 'success' : 'error';
                                            } catch (Exception $e) {
                                                echo 'error';
                                            }
                                            ?>">
                    <?php
                    try {
                        $db = new Database();
                        $conn = $db->getConnection();
                        echo $conn ? '✓' : '✗';
                    } catch (Exception $e) {
                        echo '✗';
                    }
                    ?>
                </div>
                <div class="check-content">
                    <div class="check-label">Database Status</div>
                    <div class="check-value">
                        <?php
                        try {
                            $db = new Database();
                            $conn = $db->getConnection();
                            echo $conn ? 'Connected' : 'Failed to connect';
                        } catch (Exception $e) {
                            echo 'Error: ' . $e->getMessage();
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- PHP Extension Checks -->
        <div class="check-section">
            <div class="check-title">Required PHP Extensions</div>
            <div class="check-item">
                <div class="status-badge <?php echo extension_loaded('curl') ? 'success' : 'error'; ?>">
                    <?php echo extension_loaded('curl') ? '✓' : '✗'; ?>
                </div>
                <div class="check-content">
                    <div class="check-label">cURL Extension</div>
                    <div class="check-value"><?php echo extension_loaded('curl') ? 'Enabled' : 'Disabled'; ?></div>
                </div>
            </div>

            <div class="check-item">
                <div class="status-badge <?php echo extension_loaded('json') ? 'success' : 'error'; ?>">
                    <?php echo extension_loaded('json') ? '✓' : '✗'; ?>
                </div>
                <div class="check-content">
                    <div class="check-label">JSON Extension</div>
                    <div class="check-value"><?php echo extension_loaded('json') ? 'Enabled' : 'Disabled'; ?></div>
                </div>
            </div>

            <div class="check-item">
                <div class="status-badge <?php echo extension_loaded('pdo') ? 'success' : 'error'; ?>">
                    <?php echo extension_loaded('pdo') ? '✓' : '✗'; ?>
                </div>
                <div class="check-content">
                    <div class="check-label">PDO Extension</div>
                    <div class="check-value"><?php echo extension_loaded('pdo') ? 'Enabled' : 'Disabled'; ?></div>
                </div>
            </div>
        </div>

        <!-- Warnings -->
        <?php if (MPESA_CONSUMER_KEY === 'your_consumer_key_here' || MPESA_CONSUMER_SECRET === 'your_consumer_secret_here' || MPESA_PASSKEY === 'your_passkey_here') : ?>
            <div class="alert warning">
                <strong>⚠️ Configuration Required!</strong>
                <p>Your M-Pesa credentials are not fully configured. Payment will not work until you:</p>
                <ol style="margin-top: 10px; margin-left: 20px;">
                    <li>Get your M-Pesa credentials from <a href="https://developer.safaricom.co.ke/" target="_blank">https://developer.safaricom.co.ke/</a></li>
                    <li>Update the values in <code>config/config.php</code> or set environment variables</li>
                    <li>See <strong>MPESA_SETUP.md</strong> for detailed instructions</li>
                </ol>
            </div>
        <?php else : ?>
            <div class="alert success">
                <strong>✓ Configuration Complete!</strong>
                <p>All M-Pesa credentials are configured. You can now test payments.</p>
            </div>
        <?php endif; ?>

        <!-- Test Phone Number and Amount -->
        <div class="test-section">
            <h3>Test Validation</h3>
            <p style="color: #666; margin-bottom: 15px;">Test phone number and amount validation</p>

            <?php
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $phone = trim($_POST['phone'] ?? '');
                $amount = trim($_POST['amount'] ?? '');

                echo '<div class="alert info" style="margin-bottom: 20px;">';
                echo '<strong>Test Results:</strong><br>';

                if (!empty($phone)) {
                    $formatted = MpesaHelper::formatPhoneNumber($phone);
                    if ($formatted) {
                        echo '✓ <strong>Phone:</strong> ' . $phone . ' → ' . $formatted . '<br>';
                    } else {
                        echo '✗ <strong>Phone:</strong> ' . $phone . ' is invalid<br>';
                    }
                }

                if (!empty($amount)) {
                    $valid = MpesaHelper::isValidAmount($amount);
                    if ($valid) {
                        echo '✓ <strong>Amount:</strong> KSh ' . number_format((float)$amount, 2) . ' is valid<br>';
                    } else {
                        echo '✗ <strong>Amount:</strong> ' . $amount . ' is invalid (must be 1-150000)<br>';
                    }
                }

                echo '</div>';
            }
            ?>

            <form method="POST">
                <div class="test-input-group">
                    <label for="phone">Phone Number:</label>
                    <input type="text" id="phone" name="phone" placeholder="e.g., 0712345678 or 254712345678">
                </div>
                <div class="test-input-group">
                    <label for="amount">Amount (KSh):</label>
                    <input type="text" id="amount" name="amount" placeholder="e.g., 100">
                </div>
                <button type="submit" class="test-button">Test Validation</button>
            </form>
        </div>

        <div class="alert info" style="margin-top: 30px;">
            <strong>ℹ️ Next Steps:</strong>
            <ol style="margin-top: 10px; margin-left: 20px;">
                <li>Ensure all required fields show ✓</li>
                <li>Read <strong>MPESA_SETUP.md</strong> for detailed configuration instructions</li>
                <li>Test a payment with a valid phone number and amount</li>
                <li>Check error logs if payment fails</li>
            </ol>
        </div>
    </div>
</body>

</html>