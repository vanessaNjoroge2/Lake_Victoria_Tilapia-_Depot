<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Order.php';

class MpesaController
{
    private $db;
    private $order;
    private $consumerKey;
    private $consumerSecret;
    private $shortcode;
    private $passkey;
    private $callbackUrl;

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
    private function getAccessToken()
    {
        // Validate credentials are configured
        if (
            $this->consumerKey === 'your_consumer_key_here' ||
            $this->consumerSecret === 'your_consumer_secret_here'
        ) {
            error_log("MPESA Config Error: Consumer key/secret not configured");
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
    public function initiateSTKPush($phone, $amount, $orderId, $description = "Fish Order Payment")
    {
        try {
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

            $curl_response = curl_exec($curl);

            if (curl_errno($curl)) {
                $error = curl_error($curl);
                error_log("MPESA STK Push cURL Error: " . $error);
                throw new Exception("Payment request failed: " . $error);
            }

            $response = json_decode($curl_response, true);

            // Log the request
            $this->logPaymentRequest($orderId, $phone, $amount, $response);

            // Check for success response
            if (isset($response['ResponseCode']) && $response['ResponseCode'] == '0') {
                error_log("MPESA STK Push initiated successfully for Order #{$orderId}");
            } else {
                $errorMessage = $response['errorMessage'] ?? $response['ResponseDescription'] ?? 'Unknown error';
                error_log("MPESA STK Push failed for Order #{$orderId}: " . $errorMessage);
            }

            return $response;
        } catch (Exception $e) {
            error_log("M-Pesa STK Push Error: " . $e->getMessage());
            return ['error' => $e->getMessage(), 'ResponseCode' => '1'];
        }
    }

    // Handle M-Pesa callback
    public function handleCallback($callback_data)
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
    private function logPaymentRequest($order_id, $phone, $amount, $response)
    {
        try {
            $query = "INSERT INTO payment_requests 
                     (order_id, phone, amount, merchant_request_id, checkout_request_id, response_data, created_at) 
                     VALUES (:order_id, :phone, :amount, :merchant_request_id, :checkout_request_id, :response_data, NOW())";

            $stmt = $this->db->prepare($query);
            $stmt->bindParam(":order_id", $order_id);
            $stmt->bindParam(":phone", $phone);
            $stmt->bindParam(":amount", $amount);
            $stmt->bindParam(":merchant_request_id", $response['MerchantRequestID'] ?? '');
            $stmt->bindParam(":checkout_request_id", $response['CheckoutRequestID'] ?? '');
            $stmt->bindParam(":response_data", json_encode($response));

            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Payment request logging error: " . $e->getMessage());
            return false;
        }
    }

    // Find order by checkout ID
    private function findOrderByCheckoutId($checkout_request_id)
    {
        try {
            $query = "SELECT order_id FROM payment_requests 
                     WHERE checkout_request_id = :checkout_request_id 
                     ORDER BY created_at DESC LIMIT 1";

            $stmt = $this->db->prepare($query);
            $stmt->bindParam(":checkout_request_id", $checkout_request_id);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? $result['order_id'] : null;
        } catch (Exception $e) {
            error_log("Find order by checkout ID error: " . $e->getMessage());
            return null;
        }
    }

    // Update successful payment
    private function updateSuccessfulPayment($order_id, $amount, $mpesa_receipt, $phone, $transaction_date)
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
            $stmt->bindParam(":order_id", $order_id);
            $stmt->bindParam(":amount", $amount);
            $stmt->bindParam(":mpesa_receipt", $mpesa_receipt);
            $stmt->bindParam(":phone", $phone);
            $stmt->bindParam(":transaction_date", $transaction_date);

            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Update successful payment error: " . $e->getMessage());
            return false;
        }
    }

    // Update failed payment
    private function updateFailedPayment($order_id, $reason)
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
            $stmt->bindParam(":order_id", $order_id);
            $stmt->bindParam(":failure_reason", $reason);

            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Update failed payment error: " . $e->getMessage());
            return false;
        }
    }

    // Check payment status
    public function checkPaymentStatus($order_id)
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
