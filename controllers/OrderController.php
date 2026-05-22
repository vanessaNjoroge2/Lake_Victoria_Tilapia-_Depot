<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Order.php';

class OrderController
{
    private ?\PDO $db;
    private ?Order $order;

    public function __construct()
    {
        try {
            $database = new Database();
            $this->db = $database->getConnection();

            if ($this->db) {
                $this->order = new Order($this->db);
            } else {
                error_log("OrderController: Failed to initialize database connection.");
                $this->order = null;
            }
        } catch (Exception $e) {
            error_log("OrderController constructor error: " . $e->getMessage());
            $this->order = null;
        }
    }

    public function getAllOrders(): array
    {
        try {
            if (!$this->order) return [];
            $orders = $this->order->getAll();
            return is_array($orders) ? $orders : [];
        } catch (Exception $e) {
            error_log("Get all orders error: " . $e->getMessage());
            return [];
        }
    }

    public function getRecentOrders(int $limit = 5): array
    {
        try {
            if (!$this->order) return [];
            $orders = $this->order->getRecentOrders($limit);
            return is_array($orders) ? $orders : [];
        } catch (Exception $e) {
            error_log("Get recent orders error: " . $e->getMessage());
            return [];
        }
    }

    public function getSalesAnalytics(): array
    {
        try {
            if (!$this->order) return ['total_orders' => 0, 'total_revenue' => 0, 'total_customers' => 0];
            $analytics = $this->order->getSalesAnalytics();
            return is_array($analytics) ? $analytics : [
                'total_orders' => 0,
                'total_revenue' => 0,
                'total_customers' => 0
            ];
        } catch (Exception $e) {
            error_log("Get sales analytics error: " . $e->getMessage());
            return ['total_orders' => 0, 'total_revenue' => 0, 'total_customers' => 0];
        }
    }

    public function updateOrderStatus(int $order_id, string $status): bool
    {
        try {
            // Enhanced validation and debugging
            if (empty($order_id) || empty($status)) {
                error_log("OrderController: Empty order_id or status received - order_id: '{$order_id}', status: '{$status}'");
                return false;
            }

            // Validate order_id is numeric
            if (!is_numeric($order_id)) {
                error_log("OrderController: Invalid order_id format - '{$order_id}'");
                return false;
            }

            $order_id = intval($order_id);

            // Validate status is allowed
            $allowed_statuses = ['pending', 'processing', 'completed', 'cancelled'];
            if (!in_array($status, $allowed_statuses)) {
                error_log("OrderController: Invalid status '{$status}' for order #{$order_id}");
                return false;
            }

            error_log("OrderController: Attempting to update order #{$order_id} to status: '{$status}'");

            // Check if order exists first
            $existingOrder = $this->order->getById($order_id);
            if (!$existingOrder) {
                error_log("OrderController: Order #{$order_id} not found");
                return false;
            }

            error_log("OrderController: Current order status: '{$existingOrder['status']}'");

            // Update the status
            $result = $this->order->updateStatus($order_id, $status);

            if ($result) {
                error_log("OrderController: SUCCESS - Order #{$order_id} status updated from '{$existingOrder['status']}' to '{$status}'");

                // Verify the update was successful
                $updatedOrder = $this->order->getById($order_id);
                if ($updatedOrder && $updatedOrder['status'] === $status) {
                    error_log("OrderController: VERIFIED - Order #{$order_id} status confirmed as '{$status}'");
                } else {
                    error_log("OrderController: WARNING - Order #{$order_id} status may not have been updated properly");
                }
            } else {
                error_log("OrderController: FAILED - Could not update order #{$order_id} status to '{$status}'");
            }

            return $result;
        } catch (Exception $e) {
            $error_msg = "Update order status error for order #{$order_id}: " . $e->getMessage();
            error_log("OrderController: " . $error_msg);
            return false;
        }
    }

