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
require_once '../../config/config.php';
require_once '../../models/Cart.php';
require_once '../../models/Order.php';
require_once '../../models/User.php';
require_once '../../controllers/OrderController.php';
require_once '../../controllers/MpesaController.php';

// Initialize variables
$error = '';
$success = '';
$cartItems = [];
$cartTotal = 0;
$userData = [];
$order_id = null;

try {
    // Initialize database connection
    $database = new Database();
    $db = $database->getConnection();

    // Get cart items
    $cart = new Cart($db);
    $cartItems = $cart->getCartItems($_SESSION['user_id']);
    $cartTotal = $cart->getCartTotal($_SESSION['user_id']);

    // Check if cart is empty
    if (empty($cartItems)) {
        header('Location: cart.php');
        exit();
    }

    // Get user data for pre-filling form
    $user = new User($db);
    $userData = $user->getUserById($_SESSION['user_id']);

    // Calculate shipping FIRST - before any usage
    $shippingFee = $cartTotal >= 1000 ? 0 : 200;
    $grandTotal = $cartTotal + $shippingFee;

    // Initialize controllers
    $orderController = new OrderController();
    $mpesaController = new MpesaController();

    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $shipping_address = trim($_POST['shipping_address']);
        $phone = trim($_POST['phone']);
        $notes = trim($_POST['notes'] ?? '');
        $payment_method = trim($_POST['payment_method'] ?? 'mpesa');

        if (empty($shipping_address) || empty($phone)) {
            $error = "Shipping address and phone number are required.";
        } else {
            // Create order data
            $orderData = [
                'customer_id' => $_SESSION['user_id'],
                'total_amount' => $cartTotal,
                'shipping_address' => $shipping_address,
                'phone' => $phone,
                'notes' => $notes
            ];

            // Create new order
            $order_id = $orderController->createOrder($orderData);

            if ($order_id) {
                // Add order items
                $success = true;
                foreach ($cartItems as $item) {
                    if (!$orderController->addOrderItem($order_id, $item['fish_id'], $item['quantity'], $item['price'])) {
                        $success = false;
                        break;
                    }
                }

                if ($success) {
                    // Process payment based on selected method
                    if ($payment_method === 'mpesa') {
                        // Process M-Pesa payment
                        $payment_result = $orderController->processPayment($order_id, $phone, $grandTotal);

                        if (isset($payment_result['success'])) {
                            // M-Pesa payment initiated successfully
                            $_SESSION['checkout_data'] = [
                                'order_id' => $order_id,
                                'checkout_request_id' => $payment_result['checkout_request_id'],
                                'phone' => $phone,
                                'amount' => $grandTotal
                            ];

                            header('Location: payment_processing.php?order_id=' . $order_id);
                            exit();
                        } else {
                            $error = "Payment initiation failed: " . ($payment_result['error'] ?? 'Unknown error');
                            // Mark order as failed
                            $orderController->updatePaymentStatus($order_id, 'failed');
                        }
                    } else {
                        // Cash on delivery
                        $orderController->updatePaymentStatus($order_id, 'pending');
                        $cart->clearCart($_SESSION['user_id']);
                        header('Location: order_success.php?id=' . $order_id . '&payment_method=cod');
                        exit();
                    }
                } else {
                    $error = "Failed to create order items. Please try again.";
                }
            } else {
                $error = "Failed to create order. Please try again.";
            }
        }
    }
} catch (Exception $e) {
    $error = "Error: " . $e->getMessage();
}

