<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../controllers/AuthController.php';
require_once __DIR__ . '/../../controllers/OrderController.php';
require_once __DIR__ . '/../../controllers/FishController.php';
require_once __DIR__ . '/../../controllers/UserController.php';

$authController = new AuthController();
$authController->requireRole(['admin', 'staff']);

// Initialize variables with defaults
$analytics = ['total_orders' => 0, 'total_revenue' => 0, 'total_customers' => 0];
$lowStockItems = [];
$recentOrders = [];
$customerStats = ['total_customers' => 0, 'new_customers_30_days' => 0];

try {
    // Initialize controllers
    $orderController = new OrderController();
    $fishController = new FishController();
    $userController = new UserController();

    // Get basic analytics
    $analytics = $orderController->getSalesAnalytics();
    $lowStockItems = $fishController->getLowStockItems();
    $recentOrders = $orderController->getRecentOrders(5);
    $customerStats = $userController->getCustomerStats();
} catch (Exception $e) {
    error_log("Dashboard error: " . $e->getMessage());
    // Use default values if there's an error
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo SITE_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100">
    <div class="flex">
        <!-- Sidebar -->
        <div class="w-64 bg-blue-800 min-h-screen p-4">
            <div class="text-white text-2xl font-bold mb-8">
                <i class="fas fa-fish mr-2"></i>
                <?php echo SITE_NAME; ?>
            </div>
            <nav class="space-y-2">
                <a href="<?php echo BASE_URL; ?>/views/staff/dashboard.php"
                    class="block text-white bg-blue-700 p-3 rounded flex items-center">
                    <i class="fas fa-tachometer-alt mr-3"></i>Dashboard
                </a>
                <a href="<?php echo BASE_URL; ?>/views/staff/fish_list.php"
                    class="block text-white hover:bg-blue-700 p-3 rounded flex items-center">
                    <i class="fas fa-fish mr-3"></i>Fish Management
                </a>
                <a href="<?php echo BASE_URL; ?>/views/staff/orders.php"
                    class="block text-white hover:bg-blue-700 p-3 rounded flex items-center">
                    <i class="fas fa-shopping-cart mr-3"></i>Orders
                </a>

                <?php if ($_SESSION['role'] === 'admin'): ?>
                    <a href="<?php echo BASE_URL; ?>/views/staff/users_list.php"
                        class="block text-white hover:bg-blue-700 p-3 rounded flex items-center">
                        <i class="fas fa-users mr-3"></i>Staff Management
                    </a>
                <?php endif; ?>

                <a href="<?php echo BASE_URL; ?>/views/auth/logout.php"
                    class="block text-white hover:bg-red-600 p-3 rounded flex items-center mt-8">
                    <i class="fas fa-sign-out-alt mr-3"></i>Logout
                </a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="flex-1 p-8">
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">Dashboard</h1>
                    <p class="text-gray-600">Welcome back, <?php echo htmlspecialchars($_SESSION['full_name']); ?>!</p>
                </div>
                <div class="text-sm text-gray-500">
                    <i class="fas fa-calendar-alt mr-2"></i>
                    <?php echo date('F j, Y'); ?>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Total Orders -->
                <div class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition duration-200">
                    <div class="flex items-center">
                        <div class="p-3 bg-blue-100 rounded-lg">
                            <i class="fas fa-shopping-cart text-blue-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Total Orders</p>
                            <h3 class="text-2xl font-bold text-gray-800"><?php echo $analytics['total_orders'] ?? 0; ?></h3>
                        </div>
                    </div>
                    <div class="mt-4">
                        <a href="<?php echo BASE_URL; ?>/views/staff/orders.php" class="text-blue-600 text-sm hover:text-blue-800">
                            View all orders →
                        </a>
                    </div>
                </div>

                <!-- Total Revenue -->
                <div class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition duration-200">
                    <div class="flex items-center">
                        <div class="p-3 bg-green-100 rounded-lg">
                            <i class="fas fa-money-bill-wave text-green-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Total Revenue</p>
                            <h3 class="text-2xl font-bold text-gray-800">Ksh <?php echo number_format($analytics['total_revenue'] ?? 0, 2); ?></h3>
                        </div>
                    </div>
                    <div class="mt-4">
                        <span class="text-green-600 text-sm">
                            <i class="fas fa-chart-line mr-1"></i>All time
                        </span>
                    </div>
                </div>

                <!-- Total Customers -->
                <div class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition duration-200">
                    <div class="flex items-center">
                        <div class="p-3 bg-purple-100 rounded-lg">
                            <i class="fas fa-users text-purple-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Total Customers</p>
                            <h3 class="text-2xl font-bold text-gray-800"><?php echo $customerStats['total_customers'] ?? 0; ?></h3>
                        </div>
                    </div>
                    <div class="mt-4">
                        <?php if (isset($customerStats['new_customers_30_days']) && $customerStats['new_customers_30_days'] > 0): ?>
                            <span class="text-green-600 text-sm">
                                +<?php echo $customerStats['new_customers_30_days']; ?> this month
                            </span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Low Stock Items -->
                <div class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition duration-200">
                    <div class="flex items-center">
                        <div class="p-3 <?php echo count($lowStockItems) > 0 ? 'bg-red-100' : 'bg-gray-100'; ?> rounded-lg">
                            <i class="fas fa-exclamation-triangle <?php echo count($lowStockItems) > 0 ? 'text-red-600' : 'text-gray-600'; ?> text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Low Stock Items</p>
                            <h3 class="text-2xl font-bold <?php echo count($lowStockItems) > 0 ? 'text-red-600' : 'text-gray-800'; ?>">
                                <?php echo count($lowStockItems); ?>
                            </h3>
                        </div>
                    </div>
                    <div class="mt-4">
                        <a href="<?php echo BASE_URL; ?>/views/staff/fish_list.php" class="text-red-600 text-sm hover:text-red-800">
                            Manage inventory →
                        </a>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Recent Orders -->
                <div class="bg-white rounded-lg shadow">
                    <div class="p-6 border-b border-gray-200 flex justify-between items-center">
                        <h2 class="text-xl font-semibold text-gray-800">Recent Orders</h2>
                        <a href="<?php echo BASE_URL; ?>/views/staff/orders.php" class="text-blue-600 text-sm hover:text-blue-800">
                            View all
                        </a>
                    </div>
                    <div class="p-6">
                        <?php if (empty($recentOrders)): ?>
                            <div class="text-center py-8">
                                <i class="fas fa-shopping-cart text-gray-300 text-4xl mb-3"></i>
                                <p class="text-gray-500">No orders yet</p>
                                <p class="text-gray-400 text-sm mt-1">Orders will appear here when customers place them</p>
                            </div>
                        <?php else: ?>
                            <div class="space-y-4">
                                <?php foreach ($recentOrders as $order): ?>
                                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition duration-200">
                                        <div class="flex-1">
                                            <div class="flex justify-between items-start">
                                                <div>
                                                    <p class="font-semibold text-gray-800">Order #<?php echo $order['id']; ?></p>
                                                    <p class="text-sm text-gray-600"><?php echo htmlspecialchars($order['customer_name'] ?? 'Customer'); ?></p>
                                                </div>
                                                <span class="px-3 py-1 text-xs font-semibold rounded-full 
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
                                            <div class="flex justify-between items-center mt-2">
                                                <p class="text-sm text-gray-600">Ksh <?php echo number_format($order['total_amount'], 2); ?></p>
                                                <p class="text-xs text-gray-500"><?php echo date('M j, g:i A', strtotime($order['created_at'])); ?></p>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white rounded-lg shadow">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-xl font-semibold text-gray-800">Quick Actions</h2>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 gap-4">
                            <a href="<?php echo BASE_URL; ?>/views/staff/fish_list.php"
                                class="flex items-center p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition duration-200">
                                <div class="p-3 bg-blue-100 rounded-lg">
                                    <i class="fas fa-fish text-blue-600"></i>
                                </div>
                                <div class="ml-4">
                                    <h3 class="font-semibold text-gray-800">Manage Products</h3>
                                    <p class="text-sm text-gray-600">Add, edit, or remove fish products</p>
                                </div>
                            </a>

                            <a href="<?php echo BASE_URL; ?>/views/staff/orders.php"
                                class="flex items-center p-4 bg-green-50 rounded-lg hover:bg-green-100 transition duration-200">
                                <div class="p-3 bg-green-100 rounded-lg">
                                    <i class="fas fa-shopping-cart text-green-600"></i>
                                </div>
                                <div class="ml-4">
                                    <h3 class="font-semibold text-gray-800">Process Orders</h3>
                                    <p class="text-sm text-gray-600">Update order status and track deliveries</p>
                                </div>
                            </a>

                            <?php if ($_SESSION['role'] === 'admin'): ?>
                                <a href="<?php echo BASE_URL; ?>/views/staff/users_list.php"
                                    class="flex items-center p-4 bg-purple-50 rounded-lg hover:bg-purple-100 transition duration-200">
                                    <div class="p-3 bg-purple-100 rounded-lg">
                                        <i class="fas fa-users text-purple-600"></i>
                                    </div>
                                    <div class="ml-4">
                                        <h3 class="font-semibold text-gray-800">Staff Management</h3>
                                        <p class="text-sm text-gray-600">Manage staff accounts and permissions</p>
                                    </div>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>