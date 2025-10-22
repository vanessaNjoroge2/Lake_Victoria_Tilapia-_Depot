<?php
require_once '../../config/config.php';
require_once '../../controllers/AuthController.php';
require_once '../../controllers/CartController.php';

$authController = new AuthController();
$authController->requireAuth();

$cartController = new CartController();
$cartItems = $cartController->getCartItems($_SESSION['user_id']);
$cartTotal = $cartController->getCartTotal($_SESSION['user_id']);

// Calculate shipping (free over 1000, otherwise 200)
$shippingFee = $cartTotal >= 1000 ? 0 : 200;
$grandTotal = $cartTotal + $shippingFee;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - <?php echo SITE_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .product-image {
            width: 5rem;
            height: 5rem;
            object-fit: cover;
            border-radius: 0.375rem;
        }

        .image-placeholder {
            width: 5rem;
            height: 5rem;
            background-color: #f3f4f6;
            border-radius: 0.375rem;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #9ca3af;
            font-size: 1.5rem;
        }

        .image-container {
            position: relative;
            flex-shrink: 0;
        }
    </style>
</head>

<body class="bg-gray-50">
    <?php include '../../views/includes/navbar.php'; ?>

    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-8">Shopping Cart</h1>

        <?php if (empty($cartItems)): ?>
            <div class="bg-white rounded-lg shadow p-8 text-center">
                <i class="fas fa-shopping-cart text-gray-400 text-6xl mb-4"></i>
                <h2 class="text-2xl font-semibold text-gray-600 mb-2">Your cart is empty</h2>
                <p class="text-gray-500 mb-6">Start adding some delicious fish to your cart!</p>
                <a href="<?php echo BASE_URL; ?>/views/customer/browse_fish.php" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition duration-200">
                    Browse Fish
                </a>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Cart Items -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-lg shadow">
                        <?php foreach ($cartItems as $item):
                            // Safe array access with defaults
                            $itemId = $item['id'] ?? 0;
                            $fishId = $item['fish_id'] ?? 0;
                            $name = htmlspecialchars($item['name'] ?? 'Product');
                            $weightRange = htmlspecialchars($item['weight_range'] ?? 'Not specified');
                            $price = $item['price'] ?? 0;
                            $quantity = $item['quantity'] ?? 0;
                            $imageUrl = $item['image_url'] ?? '';
                            $stockQuantity = $item['stock_quantity'] ?? 0;
                            $itemTotal = $price * $quantity;

                            // Check if image exists and is valid
                            $imagePath = '../../uploads/' . $imageUrl;
                            $hasValidImage = !empty($imageUrl) && file_exists($imagePath) && is_file($imagePath);
                            $imageSrc = $hasValidImage ? BASE_URL . '/uploads/' . $imageUrl : '';
                        ?>
                            <div class="p-6 border-b border-gray-200 last:border-b-0">
                                <div class="flex flex-col md:flex-row gap-4">
                                    <!-- Product Image -->
                                    <div class="image-container">
                                        <?php if ($hasValidImage): ?>
                                            <img src="<?php echo $imageSrc; ?>"
                                                alt="<?php echo $name; ?>"
                                                class="product-image"
                                                loading="lazy"
                                                onerror="handleImageError(this)">
                                            <div class="image-placeholder" style="display: none;">
                                                <i class="fas fa-fish"></i>
                                            </div>
                                        <?php else: ?>
                                            <div class="image-placeholder">
                                                <i class="fas fa-fish"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Product Details -->
                                    <div class="flex-1 min-w-0">
                                        <h3 class="text-lg font-semibold text-gray-800 truncate"><?php echo $name; ?></h3>
                                        <p class="text-gray-600 text-sm"><?php echo $weightRange; ?></p>
                                        <p class="text-blue-600 font-semibold">Ksh <?php echo number_format($price, 2); ?></p>
                                        <p class="text-sm text-gray-500 mt-1">In stock: <?php echo $stockQuantity; ?></p>

                                        <?php if ($quantity > $stockQuantity): ?>
                                            <p class="text-red-600 text-sm mt-1">
                                                <i class="fas fa-exclamation-triangle mr-1"></i>
                                                Only <?php echo $stockQuantity; ?> available
                                            </p>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Quantity Controls -->
                                    <div class="flex items-center gap-4">
                                        <div class="flex items-center gap-2">
                                            <button type="button"
                                                onclick="updateQuantity(<?php echo $fishId; ?>, -1)"
                                                class="bg-gray-200 text-gray-700 w-8 h-8 rounded-full flex items-center justify-center hover:bg-gray-300 transition duration-200 <?php echo $quantity <= 1 ? 'opacity-50 cursor-not-allowed' : ''; ?>"
                                                <?php echo $quantity <= 1 ? 'disabled' : ''; ?>>
                                                <i class="fas fa-minus text-xs"></i>
                                            </button>

                                            <span class="px-3 py-1 border rounded min-w-12 text-center">
                                                <?php echo $quantity; ?>
                                            </span>

                                            <button type="button"
                                                onclick="updateQuantity(<?php echo $fishId; ?>, 1)"
                                                class="bg-gray-200 text-gray-700 w-8 h-8 rounded-full flex items-center justify-center hover:bg-gray-300 transition duration-200 <?php echo $quantity >= $stockQuantity ? 'opacity-50 cursor-not-allowed' : ''; ?>"
                                                <?php echo $quantity >= $stockQuantity ? 'disabled' : ''; ?>>
                                                <i class="fas fa-plus text-xs"></i>
                                            </button>
                                        </div>

                                        <!-- Item Total -->
                                        <div class="text-right min-w-20">
                                            <p class="font-semibold text-gray-800">
                                                Ksh <?php echo number_format($itemTotal, 2); ?>
                                            </p>
                                        </div>

                                        <!-- Remove Button -->
                                        <form action="<?php echo BASE_URL; ?>/handlers/cart_handler.php" method="POST" class="flex-shrink-0">
                                            <input type="hidden" name="fish_id" value="<?php echo $fishId; ?>">
                                            <input type="hidden" name="action" value="remove">
                                            <button type="submit"
                                                class="text-red-600 hover:text-red-800 transition duration-200 p-2 rounded hover:bg-red-50"
                                                onclick="return confirm('Are you sure you want to remove this item from your cart?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Clear Cart Button -->
                    <div class="mt-4 text-right">
                        <form action="<?php echo BASE_URL; ?>/handlers/cart_handler.php" method="POST" class="inline-block">
                            <input type="hidden" name="action" value="clear">
                            <button type="submit"
                                class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition duration-200"
                                onclick="return confirm('Are you sure you want to clear your entire cart?')">
                                <i class="fas fa-trash-alt mr-2"></i>Clear Cart
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-lg shadow p-6 sticky top-4">
                        <h2 class="text-xl font-semibold text-gray-800 mb-4">Order Summary</h2>

                        <div class="space-y-3 mb-6">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Subtotal (<?php echo count($cartItems); ?> items)</span>
                                <span class="font-semibold">Ksh <?php echo number_format($cartTotal, 2); ?></span>
                            </div>

                            <div class="flex justify-between">
                                <span class="text-gray-600">Shipping</span>
                                <span class="font-semibold">
                                    <?php if ($shippingFee === 0): ?>
                                        <span class="text-green-600">Free</span>
                                    <?php else: ?>
                                        Ksh <?php echo number_format($shippingFee, 2); ?>
                                    <?php endif; ?>
                                </span>
                            </div>

                            <?php if ($shippingFee === 0): ?>
                                <div class="bg-green-50 border border-green-200 rounded p-3">
                                    <p class="text-green-700 text-sm text-center">
                                        <i class="fas fa-shipping-fast mr-1"></i>
                                        Free shipping on orders over Ksh 1,000
                                    </p>
                                </div>
                            <?php else: ?>
                                <div class="bg-blue-50 border border-blue-200 rounded p-3">
                                    <p class="text-blue-700 text-sm text-center">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        Add Ksh <?php echo number_format(1000 - $cartTotal, 2); ?> more for free shipping
                                    </p>
                                </div>
                            <?php endif; ?>

                            <div class="flex justify-between text-lg font-bold border-t pt-3">
                                <span>Total</span>
                                <span>Ksh <?php echo number_format($grandTotal, 2); ?></span>
                            </div>
                        </div>

                        <!-- Checkout Button -->
                        <a href="<?php echo BASE_URL; ?>/views/customer/checkout.php"
                            class="w-full bg-green-600 text-white py-3 rounded-lg hover:bg-green-700 transition duration-200 text-center block font-semibold mb-3">
                            <i class="fas fa-credit-card mr-2"></i>Proceed to Checkout
                        </a>

                        <!-- Continue Shopping -->
                        <a href="<?php echo BASE_URL; ?>/views/customer/browse_fish.php"
                            class="w-full bg-gray-200 text-gray-800 py-3 rounded-lg hover:bg-gray-300 transition duration-200 text-center block">
                            <i class="fas fa-arrow-left mr-2"></i>Continue Shopping
                        </a>

                        <!-- Security Features -->
                        <div class="mt-4 pt-4 border-t border-gray-200">
                            <div class="flex items-center justify-center space-x-4 text-sm text-gray-600">
                                <div class="flex items-center">
                                    <i class="fas fa-shield-alt text-green-600 mr-1"></i>
                                    <span>Secure Checkout</span>
                                </div>
                                <div class="flex items-center">
                                    <i class="fas fa-truck text-blue-600 mr-1"></i>
                                    <span>Free Delivery</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Image error handling function
        function handleImageError(img) {
            img.style.display = 'none';
            const placeholder = img.nextElementSibling;
            if (placeholder && placeholder.classList.contains('image-placeholder')) {
                placeholder.style.display = 'flex';
            }
        }

        function updateQuantity(fishId, change) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '<?php echo BASE_URL; ?>/handlers/cart_handler.php';

            const fishIdInput = document.createElement('input');
            fishIdInput.type = 'hidden';
            fishIdInput.name = 'fish_id';
            fishIdInput.value = fishId;

            const actionInput = document.createElement('input');
            actionInput.type = 'hidden';
            actionInput.name = 'action';
            actionInput.value = 'update';

            const quantityInput = document.createElement('input');
            quantityInput.type = 'hidden';
            quantityInput.name = 'quantity';
            quantityInput.value = change;

            form.appendChild(fishIdInput);
            form.appendChild(actionInput);
            form.appendChild(quantityInput);
            document.body.appendChild(form);
            form.submit();
        }

        // Add smooth animations and image preloading
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize all images with error handling
            const images = document.querySelectorAll('img[src]');
            images.forEach(img => {
                // Preload image to check if it loads properly
                const tempImage = new Image();
                tempImage.src = img.src;
                tempImage.onerror = function() {
                    handleImageError(img);
                };

                // Also set the onerror handler for the actual image
                img.onerror = function() {
                    handleImageError(this);
                };
            });

            // Add loading states to buttons (improved version)
            const buttons = document.querySelectorAll('button[type="submit"], a[href*="checkout"]');
            buttons.forEach(button => {
                button.addEventListener('click', function(e) {
                    if (!this.classList.contains('processing')) {
                        this.classList.add('processing', 'opacity-75', 'cursor-wait');
                        const originalHTML = this.innerHTML;

                        if (this.type === 'submit' || this.href.includes('checkout')) {
                            this.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Processing...';

                            // Reset after 3 seconds if still on same page
                            setTimeout(() => {
                                if (this.classList.contains('processing')) {
                                    this.innerHTML = originalHTML;
                                    this.classList.remove('processing', 'opacity-75', 'cursor-wait');
                                }
                            }, 3000);
                        }
                    }
                });
            });
        });

        // Global error handler for any uncaught image errors
        window.addEventListener('error', function(e) {
            if (e.target.tagName === 'IMG') {
                handleImageError(e.target);
                e.preventDefault();
            }
        }, true);
    </script>

    <?php include '../../views/includes/footer.php'; ?>
</body>

</html>