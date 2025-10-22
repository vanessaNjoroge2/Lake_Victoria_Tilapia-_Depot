<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Order.php';

class OrderController
{
    private $db;
    private $order;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->order = new Order($this->db);
    }

    public function getAllOrders()
    {
        try {
            $orders = $this->order->getAll();
            return is_array($orders) ? $orders : [];
        } catch (Exception $e) {
            error_log("Get all orders error: " . $e->getMessage());
            return [];
        }
    }

    public function getRecentOrders($limit = 5)
    {
        try {
            $orders = $this->order->getRecentOrders($limit);
            return is_array($orders) ? $orders : [];
        } catch (Exception $e) {
            error_log("Get recent orders error: " . $e->getMessage());
            return [];
        }
    }

    public function getSalesAnalytics()
    {
        try {
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

    public function updateOrderStatus($order_id, $status)
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

    public function getOrderById($order_id)
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

    public function getOrderItems($order_id)
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

    public function getOrdersByCustomer($customer_id)
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

    public function createOrder($orderData)
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

    public function addOrderItem($order_id, $fish_id, $quantity, $unit_price)
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

    public function getMonthlySales()
    {
        try {
            $sales = $this->order->getMonthlySales();
            return is_array($sales) ? $sales : [];
        } catch (Exception $e) {
            error_log("Get monthly sales error: " . $e->getMessage());
            return [];
        }
    }

    public function getOrderStats()
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

    public function updatePaymentStatus($order_id, $payment_status, $mpesa_receipt = null)
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

    public function searchOrders($search_term)
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

    public function getOrdersByStatus($status)
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

    public function deleteOrder($order_id)
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

    public function cancelOrder($order_id, $customer_id)
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

    public function deleteOrderByCustomer($order_id, $customer_id)
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

    public function canCustomerDeleteOrder($order_id, $customer_id)
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

    public function processPayment($order_id, $phone, $amount)
    {
        try {
            if (empty($order_id) || empty($phone) || empty($amount)) {
                return ['error' => 'Missing required payment parameters'];
            }

            require_once 'MpesaController.php';
            $mpesa = new MpesaController();

            $response = $mpesa->initiateSTKPush($phone, $amount, $order_id);

            if (isset($response['error'])) {
                // Update order payment status to failed
                $this->updatePaymentStatus($order_id, 'failed');
                return ['error' => $response['error']];
            }

            if (isset($response['ResponseCode']) && $response['ResponseCode'] == '0') {
                // STK push initiated successfully
                $this->updatePaymentStatus($order_id, 'pending');
                return [
                    'success' => true,
                    'message' => 'Payment request sent to your phone',
                    'checkout_request_id' => $response['CheckoutRequestID']
                ];
            } else {
                $this->updatePaymentStatus($order_id, 'failed');
                return ['error' => $response['ResponseDescription'] ?? 'Payment initiation failed'];
            }
        } catch (Exception $e) {
            error_log("Process payment error: " . $e->getMessage());
            return ['error' => 'Payment processing failed'];
        }
    }

    public function getOrderPaymentStatus($order_id)
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
