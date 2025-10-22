<?php
// Start session and check authentication
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header('Location: ../auth/login.php');
    exit();
}

if (!isset($_GET['id'])) {
    header('Location: my_orders.php');
    exit();
}

// Include configuration and models
require_once '../../config/database.php';
require_once '../../models/Order.php';
require_once '../../controllers/OrderController.php';

$order_id = $_GET['id'];
$order = [];
$orderItems = [];
$success = '';
$error = '';

// Define default image path
$defaultImage = BASE_URL . '/uploads/fresh_tilapia.jpg';

try {
    // Initialize database connection
    $database = new Database();
    $db = $database->getConnection();

    // Get order details
    $orderModel = new Order($db);
    $order = $orderModel->getById($order_id);
    $orderItems = $orderModel->getOrderItems($order_id);

    // Check if order belongs to current user
    if (!$order || $order['customer_id'] != $_SESSION['user_id']) {
        header('Location: my_orders.php');
        exit();
    }

    // Handle order actions
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $orderController = new OrderController();
        $action = $_POST['action'] ?? '';

        if ($action === 'cancel_order') {
            if ($orderController->cancelOrder($order_id, $_SESSION['user_id'])) {
                $success = "Order has been cancelled successfully!";
                // Refresh order data
                $order = $orderModel->getById($order_id);
            } else {
                $error = "Unable to cancel order. The order may have already been processed.";
            }
        } elseif ($action === 'delete_order') {
            if ($orderController->deleteOrderByCustomer($order_id, $_SESSION['user_id'])) {
                $success = "Order has been permanently deleted!";
                // Redirect to orders list after deletion
                header('Location: my_orders.php?deleted=1');
                exit();
            } else {
                $error = "Unable to delete order. The order may have already been processed or shipped.";
            }
        }
    }
} catch (Exception $e) {
    $error = "Error loading order details: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details - Lake Victoria Tilapia Depot</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .image-container {
            position: relative;
            width: 64px;
            height: 64px;
        }

        .product-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 8px;
            transition: opacity 0.3s ease;
        }

        .image-placeholder {
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #9ca3af;
        }
    </style>
</head>

