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

$order_id = $_GET['id'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmed - Lake Victoria Tilapia Depot</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="bg-gray-100">
    <!-- Include Navigation -->
    <?php include '../../views/includes/navbar.php'; ?>

    <div class="container mx-auto px-4 py-16">
        <div class="max-w-2xl mx-auto text-center">
            <div class="bg-white rounded-lg shadow-lg p-8">
                <!-- Success Icon -->
                <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-check text-green-600 text-3xl"></i>
                </div>

                <h1 class="text-3xl font-bold text-gray-800 mb-4">Order Confirmed!</h1>
                <p class="text-lg text-gray-600 mb-2">Thank you for your purchase.</p>
                <p class="text-gray-600 mb-6">Your order has been successfully placed and is being processed.</p>

                <div class="bg-gray-50 rounded-lg p-6 mb-6">
                    <p class="text-sm text-gray-600 mb-2">Order Number</p>
                    <p class="text-xl font-semibold text-gray-800">#<?php echo htmlspecialchars($order_id); ?></p>
                </div>

                <div class="space-y-4 mb-8">
                    <p class="text-gray-600">
                        <i class="fas fa-envelope text-blue-600 mr-2"></i>
                        A confirmation email has been sent to your email address.
                    </p>
                    <p class="text-gray-600">
                        <i class="fas fa-phone text-blue-600 mr-2"></i>
                        We'll contact you shortly to confirm delivery details.
                    </p>
                </div>

                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="my_orders.php"
                        class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition duration-200">
                        <i class="fas fa-history mr-2"></i>View My Orders
                    </a>
                    <a href="browse_fish.php"
                        class="bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 transition duration-200">
                        <i class="fas fa-shopping-cart mr-2"></i>Continue Shopping
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Include Footer -->
    <?php include '../../views/includes/footer.php'; ?>
</body>

</html>