    public function getOrderById(int $order_id): ?array
    {
        try {
            if (empty($order_id)) {
                return null;
            }
            return $this->order->getById($order_id);
        } catch (Exception $e) {
            error_log("Get order by ID error: " . $e->getMessage());
            return null;
        }
    }

    public function getOrderItems(int $order_id): array
    {
        try {
            if (empty($order_id)) {
                return [];
            }
            $items = $this->order->getOrderItems($order_id);
            return is_array($items) ? $items : [];
        } catch (Exception $e) {
            error_log("Get order items error: " . $e->getMessage());
            return [];
        }
    }

    public function getOrdersByCustomer(int $customer_id): array
    {
        try {
            if (empty($customer_id)) {
                return [];
            }
            $orders = $this->order->getByCustomer($customer_id);
            return is_array($orders) ? $orders : [];
        } catch (Exception $e) {
            error_log("Get orders by customer error: " . $e->getMessage());
            return [];
        }
    }

    public function createOrder(array $orderData): bool|int
    {
        try {
            // Validate required fields
            $required = ['customer_id', 'total_amount', 'shipping_address', 'phone'];
            foreach ($required as $field) {
                if (empty($orderData[$field])) {
                    throw new Exception("Missing required field: $field");
                }
            }

            $this->order->customer_id = $orderData['customer_id'];
            $this->order->total_amount = $orderData['total_amount'];
            $this->order->shipping_address = $orderData['shipping_address'];
            $this->order->phone = $orderData['phone'];
            $this->order->notes = $orderData['notes'] ?? '';

            return $this->order->create();
        } catch (Exception $e) {
            error_log("Create order error: " . $e->getMessage());
            return false;
        }
    }

    public function addOrderItem(int $order_id, int $fish_id, int $quantity, int|float $unit_price): bool
    {
        try {
            if (empty($order_id) || empty($fish_id) || empty($quantity) || empty($unit_price)) {
                return false;
            }
            return $this->order->addOrderItem($order_id, $fish_id, $quantity, $unit_price);
        } catch (Exception $e) {
            error_log("Add order item error: " . $e->getMessage());
            return false;
        }
    }

    public function getMonthlySales(): array
    {
        try {
            $sales = $this->order->getMonthlySales();
            return is_array($sales) ? $sales : [];
        } catch (Exception $e) {
            error_log("Get monthly sales error: " . $e->getMessage());
            return [];
        }
    }

    public function getOrderStats(): array
    {
        try {
            $stats = $this->order->getOrderStats();
            return is_array($stats) ? $stats : [
                'total_orders' => 0,
                'pending_orders' => 0,
                'processing_orders' => 0,
                'completed_orders' => 0,
                'cancelled_orders' => 0,
                'total_revenue' => 0
            ];
        } catch (Exception $e) {
            error_log("Get order stats error: " . $e->getMessage());
            return [
                'total_orders' => 0,
                'pending_orders' => 0,
                'processing_orders' => 0,
                'completed_orders' => 0,
                'cancelled_orders' => 0,
                'total_revenue' => 0
            ];
        }
    }

    public function updatePaymentStatus(int $order_id, string $payment_status, ?string $mpesa_receipt = null): bool
    {
        try {
            if (empty($order_id) || empty($payment_status)) {
                return false;
            }
            return $this->order->updatePaymentStatus($order_id, $payment_status, $mpesa_receipt);
        } catch (Exception $e) {
            error_log("Update payment status error: " . $e->getMessage());
            return false;
        }
    }

    public function searchOrders(string $search_term): array
    {
        try {
            if (empty($search_term)) {
                return [];
            }
            $results = $this->order->searchOrders($search_term);
            return is_array($results) ? $results : [];
        } catch (Exception $e) {
            error_log("Search orders error: " . $e->getMessage());
            return [];
        }
    }

