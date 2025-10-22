<?php
require_once __DIR__ . '/../config/config.php';

/**
 * NotificationService
 * Handles email and SMS notifications for the application
 */
class NotificationService
{
    /**
     * Send email notification
     * 
     * @param string $to Recipient email address
     * @param string $subject Email subject
     * @param string $message Email body (HTML)
     * @param string $recipientName Recipient's name
     * @return bool Success status
     */
    public static function sendEmail($to, $subject, $message, $recipientName = '')
    {
        if (!ENABLE_EMAIL_NOTIFICATIONS) {
            error_log("Email notifications are disabled");
            return false;
        }

        try {
            // Check if PHPMailer is available
            if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
                error_log("PHPMailer not installed. Run: composer require phpmailer/phpmailer");
                // Fallback to PHP mail() function
                return self::sendSimpleEmail($to, $subject, $message);
            }

            $mail = new PHPMailer\PHPMailer\PHPMailer(true);

            // Server settings
            $mail->isSMTP();
            $mail->Host = MAIL_HOST;
            $mail->SMTPAuth = true;
            $mail->Username = MAIL_USERNAME;
            $mail->Password = MAIL_PASSWORD;
            $mail->SMTPSecure = MAIL_ENCRYPTION;
            $mail->Port = MAIL_PORT;

            // Recipients
            $mail->setFrom(MAIL_FROM_EMAIL, MAIL_FROM_NAME);
            $mail->addAddress($to, $recipientName);

            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $message;
            $mail->AltBody = strip_tags($message);

            $mail->send();
            error_log("Email sent successfully to: $to");
            return true;
        } catch (Exception $e) {
            error_log("Email error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send simple email using PHP mail() function (fallback)
     */
    private static function sendSimpleEmail($to, $subject, $message)
    {
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: " . MAIL_FROM_NAME . " <" . MAIL_FROM_EMAIL . ">" . "\r\n";

        return mail($to, $subject, $message, $headers);
    }

    /**
     * Send SMS notification via Africa's Talking API
     * 
     * @param string $phone Phone number with country code (e.g., +254712345678)
     * @param string $message SMS message content
     * @return bool Success status
     */
    public static function sendSMS($phone, $message)
    {
        if (!ENABLE_SMS_NOTIFICATIONS) {
            error_log("SMS notifications are disabled");
            return false;
        }

        try {
            // Clean phone number
            $phone = self::formatPhoneNumber($phone);

            $url = SMS_ENVIRONMENT === 'sandbox'
                ? 'https://api.sandbox.africastalking.com/version1/messaging'
                : 'https://api.africastalking.com/version1/messaging';

            $data = [
                'username' => SMS_USERNAME,
                'to' => $phone,
                'message' => $message,
                'from' => SMS_SHORTCODE
            ];

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'apiKey: ' . SMS_API_KEY,
                'Content-Type: application/x-www-form-urlencoded',
                'Accept: application/json'
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode == 201 || $httpCode == 200) {
                error_log("SMS sent successfully to: $phone");
                return true;
            } else {
                error_log("SMS sending failed. HTTP Code: $httpCode, Response: $response");
                return false;
            }
        } catch (Exception $e) {
            error_log("SMS error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Format phone number to international format
     */
    private static function formatPhoneNumber($phone)
    {
        // Remove spaces, dashes, and parentheses
        $phone = preg_replace('/[\s\-\(\)]/', '', $phone);

        // If starts with 0, replace with +254 (Kenya)
        if (substr($phone, 0, 1) === '0') {
            $phone = '+254' . substr($phone, 1);
        }

        // If doesn't start with +, add +254
        if (substr($phone, 0, 1) !== '+') {
            $phone = '+254' . $phone;
        }

        return $phone;
    }

    /**
     * Send order confirmation notification
     */
    public static function sendOrderConfirmation($order, $customer)
    {
        $subject = "Order Confirmation #" . $order['id'];
        $emailMessage = self::getOrderEmailTemplate($order, $customer, 'confirmation');
        $smsMessage = "Your order #" . $order['id'] . " has been received. Total: KSh " . number_format($order['total_amount'], 2) . ". Thank you for shopping with us!";

        self::sendEmail($customer['email'], $subject, $emailMessage, $customer['full_name']);
        self::sendSMS($customer['phone'], $smsMessage);
    }

    /**
     * Send order cancellation notification
     */
    public static function sendOrderCancellation($order, $customer)
    {
        $subject = "Order Cancelled #" . $order['id'];
        $emailMessage = self::getOrderEmailTemplate($order, $customer, 'cancellation');
        $smsMessage = "Your order #" . $order['id'] . " has been cancelled. For any queries, contact us.";

        self::sendEmail($customer['email'], $subject, $emailMessage, $customer['full_name']);
        self::sendSMS($customer['phone'], $smsMessage);
    }

    /**
     * Send new order notification to admin
     */
    public static function notifyAdminNewOrder($order, $customer)
    {
        $subject = "New Order Received #" . $order['id'];
        $emailMessage = self::getAdminOrderEmailTemplate($order, $customer);
        $smsMessage = "New order #" . $order['id'] . " from " . $customer['full_name'] . ". Amount: KSh " . number_format($order['total_amount'], 2);

        self::sendEmail(ADMIN_EMAIL, $subject, $emailMessage, 'Admin');
        self::sendSMS(ADMIN_PHONE, $smsMessage);
    }

    /**
     * Get order email template
     */
    private static function getOrderEmailTemplate($order, $customer, $type)
    {
        $siteName = SITE_NAME;
        $baseUrl = BASE_URL;

        if ($type === 'confirmation') {
            $title = "Order Confirmed!";
            $message = "Thank you for your order. We have received your order and will process it shortly.";
            $color = "#10b981"; // green
        } else {
            $title = "Order Cancelled";
            $message = "Your order has been cancelled as requested.";
            $color = "#ef4444"; // red
        }

        $html = "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: $color; color: white; padding: 20px; text-align: center; }
                .content { background: #f9fafb; padding: 20px; }
                .order-details { background: white; padding: 15px; margin: 15px 0; border-radius: 5px; }
                .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>$title</h1>
                </div>
                <div class='content'>
                    <p>Hello {$customer['full_name']},</p>
                    <p>$message</p>
                    <div class='order-details'>
                        <h3>Order Details</h3>
                        <p><strong>Order ID:</strong> #{$order['id']}</p>
                        <p><strong>Total Amount:</strong> KSh " . number_format($order['total_amount'], 2) . "</p>
                        <p><strong>Status:</strong> " . ucfirst($order['status']) . "</p>
                        <p><strong>Payment Status:</strong> " . ucfirst($order['payment_status']) . "</p>
                        <p><strong>Shipping Address:</strong> {$order['shipping_address']}</p>
                    </div>
                    <p>You can track your order status by visiting your orders page.</p>
                    <p><a href='$baseUrl/views/customer/my_orders.php' style='background: #3b82f6; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;'>View My Orders</a></p>
                </div>
                <div class='footer'>
                    <p>&copy; " . date('Y') . " $siteName. All rights reserved.</p>
                    <p>Contact us: " . ADMIN_EMAIL . " | " . ADMIN_PHONE . "</p>
                </div>
            </div>
        </body>
        </html>";

        return $html;
    }

    /**
     * Get admin order email template
     */
    private static function getAdminOrderEmailTemplate($order, $customer)
    {
        $siteName = SITE_NAME;
        $baseUrl = BASE_URL;

        $html = "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #3b82f6; color: white; padding: 20px; text-align: center; }
                .content { background: #f9fafb; padding: 20px; }
                .order-details { background: white; padding: 15px; margin: 15px 0; border-radius: 5px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>New Order Received</h1>
                </div>
                <div class='content'>
                    <p>A new order has been placed on $siteName.</p>
                    <div class='order-details'>
                        <h3>Order Information</h3>
                        <p><strong>Order ID:</strong> #{$order['id']}</p>
                        <p><strong>Customer:</strong> {$customer['full_name']}</p>
                        <p><strong>Email:</strong> {$customer['email']}</p>
                        <p><strong>Phone:</strong> {$customer['phone']}</p>
                        <p><strong>Total Amount:</strong> KSh " . number_format($order['total_amount'], 2) . "</p>
                        <p><strong>Payment Status:</strong> " . ucfirst($order['payment_status']) . "</p>
                        <p><strong>Shipping Address:</strong> {$order['shipping_address']}</p>
                    </div>
                    <p><a href='$baseUrl/views/staff/order_details.php?id={$order['id']}' style='background: #3b82f6; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;'>View Order Details</a></p>
                </div>
            </div>
        </body>
        </html>";

        return $html;
    }
}