<body class="bg-gray-100">
    <!-- Include Header -->
    <?php include '../../views/includes/header.php'; ?>

    <!-- Include Navigation -->
    <?php include '../../views/includes/navbar.php'; ?>

    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto">
            <div class="mb-6">
                <a href="my_orders.php" class="text-blue-600 hover:text-blue-800 mb-4 inline-block">
                    <i class="fas fa-arrow-left mr-2"></i>Back to My Orders
                </a>
                <h1 class="text-3xl font-bold text-gray-800">Order Details</h1>
                <p class="text-gray-600">Order #<?php echo htmlspecialchars($order_id); ?></p>
            </div>

            <!-- Success/Error Messages -->
            <?php if ($success): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                    <i class="fas fa-check-circle mr-2"></i><?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                    <i class="fas fa-exclamation-triangle mr-2"></i><?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Order Items -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Order Summary -->
                    <div class="bg-white rounded-lg shadow">
                        <div class="p-6 border-b border-gray-200">
                            <h2 class="text-xl font-semibold text-gray-800">Order Summary</h2>
                        </div>
                        <div class="p-6">
                            <?php if (empty($orderItems)): ?>
                                <div class="text-center py-8">
                                    <i class="fas fa-shopping-basket text-gray-300 text-4xl mb-3"></i>
                                    <p class="text-gray-500">No items found in this order.</p>
                                </div>
                            <?php else: ?>
                                <div class="space-y-4">
                                    <?php foreach ($orderItems as $item):
                                        // Use the product image if available, otherwise use fresh_tilapia.jpg
                                        $imageUrl = !empty($item['image_url']) ? BASE_URL . '/uploads/' . $item['image_url'] : $defaultImage;
                                    ?>
                                        <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition duration-200">
                                            <div class="flex items-center space-x-4">
                                                <div class="image-container">
                                                    <img
                                                        src="<?php echo $imageUrl; ?>"
                                                        alt="<?php echo htmlspecialchars($item['fish_name']); ?>"
                                                        class="product-image"
                                                        onerror="this.src='<?php echo $defaultImage; ?>'">
                                                </div>
                                                <div class="min-w-0 flex-1">
                                                    <h3 class="font-semibold text-gray-800 truncate"><?php echo htmlspecialchars($item['fish_name']); ?></h3>
                                                    <p class="text-sm text-gray-600">Quantity: <?php echo $item['quantity']; ?></p>
                                                    <p class="text-sm text-gray-600">Ksh <?php echo number_format($item['unit_price'], 2); ?> each</p>
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <p class="font-semibold text-gray-800">
                                                    Ksh <?php echo number_format($item['quantity'] * $item['unit_price'], 2); ?>
                                                </p>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Order Information -->
                    <div class="bg-white rounded-lg shadow">
                        <div class="p-6 border-b border-gray-200">
                            <h2 class="text-xl font-semibold text-gray-800">Order Information</h2>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <h3 class="font-semibold text-gray-700 mb-2">Order Details</h3>
                                    <div class="space-y-2">
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">Order Date:</span>
                                            <span class="font-medium"><?php echo date('M j, Y g:i A', strtotime($order['created_at'])); ?></span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">Order Status:</span>
                                            <span class="font-medium px-2 py-1 text-xs rounded-full 
                                                <?php echo match ($order['status']) {
                                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                                    'processing' => 'bg-blue-100 text-blue-800',
                                                    'completed' => 'bg-green-100 text-green-800',
                                                    'cancelled' => 'bg-red-100 text-red-800',
                                                    default => 'bg-gray-100 text-gray-800'
                                                }; ?>">
                                                <?php echo ucfirst($order['status']); ?>
                                            </span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">Payment Status:</span>
                                            <span class="font-medium px-2 py-1 text-xs rounded-full 
                                                <?php echo $order['payment_status'] === 'paid' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'; ?>">
                                                <?php echo ucfirst($order['payment_status']); ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <h3 class="font-semibold text-gray-700 mb-2">Delivery Information</h3>
                                    <div class="space-y-2">
                                        <div>
                                            <span class="text-gray-600">Phone:</span>
                                            <p class="font-medium"><?php echo htmlspecialchars($order['phone'] ?? 'N/A'); ?></p>
                                        </div>
                                        <div>
                                            <span class="text-gray-600">Address:</span>
                                            <p class="font-medium"><?php echo nl2br(htmlspecialchars($order['shipping_address'] ?? 'N/A')); ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <?php if (!empty($order['notes'])): ?>
                                <div class="mt-6 p-4 bg-blue-50 rounded-lg">
                                    <h4 class="font-semibold text-blue-800 mb-2">Order Notes</h4>
                                    <p class="text-blue-700"><?php echo htmlspecialchars($order['notes']); ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Order Total & Actions -->
                <div class="space-y-6">
                    <!-- Order Total -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h2 class="text-xl font-semibold text-gray-800 mb-4">Order Total</h2>
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Subtotal</span>
                                <span class="font-semibold">Ksh <?php echo number_format($order['total_amount'], 2); ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Shipping</span>
                                <span class="font-semibold">Ksh 0.00</span>
                            </div>
                            <div class="flex justify-between text-lg font-bold border-t pt-3">
                                <span>Total</span>
                                <span class="text-green-600">Ksh <?php echo number_format($order['total_amount'], 2); ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Order Actions -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h2 class="text-xl font-semibold text-gray-800 mb-4">Order Actions</h2>
                        <div class="space-y-3">
                            <?php if ($order['status'] === 'pending'): ?>
                                <!-- Cancel Order Form -->
                                <form method="POST" onsubmit="return confirm('Are you sure you want to cancel this order? This will remove the order from your history.');">
                                    <input type="hidden" name="action" value="cancel_order">
                                    <button type="submit"
                                        class="w-full bg-yellow-600 text-white py-2 px-4 rounded hover:bg-yellow-700 transition duration-200 flex items-center justify-center">
                                        <i class="fas fa-times mr-2"></i>Cancel Order
                                    </button>
                                </form>

                                <!-- Delete Order Form -->
                                <form method="POST" onsubmit="return confirm('Are you sure you want to permanently delete this order? This action cannot be undone and will remove all order details.');">
                                    <input type="hidden" name="action" value="delete_order">
                                    <button type="submit"
                                        class="w-full bg-red-600 text-white py-2 px-4 rounded hover:bg-red-700 transition duration-200 flex items-center justify-center">
                                        <i class="fas fa-trash mr-2"></i>Delete Order Permanently
                                    </button>
                                </form>

                            <?php elseif ($order['status'] === 'cancelled'): ?>
                                <!-- For cancelled orders, show delete option -->
                                <form method="POST" onsubmit="return confirm('Are you sure you want to permanently delete this cancelled order? This action cannot be undone.');">
                                    <input type="hidden" name="action" value="delete_order">
                                    <button type="submit"
                                        class="w-full bg-red-600 text-white py-2 px-4 rounded hover:bg-red-700 transition duration-200 flex items-center justify-center">
                                        <i class="fas fa-trash mr-2"></i>Delete Cancelled Order
                                    </button>
                                </form>

                                <div class="text-center py-2">
                                    <i class="fas fa-ban text-red-500 text-xl mb-1"></i>
                                    <p class="text-red-600 font-semibold">Order Cancelled</p>
                                </div>

                            <?php elseif (in_array($order['status'], ['processing', 'completed'])): ?>
                                <div class="text-center py-3">
                                    <i class="fas fa-info-circle text-blue-500 text-2xl mb-2"></i>
                                    <p class="text-blue-600 font-semibold">Order Being Processed</p>
                                    <p class="text-gray-500 text-sm mt-1">This order can no longer be modified or deleted</p>
                                </div>
                            <?php endif; ?>

                            <div class="border-t pt-3 space-y-2">
                                <a href="browse_fish.php"
                                    class="w-full bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700 transition duration-200 text-center block">
                                    <i class="fas fa-shopping-cart mr-2"></i>Continue Shopping
                                </a>

                                <a href="my_orders.php"
                                    class="w-full bg-gray-200 text-gray-800 py-2 px-4 rounded hover:bg-gray-300 transition duration-200 text-center block">
                                    <i class="fas fa-history mr-2"></i>Back to Orders
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Support Information -->
                    <div class="bg-blue-50 rounded-lg p-6">
                        <h3 class="font-semibold text-blue-800 mb-2">Need Help?</h3>
                        <p class="text-blue-700 text-sm mb-3">
                            If you have any questions about your order, please contact our customer support.
                        </p>
                        <div class="space-y-1 text-sm text-blue-600">
                            <p><i class="fas fa-phone mr-2"></i>+254 700 000 000</p>
                            <p><i class="fas fa-envelope mr-2"></i>support@tilapiadepot.com</p>
                            <p><i class="fas fa-clock mr-2"></i>Mon-Sun: 8:00 AM - 8:00 PM</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Include Footer -->
    <?php include '../../views/includes/footer.php'; ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Simple image error handling - if an image fails, it will use the default
            const images = document.querySelectorAll('.product-image');

            images.forEach(img => {
                img.addEventListener('error', function() {
                    console.log('Image failed to load, using default image');
                    this.src = '<?php echo $defaultImage; ?>';
                });
            });

            // Form confirmation handling
            const forms = document.querySelectorAll('form[onsubmit]');
            forms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    const action = this.querySelector('input[name="action"]').value;
                    let message = '';

                    if (action === 'cancel_order') {
                        message = 'Are you sure you want to cancel this order? This will remove the order from your history.';
                    } else if (action === 'delete_order') {
                        message = 'Are you sure you want to permanently delete this order? This action cannot be undone and will remove all order details.';
                    }

                    if (message && !confirm(message)) {
                        e.preventDefault();
                    }
                });
            });
        });
    </script>
</body>

</html>