    public function getOrdersByStatus(string $status): array
    {
        try {
            if (empty($status)) {
                return [];
            }
            $orders = $this->order->getOrdersByStatus($status);
            return is_array($orders) ? $orders : [];
        } catch (Exception $e) {
            error_log("Get orders by status error: " . $e->getMessage());
            return [];
        }
    }

    public function deleteOrder(int $order_id): bool
    {
        try {
            if (empty($order_id)) {
                return false;
            }
            return $this->order->delete($order_id);
        } catch (Exception $e) {
            error_log("Delete order error: " . $e->getMessage());
            return false;
        }
    }

    public function cancelOrder(int $order_id, int $customer_id): bool
    {
        try {
            if (empty($order_id) || empty($customer_id)) {
                return false;
            }
            return $this->order->cancelOrder($order_id, $customer_id);
        } catch (Exception $e) {
            error_log("Cancel order error: " . $e->getMessage());
            return false;
        }
    }

    public function deleteOrderByCustomer(int $order_id, int $customer_id): bool
    {
        try {
            if (empty($order_id) || empty($customer_id)) {
                return false;
            }
            return $this->order->deleteOrderByCustomer($order_id, $customer_id);
        } catch (Exception $e) {
            error_log("Delete order by customer error: " . $e->getMessage());
            return false;
        }
    }

    public function canCustomerDeleteOrder(int $order_id, int $customer_id): bool
    {
        try {
            if (empty($order_id) || empty($customer_id)) {
                return false;
            }
            return $this->order->canCustomerDeleteOrder($order_id, $customer_id);
        } catch (Exception $e) {
            error_log("Check order deletion permission error: " . $e->getMessage());
            return false;
        }
    }
    // Add to OrderController class

    public function processPayment(int $order_id, string $phone, int|float $amount): array
    {
        try {
            if (empty($order_id) || empty($phone) || empty($amount)) {
                return ['error' => 'Missing required payment parameters'];
            }

            require_once 'MpesaController.php';
            require_once 'MpesaHelper.php';

            // Validate phone number format
            $formatted_phone = MpesaHelper::formatPhoneNumber($phone);
            if (!$formatted_phone) {
                return ['error' => 'Invalid phone number format. Please use: 0712345678 or 254712345678'];
            }

            // Validate amount
            if (!MpesaHelper::isValidAmount($amount)) {
                return ['error' => 'Invalid amount. Must be between 1 and 150,000 KSH'];
            }

            $mpesa = new MpesaController();

            $response = $mpesa->initiateSTKPush($formatted_phone, $amount, $order_id);

            if (isset($response['error'])) {
                // Update order payment status to failed
                $this->updatePaymentStatus($order_id, 'failed');
                error_log("Payment processing error for Order #{$order_id}: " . $response['error']);
                return ['error' => $response['error']];
            }

            if (isset($response['ResponseCode']) && $response['ResponseCode'] == '0') {
                // STK push initiated successfully
                $this->updatePaymentStatus($order_id, 'pending');
                error_log("Payment initiated successfully for Order #{$order_id}");
                return [
                    'success' => true,
                    'message' => 'Payment request sent to your phone',
                    'checkout_request_id' => $response['CheckoutRequestID'] ?? null
                ];
            } else {
                $this->updatePaymentStatus($order_id, 'failed');
                $error_msg = $response['ResponseDescription'] ?? $response['errorMessage'] ?? 'Payment initiation failed. Please check your phone number and try again.';
                error_log("Payment failed for Order #{$order_id}: " . json_encode($response));
                return ['error' => $error_msg];
            }
        } catch (Exception $e) {
            error_log("Process payment exception for Order #{$order_id}: " . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    public function getOrderPaymentStatus(int $order_id): ?array
    {
        try {
            require_once 'MpesaController.php';
            $mpesa = new MpesaController();
            return $mpesa->checkPaymentStatus($order_id);
        } catch (Exception $e) {
            error_log("Get order payment status error: " . $e->getMessage());
            return null;
        }
    }
}
