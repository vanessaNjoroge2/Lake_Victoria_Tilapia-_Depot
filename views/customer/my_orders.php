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

// Include configuration and models
require_once '../../config/database.php';
require_once '../../models/Order.php';

// Handle order deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_order') {
    $order_id = intval($_POST['order_id']);
    $customer_id = $_SESSION['user_id'];

    try {
        $database = new Database();
        $db = $database->getConnection();
        $order = new Order($db);

        // Attempt to delete the order
        if ($order->deleteOrder($order_id, $customer_id)) {
            $_SESSION['success_message'] = 'Order deleted successfully.';
            header('Location: my_orders.php?deleted=1');
            exit();
        } else {
            $_SESSION['error_message'] = 'Failed to delete order. Only cancelled orders can be deleted.';
        }
    } catch (Exception $e) {
        $_SESSION['error_message'] = 'Error deleting order: ' . $e->getMessage();
    }
}

// Check for deletion success
$deletionSuccess = isset($_GET['deleted']) && $_GET['deleted'] == 1;

try {
    // Initialize database connection
    $database = new Database();
    $db = $database->getConnection();

    // Get orders for the current customer
    $order = new Order($db);
    $orders = $order->getByCustomer($_SESSION['user_id']);
} catch (Exception $e) {
    $error = "Error loading orders: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - Lake Victoria Tilapia Depot</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="bg-gray-100">
    <!-- Include Header -->
    <?php include '../../views/includes/header.php'; ?>

    <!-- Include Navigation -->
    <?php include '../../views/includes/navbar.php'; ?>

    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-8">My Orders</h1>

        <?php if ($deletionSuccess || isset($_SESSION['success_message'])): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                <i class="fas fa-check-circle mr-2"></i>
                <?php
                if (isset($_SESSION['success_message'])) {
                    echo htmlspecialchars($_SESSION['success_message']);
                    unset($_SESSION['success_message']);
                } else {
                    echo 'Order has been permanently deleted successfully!';
                }
                ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                <i class="fas fa-exclamation-circle mr-2"></i><?php echo htmlspecialchars($_SESSION['error_message']);
                                                                unset($_SESSION['error_message']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if (empty($orders)): ?>
            <div class="bg-white rounded-lg shadow p-6 text-center">
                <p class="text-gray-600 text-lg">You haven't placed any orders yet.</p>
                <a href="browse_fish.php" class="inline-block mt-4 bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                    Start Shopping
                </a>
            </div>
        <?php else: ?>
            <div class="space-y-6">
                <?php foreach ($orders as $order): ?>
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-800">
                                    Order #<?php echo htmlspecialchars($order['id']); ?>
                                </h3>
                                <p class="text-gray-600 text-sm">
                                    Placed on: <?php echo date('M j, Y g:i A', strtotime($order['created_at'])); ?>
                                </p>
                            </div>
                            <div class="text-right">
                                <span class="inline-block px-3 py-1 rounded-full text-sm font-semibold 
                                    <?php echo match ($order['status']) {
                                        'pending' => 'bg-yellow-100 text-yellow-800',
                                        'processing' => 'bg-blue-100 text-blue-800',
                                        'completed' => 'bg-green-100 text-green-800',
                                        'cancelled' => 'bg-red-100 text-red-800',
                                        default => 'bg-gray-100 text-gray-800'
                                    }; ?>">
                                    <?php echo ucfirst(htmlspecialchars($order['status'])); ?>
                                </span>
                                <p class="text-lg font-bold text-gray-800 mt-2">
                                    KSh <?php echo number_format($order['total_amount'], 2); ?>
                                </p>
                            </div>
                        </div>

                        <div class="border-t pt-4">
                            <div class="flex justify-between text-sm text-gray-600 mb-2">
                                <span>Items: <?php echo htmlspecialchars($order['item_count'] ?? 0); ?></span>
                                <span>Payment:
                                    <span class="font-semibold 
                                        <?php echo $order['payment_status'] === 'paid' ? 'text-green-600' : 'text-red-600'; ?>">
                                        <?php echo ucfirst(htmlspecialchars($order['payment_status'])); ?>
                                    </span>
                                </span>
                            </div>

                            <!-- Order Actions -->
                            <div class="flex justify-end space-x-4 mt-4">
                                <a href="order_details.php?id=<?php echo $order['id']; ?>"
                                    class="text-blue-600 hover:text-blue-800 font-medium">
                                    <i class="fas fa-eye mr-1"></i>View Details
                                </a>
                                <?php if ($order['status'] === 'cancelled'): ?>
                                    <form method="POST" action="" class="inline" onsubmit="return confirm('Are you sure you want to permanently delete this order? This action cannot be undone.');">
                                        <input type="hidden" name="action" value="delete_order">
                                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                        <button type="submit"
                                            class="text-red-600 hover:text-red-800 font-medium">
                                            <i class="fas fa-trash mr-1"></i>Delete
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Include Footer -->
    <?php include '../../views/includes/footer.php'; ?>
</body>

</html>