// Calculate shipping (fallback - though it should already be calculated above)
if (!isset($grandTotal)) {
    $shippingFee = $cartTotal >= 1000 ? 0 : 200;
    $grandTotal = $cartTotal + $shippingFee;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Lake Victoria Tilapia Depot</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .product-image {
            width: 3rem;
            height: 3rem;
            object-fit: cover;
            border-radius: 0.375rem;
        }

        .image-placeholder {
            width: 3rem;
            height: 3rem;
            background-color: #f3f4f6;
            border-radius: 0.375rem;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #9ca3af;
        }
    </style>
</head>

<body class="bg-gray-100">
    <!-- Include Navigation -->
    <?php include '../../views/includes/navbar.php'; ?>

    <div class="container mx-auto px-4 py-8">
        <div class="max-w-6xl mx-auto">
            <h1 class="text-3xl font-bold text-gray-800 mb-8">Checkout</h1>

            <?php if ($error): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Checkout Form -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-6">
                        <i class="fas fa-truck mr-2"></i>Shipping & Payment Information
                    </h2>

                    <form method="POST" action="" id="checkoutForm">
                        <div class="space-y-4">
                            <div>
                                <label for="full_name" class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                                <input type="text" id="full_name"
                                    value="<?php echo htmlspecialchars($userData['full_name'] ?? ''); ?>"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50"
                                    readonly>
                            </div>

                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                <input type="email" id="email"
                                    value="<?php echo htmlspecialchars($userData['email'] ?? ''); ?>"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50"
                                    readonly>
                            </div>

                            <div>
                                <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">
                                    <i class="fas fa-phone mr-1"></i>Phone Number *
                                </label>
                                <input type="tel" id="phone" name="phone"
                                    value="<?php echo htmlspecialchars($userData['phone'] ?? ''); ?>"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    required
                                    placeholder="2547XXXXXXXX"
                                    pattern="254[0-9]{9}"
                                    title="Please enter a valid Kenyan phone number starting with 254">
                                <p class="text-xs text-gray-500 mt-1">Format: 2547XXXXXXXX (e.g., 254712345678)</p>
                            </div>

                            <div>
                                <label for="shipping_address" class="block text-sm font-medium text-gray-700 mb-1">
                                    <i class="fas fa-map-marker-alt mr-1"></i>Shipping Address *
                                </label>
                                <textarea id="shipping_address" name="shipping_address" rows="4"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    required
                                    placeholder="Enter your complete delivery address including street, building, and any landmarks"><?php echo htmlspecialchars($userData['address'] ?? ''); ?></textarea>
                                <p class="text-xs text-gray-500 mt-1">Please provide detailed address for accurate delivery</p>
                            </div>

                            <div>
                                <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">
                                    <i class="fas fa-sticky-note mr-1"></i>Order Notes (Optional)
                                </label>
                                <textarea id="notes" name="notes" rows="3"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    placeholder="Any special instructions for your order (delivery time preferences, etc.)"></textarea>
                            </div>

                            <!-- Payment Method -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-3">
                                    <i class="fas fa-credit-card mr-1"></i>Payment Method *
                                </label>
                                <div class="space-y-2">
                                    <div class="flex items-center">
                                        <input type="radio" id="mpesa" name="payment_method" value="mpesa" checked
                                            class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300">
                                        <label for="mpesa" class="ml-3 block text-sm font-medium text-gray-700">
                                            <i class="fab fa-mpesa text-green-600 mr-2"></i>
                                            M-Pesa Mobile Payment
                                        </label>
                                    </div>
                                    <div class="flex items-center">
                                        <input type="radio" id="cod" name="payment_method" value="cod"
                                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300">
                                        <label for="cod" class="ml-3 block text-sm font-medium text-gray-700">
                                            <i class="fas fa-money-bill-wave text-blue-600 mr-2"></i>
                                            Cash on Delivery
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Payment Instructions -->
                            <div id="mpesaInstructions" class="bg-green-50 border border-green-200 rounded-lg p-4">
                                <h4 class="font-semibold text-green-800 mb-2">
                                    <i class="fas fa-info-circle mr-2"></i>M-Pesa Payment Instructions
                                </h4>
                                <ol class="text-sm text-green-700 space-y-1 list-decimal list-inside">
                                    <li>Enter your M-Pesa registered phone number above</li>
                                    <li>Click "Pay with M-Pesa" to initiate payment</li>
                                    <li>Check your phone for STK push notification</li>
                                    <li>Enter your M-Pesa PIN to complete payment</li>
                                    <li>You will be redirected automatically upon success</li>
                                </ol>
                            </div>

                            <div id="codInstructions" class="bg-blue-50 border border-blue-200 rounded-lg p-4 hidden">
                                <h4 class="font-semibold text-blue-800 mb-2">
                                    <i class="fas fa-info-circle mr-2"></i>Cash on Delivery Instructions
                                </h4>
                                <ul class="text-sm text-blue-700 space-y-1">
                                    <li><i class="fas fa-check-circle mr-2"></i>Pay when your order is delivered</li>
                                    <li><i class="fas fa-check-circle mr-2"></i>Exact amount: KSh <?php echo number_format($grandTotal, 2); ?></li>
                                    <li><i class="fas fa-check-circle mr-2"></i>Have exact change ready for delivery</li>
                                </ul>
                            </div>
                        </div>

                        <div class="mt-8">
                            <button type="submit" id="submitBtn"
                                class="w-full bg-green-600 text-white py-3 px-6 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 font-semibold text-lg transition duration-200">
                                <i class="fas fa-lock mr-2"></i>
                                <span id="submitText">Pay with M-Pesa - KSh <?php echo number_format($grandTotal, 2); ?></span>
                            </button>
                            <p class="text-xs text-gray-500 text-center mt-2">
                                <i class="fas fa-shield-alt mr-1"></i>
                                Your payment details are secure and encrypted
                            </p>
                        </div>
                    </form>
                </div>

                <!-- Order Summary -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-6">
                        <i class="fas fa-receipt mr-2"></i>Order Summary
                    </h2>

                    <div class="space-y-4 mb-6 max-h-96 overflow-y-auto">
                        <?php foreach ($cartItems as $item):
                            $itemName = htmlspecialchars($item['name'] ?? 'Product');
                            $itemPrice = $item['price'] ?? 0;
                            $itemQuantity = $item['quantity'] ?? 0;
                            $itemImage = $item['image_url'] ?? '';
                            $itemTotal = $itemPrice * $itemQuantity;

                            // Check if image exists and is valid
                            $imagePath = '../../uploads/' . $itemImage;
                            $hasValidImage = !empty($itemImage) && file_exists($imagePath) && is_file($imagePath);
                        ?>
                            <div class="flex justify-between items-center border-b border-gray-200 pb-4">
                                <div class="flex items-center space-x-3">
                                    <?php if ($hasValidImage): ?>
                                        <img src="<?php echo $imagePath; ?>"
                                            alt="<?php echo $itemName; ?>"
                                            class="product-image"
                                            loading="lazy"
                                            onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                        <div class="image-placeholder" style="display: none;">
                                            <i class="fas fa-fish text-gray-400"></i>
                                        </div>
                                    <?php else: ?>
                                        <div class="image-placeholder">
                                            <i class="fas fa-fish text-gray-400"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div>
                                        <h3 class="font-medium text-gray-800 text-sm"><?php echo $itemName; ?></h3>
                                        <p class="text-xs text-gray-600">Qty: <?php echo $itemQuantity; ?></p>
                                        <p class="text-xs text-gray-600">KSh <?php echo number_format($itemPrice, 2); ?> each</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="font-semibold text-gray-800">
                                        KSh <?php echo number_format($itemTotal, 2); ?>
                                    </p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="border-t border-gray-200 pt-4 space-y-2">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Subtotal (<?php echo count($cartItems); ?> items)</span>
                            <span class="font-semibold">KSh <?php echo number_format($cartTotal, 2); ?></span>
                        </div>

                        <div class="flex justify-between">
                            <span class="text-gray-600">Shipping</span>
                            <span class="font-semibold">
                                <?php if ($shippingFee === 0): ?>
                                    <span class="text-green-600">Free</span>
                                <?php else: ?>
                                    KSh <?php echo number_format($shippingFee, 2); ?>
                                <?php endif; ?>
                            </span>
                        </div>

                        <?php if ($shippingFee === 0): ?>
                            <div class="bg-green-50 border border-green-200 rounded p-2">
                                <p class="text-green-700 text-xs text-center">
                                    <i class="fas fa-shipping-fast mr-1"></i>
                                    Free shipping applied
                                </p>
                            </div>
                        <?php endif; ?>

                        <div class="flex justify-between text-lg font-bold border-t pt-3">
                            <span>Total Amount</span>
                            <span class="text-green-600">KSh <?php echo number_format($grandTotal, 2); ?></span>
                        </div>
                    </div>

                    <!-- Delivery Information -->
                    <div class="mt-6 p-4 bg-blue-50 rounded-lg">
                        <h3 class="font-semibold text-blue-800 mb-2">
                            <i class="fas fa-info-circle mr-2"></i>Delivery Information
                        </h3>
                        <ul class="text-sm text-blue-600 space-y-1">
                            <li><i class="fas fa-check-circle mr-2"></i>Free delivery within Kisumu area</li>
                            <li><i class="fas fa-check-circle mr-2"></i>Orders processed within 24 hours</li>
                            <li><i class="fas fa-check-circle mr-2"></i>Fresh tilapia delivered on ice</li>
                            <li><i class="fas fa-check-circle mr-2"></i>Contact: +254 700 000 000</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Include Footer -->
    <?php include '../../views/includes/footer.php'; ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('checkoutForm');
            const submitBtn = document.getElementById('submitBtn');
            const submitText = document.getElementById('submitText');
            const paymentMethodRadios = document.querySelectorAll('input[name="payment_method"]');
            const mpesaInstructions = document.getElementById('mpesaInstructions');
            const codInstructions = document.getElementById('codInstructions');
            const phoneInput = document.getElementById('phone');

            // Handle image loading errors
            function handleImageErrors() {
                const images = document.querySelectorAll('.product-image');
                images.forEach(img => {
                    img.onerror = function() {
                        this.style.display = 'none';
                        // Show the placeholder that's already in the HTML
                        const placeholder = this.nextElementSibling;
                        if (placeholder && placeholder.classList.contains('image-placeholder')) {
                            placeholder.style.display = 'flex';
                        }
                    };
                });
            }

            // Initialize image error handling
            handleImageErrors();

            // Format phone number
            phoneInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                if (value.startsWith('0')) {
                    value = '254' + value.substring(1);
                } else if (value.startsWith('7')) {
                    value = '254' + value;
                } else if (value.startsWith('+254')) {
                    value = value.substring(1);
                }
                e.target.value = value;
            });

            // Handle payment method changes
            paymentMethodRadios.forEach(radio => {
                radio.addEventListener('change', function() {
                    if (this.value === 'mpesa') {
                        mpesaInstructions.classList.remove('hidden');
                        codInstructions.classList.add('hidden');
                        submitText.textContent = `Pay with M-Pesa - KSh <?php echo number_format($grandTotal, 2); ?>`;
                        submitBtn.className = 'w-full bg-green-600 text-white py-3 px-6 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 font-semibold text-lg transition duration-200';
                    } else {
                        mpesaInstructions.classList.add('hidden');
                        codInstructions.classList.remove('hidden');
                        submitText.textContent = `Place Order (Cash on Delivery) - KSh <?php echo number_format($grandTotal, 2); ?>`;
                        submitBtn.className = 'w-full bg-blue-600 text-white py-3 px-6 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 font-semibold text-lg transition duration-200';
                    }
                });
            });

            // Form validation
            form.addEventListener('submit', function(e) {
                const phone = phoneInput.value.trim();
                const address = document.getElementById('shipping_address').value.trim();

                if (!phone || !address) {
                    e.preventDefault();
                    alert('Please fill in all required fields.');
                    return;
                }

                // Validate phone format
                const phoneRegex = /^254[0-9]{9}$/;
                if (!phoneRegex.test(phone)) {
                    e.preventDefault();
                    alert('Please enter a valid Kenyan phone number starting with 254 followed by 9 digits (e.g., 254712345678).');
                    return;
                }

                // Show loading state
                const paymentMethod = document.querySelector('input[name="payment_method"]:checked').value;
                if (paymentMethod === 'mpesa') {
                    submitText.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Initiating M-Pesa Payment...';
                } else {
                    submitText.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Placing Order...';
                }
                submitBtn.disabled = true;
            });
        });

        // Global error handler for any uncaught image errors
        window.addEventListener('error', function(e) {
            if (e.target.tagName === 'IMG') {
                e.target.style.display = 'none';
                const placeholder = e.target.nextElementSibling;
                if (placeholder && placeholder.classList.contains('image-placeholder')) {
                    placeholder.style.display = 'flex';
                }
                e.preventDefault();
            }
        }, true);
    </script>
</body>

</html>