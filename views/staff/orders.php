<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../controllers/AuthController.php';
require_once __DIR__ . '/../../controllers/OrderController.php';

$authController = new AuthController();
$authController->requireRole(['admin', 'staff']);

$orderController = new OrderController();
$orders = $orderController->getAllOrders();

// Ensure $orders is always an array
$orders = is_array($orders) ? $orders : [];

// Handle status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id']) && isset($_POST['status'])) {
    $order_id = intval($_POST['order_id']);
    $status = trim($_POST['status']);

    // Validate status
    $allowed_statuses = ['pending', 'processing', 'completed', 'cancelled'];
    if (in_array($status, $allowed_statuses)) {
        $result = $orderController->updateOrderStatus($order_id, $status);
        if ($result) {
            $_SESSION['success_message'] = "Order #{$order_id} status updated to " . ucfirst($status);
        } else {
            $_SESSION['error_message'] = "Failed to update order status";
        }
    } else {
        $_SESSION['error_message'] = "Invalid status selected";
    }

    header('Location: orders.php');
    exit();
}

// Display messages
if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

if (isset($_SESSION['error_message'])) {
    $error_message = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Management - <?php echo SITE_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100">
    <div class="flex">
        <!-- Sidebar -->
        <?php include '../includes/staff_sidebar.php'; ?>

        <!-- Main Content -->
        <div class="flex-1 ml-64 p-8">
            <div class="mb-6">
                <h1 class="text-3xl font-bold text-gray-800">Order Management</h1>
                <p class="text-gray-600">Manage and track customer orders</p>
            </div>

            <!-- Success/Error Messages -->
            <?php if (isset($success_message)): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($error_message)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <!-- Order Stats -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <?php
                $statusCounts = [
                    'pending' => 0,
                    'processing' => 0,
                    'completed' => 0,
                    'cancelled' => 0
                ];

                foreach ($orders as $order) {
                    // Safe array access
                    $status = $order['status'] ?? 'pending';
                    if (isset($statusCounts[$status])) {
                        $statusCounts[$status]++;
                    }
                }
                ?>

                <div class="bg-white rounded-lg shadow p-4">
                    <div class="flex items-center">
                        <div class="p-2 bg-yellow-100 rounded-lg">
                            <i class="fas fa-clock text-yellow-600"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-gray-600">Pending</p>
                            <h3 class="text-lg font-bold"><?php echo $statusCounts['pending']; ?></h3>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-4">
                    <div class="flex items-center">
                        <div class="p-2 bg-blue-100 rounded-lg">
                            <i class="fas fa-cog text-blue-600"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-gray-600">Processing</p>
                            <h3 class="text-lg font-bold"><?php echo $statusCounts['processing']; ?></h3>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-4">
                    <div class="flex items-center">
                        <div class="p-2 bg-green-100 rounded-lg">
                            <i class="fas fa-check text-green-600"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-gray-600">Completed</p>
                            <h3 class="text-lg font-bold"><?php echo $statusCounts['completed']; ?></h3>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-4">
                    <div class="flex items-center">
                        <div class="p-2 bg-red-100 rounded-lg">
                            <i class="fas fa-times text-red-600"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-gray-600">Cancelled</p>
                            <h3 class="text-lg font-bold"><?php echo $statusCounts['cancelled']; ?></h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Orders Table -->
            <div class="bg-white rounded-lg shadow">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-xl font-semibold text-gray-800">All Orders</h2>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php if (empty($orders)): ?>
                                <tr>
                                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                        No orders found.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($orders as $index => $order):
                                    // EXTREMELY SAFE array access with validation
                                    if (!is_array($order)) {
                                        continue; // Skip non-array elements
                                    }

                                    // Check for empty keys and provide defaults
                                    $orderId = isset($order['id']) && $order['id'] !== '' ? intval($order['id']) : 0;
                                    $customerName = isset($order['customer_name']) && $order['customer_name'] !== '' ?
                                        htmlspecialchars($order['customer_name']) : 'Unknown Customer';
                                    $customerEmail = isset($order['customer_email']) && $order['customer_email'] !== '' ?
                                        htmlspecialchars($order['customer_email']) : 'No email';
                                    $totalAmount = isset($order['total_amount']) ? floatval($order['total_amount']) : 0;
                                    $status = isset($order['status']) && $order['status'] !== '' ? $order['status'] : 'pending';
                                    $createdAt = isset($order['created_at']) && $order['created_at'] !== '' ?
                                        $order['created_at'] : date('Y-m-d H:i:s');
                                ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">#<?php echo $orderId; ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900"><?php echo $customerName; ?></div>
                                            <div class="text-sm text-gray-500"><?php echo $customerEmail; ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">Ksh <?php echo number_format($totalAmount, 2); ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                <?php echo match ($status) {
                                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                                    'processing' => 'bg-blue-100 text-blue-800',
                                                    'completed' => 'bg-green-100 text-green-800',
                                                    'cancelled' => 'bg-red-100 text-red-800',
                                                    default => 'bg-gray-100 text-gray-800'
                                                }; ?>">
                                                <?php echo ucfirst($status); ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo date('M j, Y g:i A', strtotime($createdAt)); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <a href="<?php echo BASE_URL; ?>/views/staff/order_details.php?id=<?php echo $orderId; ?>"
                                                class="text-blue-600 hover:text-blue-900 mr-3">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                            <?php if ($orderId > 0): ?>
                                                <div class="inline-block relative">
                                                    <select onchange="updateOrderStatus(<?php echo $orderId; ?>, this.value)"
                                                        class="text-sm border rounded px-2 py-1 
                                                            <?php echo match ($status) {
                                                                'pending' => 'border-yellow-300 bg-yellow-50',
                                                                'processing' => 'border-blue-300 bg-blue-50',
                                                                'completed' => 'border-green-300 bg-green-50',
                                                                'cancelled' => 'border-red-300 bg-red-50',
                                                                default => 'border-gray-300'
                                                            }; ?>">
                                                        <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                        <option value="processing" <?php echo $status === 'processing' ? 'selected' : ''; ?>>Processing</option>
                                                        <option value="completed" <?php echo $status === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                                        <option value="cancelled" <?php echo $status === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                                    </select>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        function updateOrderStatus(orderId, status) {
            if (!orderId || orderId <= 0 || !status) {
                alert('Error: Invalid order ID or status');
                return;
            }

            if (!confirm('Are you sure you want to update order #' + orderId + ' to "' + status + '"?')) {
                // Reset the select to original value
                event.target.value = event.target.getAttribute('data-original-value');
                return;
            }

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'orders.php';

            const orderIdInput = document.createElement('input');
            orderIdInput.type = 'hidden';
            orderIdInput.name = 'order_id';
            orderIdInput.value = orderId;

            const statusInput = document.createElement('input');
            statusInput.type = 'hidden';
            statusInput.name = 'status';
            statusInput.value = status;

            form.appendChild(orderIdInput);
            form.appendChild(statusInput);
            document.body.appendChild(form);
            form.submit();
        }

        // Store original values for select elements
        document.addEventListener('DOMContentLoaded', function() {
            const selects = document.querySelectorAll('select');
            selects.forEach(select => {
                select.setAttribute('data-original-value', select.value);
            });
        });
    </script>
</body>

</html>