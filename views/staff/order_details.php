<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../controllers/AuthController.php';
require_once __DIR__ . '/../../controllers/OrderController.php';

$authController = new AuthController();
$authController->requireRole(['admin', 'staff']);

$orderController = new OrderController();

// Get order ID from URL
$order_id = $_GET['id'] ?? null;
if (!$order_id) {
    header('Location: orders.php');
    exit();
}

// Get order details
$order = $orderController->getOrderById($order_id);
if (!$order) {
    header('Location: orders.php');
    exit();
}

// Get order items
$orderItems = $orderController->getOrderItems($order_id);

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['status'])) {
    $orderController->updateOrderStatus($order_id, $_POST['status']);
    // Refresh order data
    $order = $orderController->getOrderById($order_id);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details - <?php echo SITE_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100">
    <div class="flex">
        <!-- Sidebar -->
        <?php include '../includes/staff_sidebar.php'; ?>

        <!-- Main Content -->
        <div class="flex-1 ml-64 p-8">
            <div class="max-w-4xl mx-auto">
                <div class="mb-6">
                    <a href="<?php echo BASE_URL; ?>/views/staff/orders.php"
                        class="text-blue-600 hover:text-blue-800 mb-4 inline-block">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Orders
                    </a>
                    <h1 class="text-3xl font-bold text-gray-800">Order Details</h1>
                    <p class="text-gray-600">Order #<?php echo $order['id']; ?></p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Order Information -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Order Items -->
                        <div class="bg-white rounded-lg shadow">
                            <div class="p-6 border-b border-gray-200">
                                <h2 class="text-xl font-semibold text-gray-800">Order Items</h2>
                            </div>
                            <div class="p-6">
                                <?php if (empty($orderItems)): ?>
                                    <p class="text-gray-500 text-center py-4">No items found.</p>
                                <?php else: ?>
                                    <div class="space-y-4">
                                        <?php foreach ($orderItems as $item): ?>
                                            <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                                                <div class="flex items-center space-x-4">
                                                    <img src="<?php echo BASE_URL; ?>/uploads/<?php echo htmlspecialchars($item['image_url'] ?? 'default_fish.png'); ?>"
                                                        alt="<?php echo htmlspecialchars($item['fish_name']); ?>"
                                                        class="w-16 h-16 object-cover rounded"
                                                        onerror="this.src='<?php echo BASE_URL; ?>/public/images/default_fish.png'">
                                                    <div>
                                                        <h3 class="font-semibold text-gray-800"><?php echo htmlspecialchars($item['fish_name']); ?></h3>
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

                        <!-- Customer Information -->
                        <div class="bg-white rounded-lg shadow">
                            <div class="p-6 border-b border-gray-200">
                                <h2 class="text-xl font-semibold text-gray-800">Customer Information</h2>
                            </div>
                            <div class="p-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-sm text-gray-600">Customer Name</p>
                                        <p class="font-semibold text-gray-800"><?php echo htmlspecialchars($order['customer_name'] ?? 'N/A'); ?></p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-600">Email</p>
                                        <p class="font-semibold text-gray-800"><?php echo htmlspecialchars($order['customer_email'] ?? 'N/A'); ?></p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-600">Phone</p>
                                        <p class="font-semibold text-gray-800"><?php echo htmlspecialchars($order['phone'] ?? 'N/A'); ?></p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-600">Order Date</p>
                                        <p class="font-semibold text-gray-800"><?php echo date('M j, Y g:i A', strtotime($order['created_at'])); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Order Summary -->
                    <div class="space-y-6">
                        <!-- Order Status -->
                        <div class="bg-white rounded-lg shadow p-6">
                            <h2 class="text-xl font-semibold text-gray-800 mb-4">Order Status</h2>
                            <form method="POST" class="space-y-4">
                                <div>
                                    <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                                        Update Status
                                    </label>
                                    <select id="status" name="status"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="processing" <?php echo $order['status'] === 'processing' ? 'selected' : ''; ?>>Processing</option>
                                        <option value="completed" <?php echo $order['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                        <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                    </select>
                                </div>
                                <button type="submit"
                                    class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    Update Status
                                </button>
                            </form>
                            <div class="mt-4 p-3 bg-gray-50 rounded">
                                <p class="text-sm text-gray-600">Current Status:</p>
                                <span class="px-2 py-1 text-xs font-semibold rounded-full 
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
                        </div>

                        <!-- Order Summary -->
                        <div class="bg-white rounded-lg shadow p-6">
                            <h2 class="text-xl font-semibold text-gray-800 mb-4">Order Summary</h2>
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

                        <!-- Shipping Information -->
                        <div class="bg-white rounded-lg shadow p-6">
                            <h2 class="text-xl font-semibold text-gray-800 mb-4">Shipping Address</h2>
                            <p class="text-gray-700"><?php echo nl2br(htmlspecialchars($order['shipping_address'] ?? 'No address provided')); ?></p>
                            <?php if (!empty($order['notes'])): ?>
                                <div class="mt-4 p-3 bg-blue-50 rounded">
                                    <p class="text-sm font-medium text-blue-800">Customer Notes:</p>
                                    <p class="text-sm text-blue-700 mt-1"><?php echo htmlspecialchars($order['notes']); ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>