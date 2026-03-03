<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../controllers/CartController.php';

$cartController = new CartController();
$cartItemCount = $cartController->getCartItemCount($_SESSION['user_id'] ?? 0);

// Determine base path for links based on current directory
$isCustomerView = strpos($_SERVER['PHP_SELF'], '/customer/') !== false;
$basePath = $isCustomerView ? '' : 'customer/';
?>

<nav class="bg-white shadow-lg">
    <div class="container mx-auto px-4">
        <div class="flex justify-between items-center py-4">
            <!-- Logo -->
            <div class="flex items-center">
                <a href="<?php echo BASE_URL; ?>/landing.php" class="flex items-center space-x-2">
                    <div class="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center">
                        <i class="fas fa-fish text-white"></i>
                    </div>
                    <span class="text-xl font-bold text-gray-800">Lake Victoria Tilapia</span>
                </a>
            </div>

            <!-- Navigation Links -->
            <div class="hidden md:flex items-center space-x-8">
                <a href="<?php echo $basePath; ?>browse_fish.php" class="text-gray-600 hover:text-blue-600 font-medium">Browse Fish</a>
                <a href="<?php echo $basePath; ?>my_orders.php" class="text-gray-600 hover:text-blue-600 font-medium">My Orders</a>
                <a href="<?php echo $basePath; ?>profile.php" class="text-gray-600 hover:text-blue-600 font-medium">Profile</a>

                <!-- Cart -->
                <a href="<?php echo $basePath; ?>cart.php" class="relative text-gray-600 hover:text-blue-600">
                    <i class="fas fa-shopping-cart text-xl"></i>
                    <?php if ($cartItemCount > 0): ?>
                        <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                            <?php echo $cartItemCount; ?>
                        </span>
                    <?php endif; ?>
                </a>

                <!-- User Dropdown -->
                <div class="relative group">
                    <button class="flex items-center space-x-2 text-gray-600 hover:text-blue-600">
                        <i class="fas fa-user-circle text-xl"></i>
                        <span><?php echo $_SESSION['full_name'] ?? 'User'; ?></span>
                        <i class="fas fa-chevron-down text-xs"></i>
                    </button>

                    <div class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-2 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 z-50">
                        <a href="<?php echo $basePath; ?>profile.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            <i class="fas fa-user mr-2"></i>Profile
                        </a>
                        <a href="<?php echo $basePath; ?>my_orders.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            <i class="fas fa-history mr-2"></i>Order History
                        </a>
                        <div class="border-t border-gray-100"></div>
                        <a href="<?php echo BASE_URL; ?>/views/auth/logout.php"
                            class="block px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
                            <i class="fas fa-sign-out-alt mr-2"></i>Logout
                        </a>
                    </div>
                </div>
            </div>

            <!-- Mobile menu button -->
            <div class="md:hidden">
                <button id="mobile-menu-button" class="text-gray-600 hover:text-blue-600">
                    <i class="fas fa-bars text-xl"></i>
                </button>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div id="mobile-menu" class="md:hidden hidden pb-4 border-t border-gray-200">
            <a href="<?php echo $basePath; ?>browse_fish.php" class="block py-3 px-4 text-gray-600 hover:text-blue-600 hover:bg-gray-50">Browse Fish</a>
            <a href="<?php echo $basePath; ?>my_orders.php" class="block py-3 px-4 text-gray-600 hover:text-blue-600 hover:bg-gray-50">My Orders</a>
            <a href="<?php echo $basePath; ?>profile.php" class="block py-3 px-4 text-gray-600 hover:text-blue-600 hover:bg-gray-50">Profile</a>
            <a href="<?php echo $basePath; ?>cart.php" class="block py-3 px-4 text-gray-600 hover:text-blue-600 hover:bg-gray-50 flex items-center">
                Shopping Cart
                <?php if ($cartItemCount > 0): ?>
                    <span class="ml-2 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                        <?php echo $cartItemCount; ?>
                    </span>
                <?php endif; ?>
            </a>
            <a href="<?php echo BASE_URL; ?>/views/auth/logout.php" class="block py-3 px-4 text-red-600 hover:text-red-800 hover:bg-gray-50 border-t border-gray-200">
                <i class="fas fa-sign-out-alt mr-2"></i>Logout
            </a>
        </div>
    </div>
</nav>

<script>
    document.getElementById('mobile-menu-button').addEventListener('click', function() {
        const menu = document.getElementById('mobile-menu');
        menu.classList.toggle('hidden');
    });

    // Close mobile menu when clicking outside
    document.addEventListener('click', function(event) {
        const menu = document.getElementById('mobile-menu');
        const button = document.getElementById('mobile-menu-button');

        if (!menu.contains(event.target) && !button.contains(event.target)) {
            menu.classList.add('hidden');
        }
    });
</script>