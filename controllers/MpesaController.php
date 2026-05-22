<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Order.php';

class MpesaController
{
    private ?\PDO $db;
    private ?Order $order;
    private string $consumerKey;
    private string $consumerSecret;
    private string $shortcode;
    private string $passkey;
    private string $callbackUrl;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->order = new Order($this->db);

        // M-Pesa configuration from config.php
        $this->consumerKey = MPESA_CONSUMER_KEY;
        $this->consumerSecret = MPESA_CONSUMER_SECRET;
        $this->shortcode = MPESA_SHORTCODE;
        $this->passkey = MPESA_PASSKEY;
        $this->callbackUrl = MPESA_CALLBACK_URL;
    }

    // Generate access token
    private function getAccessToken(): ?string
    {
        // Validate credentials are configured
        if (
            $this->consumerKey === 'your_consumer_key_here' ||
            $this->consumerSecret === 'your_consumer_secret_here'
        ) {
            error_log("MPESA Config Error: Consumer key/secret not configured. Update config.php or set environment variables.");
            return null;
        }

        // Validate passkey is configured (for sandbox it's the default)
        if (
            $this->passkey === 'your_passkey_here' ||
            empty($this->passkey)
        ) {
            error_log("MPESA Config Error: Passkey not configured. Update config.php or set MPESA_PASSKEY environment variable.");
            return null;
        }

        $url = (MPESA_ENVIRONMENT === 'production')
            ? 'https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials'
            : 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        $credentials = base64_encode($this->consumerKey . ':' . $this->consumerSecret);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Authorization: Basic ' . $credentials));
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        $curl_response = curl_exec($curl);

        if (curl_errno($curl)) {
            error_log("MPESA Token cURL Error: " . curl_error($curl));
            return null;
        }
        $response = json_decode($curl_response);

        // Log detailed error if token retrieval fails
        if (!isset($response->access_token)) {
            error_log("MPESA Token Response Error: " . $curl_response);
            error_log("MPESA Token Response decoded: " . print_r($response, true));
        }

        return $response->access_token ?? null;
    }

    // Initiate STK Push
    public function initiateSTKPush(string $phone, int|float $amount, int $orderId, string $description = "Fish Order Payment"): array
    {
        try {
            // Validate phone number
            if (empty($phone)) {
                throw new Exception("Phone number is required for payment.");
            }

            // Format and validate phone number
            require_once __DIR__ . '/MpesaHelper.php';
            $formatted_phone = MpesaHelper::formatPhoneNumber($phone);
            if (!$formatted_phone) {
                throw new Exception("Invalid phone number format. Please use format: 0712345678 or 254712345678");
            }
            $phone = $formatted_phone;

            // Validate amount
            require_once __DIR__ . '/MpesaHelper.php';
            if (!MpesaHelper::isValidAmount($amount)) {
                throw new Exception("Invalid amount. Amount must be between 1 and 150000 KSH.");
            }

            $access_token = $this->getAccessToken();

            if (!$access_token) {
                throw new Exception("Failed to get access token. Please check M-Pesa configuration in config.php and ensure credentials are properly set.");
            }

            $timestamp = date('YmdHis');
            $password = base64_encode($this->shortcode . $this->passkey . $timestamp);

            $curl_post_data = [
                'BusinessShortCode' => $this->shortcode,
                'Password' => $password,
                'Timestamp' => $timestamp,
                'TransactionType' => 'CustomerPayBillOnline',
                'Amount' => $amount,
                'PartyA' => $phone,
                'PartyB' => $this->shortcode,
                'PhoneNumber' => $phone,
                'CallBackURL' => $this->callbackUrl,
                'AccountReference' => 'Order-' . $orderId,
                'TransactionDesc' => $description
            ];

            $url = (MPESA_ENVIRONMENT === 'production')
                ? 'https://api.safaricom.co.ke/mpesa/stkpush/v1/processrequest'
                : 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest';

            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $access_token,
                'Content-Type: application/json'
            ]);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($curl_post_data));
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_TIMEOUT, 30);

            $curl_response = curl_exec($curl);
            $curl_info = curl_getinfo($curl);
            $curl_errno = curl_errno($curl);
            $curl_error = curl_error($curl);
            curl_close($curl);

            if ($curl_errno) {
                $error = $curl_error;
                error_log("MPESA STK Push cURL Error [" . $curl_errno . "]: " . $error);
                error_log("MPESA Request Details - Phone: {$phone}, Amount: {$amount}, OrderID: {$orderId}");
                throw new Exception("Network error during payment request: " . $error);
            }

            error_log("MPESA STK Push HTTP Response Code: " . $curl_info['http_code']);
            error_log("MPESA STK Push Response Body: " . $curl_response);

            $response = json_decode($curl_response, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                error_log("MPESA STK Push JSON Decode Error: " . json_last_error_msg());
                error_log("Raw Response: " . $curl_response);
                throw new Exception("Invalid response from payment service. Please try again.");
            }

            // Log the request
            $this->logPaymentRequest($orderId, $phone, $amount, $response);

            // Check for success response
            if (isset($response['ResponseCode']) && $response['ResponseCode'] == '0') {
                error_log("MPESA STK Push initiated successfully for Order #{$orderId}, CheckoutRequestID: " . ($response['CheckoutRequestID'] ?? 'N/A'));
            } else {
                $errorMessage = $response['errorMessage'] ?? $response['ResponseDescription'] ?? json_encode($response);
                error_log("MPESA STK Push failed for Order #{$orderId}: " . $errorMessage);
            }

            return $response;
        } catch (Exception $e) {
            error_log("M-Pesa STK Push Error: " . $e->getMessage());
            return ['error' => $e->getMessage(), 'ResponseCode' => '1'];
        }
    }

    // Handle M-Pesa callback
    public function handleCallback(string $callback_data): array
    {
        try {
            $data = json_decode($callback_data, true);

            if (!isset($data['Body']['stkCallback'])) {
                throw new Exception("Invalid callback data");
            }

            $callback = $data['Body']['stkCallback'];
            $merchant_request_id = $callback['MerchantRequestID'];
            $checkout_request_id = $callback['CheckoutRequestID'];
            $result_code = $callback['ResultCode'];
            $result_desc = $callback['ResultDesc'];

            // Find order by checkout request ID
            $order_id = $this->findOrderByCheckoutId($checkout_request_id);

            if ($order_id) {
                if ($result_code == 0) {
                    // Payment successful
                    $callback_metadata = $callback['CallbackMetadata']['Item'];

                    $amount = 0;
                    $mpesa_receipt = '';
                    $phone = '';
                    $transaction_date = '';

                    foreach ($callback_metadata as $item) {
                        switch ($item['Name']) {
                            case 'Amount':
                                $amount = $item['Value'];
                                break;
                            case 'MpesaReceiptNumber':
                                $mpesa_receipt = $item['Value'];
                                break;
                            case 'PhoneNumber':
                                $phone = $item['Value'];
                                break;
                            case 'TransactionDate':
                                $transaction_date = $item['Value'];
                                break;
                        }
                    }

                    // Update order payment status
                    $this->updateSuccessfulPayment($order_id, $amount, $mpesa_receipt, $phone, $transaction_date);

                    return [
                        'success' => true,
                        'order_id' => $order_id,
                        'mpesa_receipt' => $mpesa_receipt,
                        'message' => 'Payment completed successfully'
                    ];
                } else {
                    // Payment failed
                    $this->updateFailedPayment($order_id, $result_desc);

                    return [
                        'success' => false,
                        'order_id' => $order_id,
                        'message' => 'Payment failed: ' . $result_desc
                    ];
                }
            }

            return ['success' => false, 'message' => 'Order not found'];
        } catch (Exception $e) {
            error_log("M-Pesa Callback Error: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    // Log payment request
    private function logPaymentRequest(int $order_id, string $phone, int|float $amount, array $response): bool
    {
        try {
            $query = "INSERT INTO payment_requests 
                     (order_id, phone, amount, merchant_request_id, checkout_request_id, response_data, created_at) 
                     VALUES (:order_id, :phone, :amount, :merchant_request_id, :checkout_request_id, :response_data, NOW())";

            $stmt = $this->db->prepare($query);
            $stmt->bindValue(":order_id", $order_id);
            $stmt->bindValue(":phone", $phone);
            $stmt->bindValue(":amount", $amount);
            $stmt->bindValue(":merchant_request_id", $response['MerchantRequestID'] ?? '');
            $stmt->bindValue(":checkout_request_id", $response['CheckoutRequestID'] ?? '');
            $stmt->bindValue(":response_data", json_encode($response));

            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Payment request logging error: " . $e->getMessage());
            return false;
        }
    }

    // Find order by checkout ID
    private function findOrderByCheckoutId(string $checkout_request_id): ?int
    {
        try {
            $query = "SELECT order_id FROM payment_requests 
                     WHERE checkout_request_id = :checkout_request_id 
                     ORDER BY created_at DESC LIMIT 1";

            $stmt = $this->db->prepare($query);
            $stmt->bindValue(":checkout_request_id", $checkout_request_id);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? $result['order_id'] : null;
        } catch (Exception $e) {
            error_log("Find order by checkout ID error: " . $e->getMessage());
            return null;
        }
    }

    // Update successful payment
    private function updateSuccessfulPayment(int $order_id, int|float $amount, string $mpesa_receipt, string $phone, string $transaction_date): bool
    {
        try {
            // Update order payment status
            $orderController = new OrderController();
            $orderController->updatePaymentStatus($order_id, 'paid', $mpesa_receipt);

            // Log successful payment
            $query = "INSERT INTO payment_transactions 
                     (order_id, amount, mpesa_receipt, phone, transaction_date, status, created_at) 
                     VALUES (:order_id, :amount, :mpesa_receipt, :phone, :transaction_date, 'success', NOW())";

            $stmt = $this->db->prepare($query);
            $stmt->bindValue(":order_id", $order_id);
            $stmt->bindValue(":amount", $amount);
            $stmt->bindValue(":mpesa_receipt", $mpesa_receipt);
            $stmt->bindValue(":phone", $phone);
            $stmt->bindValue(":transaction_date", $transaction_date);

            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Update successful payment error: " . $e->getMessage());
            return false;
        }
    }

    // Update failed payment
    private function updateFailedPayment(int $order_id, string $reason): bool
    {
        try {
            // Update order payment status
            $orderController = new OrderController();
            $orderController->updatePaymentStatus($order_id, 'failed');

            // Log failed payment
            $query = "INSERT INTO payment_transactions 
                     (order_id, status, failure_reason, created_at) 
                     VALUES (:order_id, 'failed', :failure_reason, NOW())";

            $stmt = $this->db->prepare($query);
            $stmt->bindValue(":order_id", $order_id);
            $stmt->bindValue(":failure_reason", $reason);

            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Update failed payment error: " . $e->getMessage());
            return false;
        }
    }

    // Check payment status
    public function checkPaymentStatus(int $order_id): ?array
    {
        try {
            $query = "SELECT pt.*, o.payment_status 
                     FROM payment_transactions pt 
                     RIGHT JOIN orders o ON pt.order_id = o.id 
                     WHERE o.id = :order_id 
                     ORDER BY pt.created_at DESC LIMIT 1";

            $stmt = $this->db->prepare($query);
            $stmt->bindParam(":order_id", $order_id);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Check payment status error: " . $e->getMessage());
            return null;
        }
    }
}
