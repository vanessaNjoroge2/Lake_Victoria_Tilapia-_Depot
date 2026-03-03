<?php

/**
 * Products Page - Lake Victoria Tilapia Depot
 * Public-facing product showcase (no login required)
 */
require_once '../../config/config.php';
require_once '../../controllers/FishController.php';

$fishController = new FishController();
$fish = $fishController->getActiveFish();
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Products - <?php echo SITE_NAME; ?></title>
    <meta name="description" content="Browse our selection of fresh tilapia from Lake Victoria. Available in small, medium, and large sizes.">

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        * {
            font-family: 'Poppins', sans-serif;
        }

        .product-card {
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .product-card:hover {
            transform: translateY(-15px);
        }
    </style>
</head>

<body class="bg-gray-50">

    <!-- Navigation -->
    <nav class="bg-white shadow-md sticky top-0 z-50">
        <div class="container mx-auto px-4 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <a href="<?php echo BASE_URL; ?>" class="flex items-center space-x-3">
                    <img src="<?php echo BASE_URL; ?>/uploads/fresh_tilapia_logo.jpg" alt="Logo" class="w-12 h-12 rounded-full object-cover shadow-md">
                    <div>
                        <h1 class="text-xl font-bold text-cyan-600">Lake Victoria</h1>
                        <p class="text-xs text-gray-600">Tilapia Depot</p>
                    </div>
                </a>

                <div class="hidden lg:flex items-center space-x-6">
                    <a href="<?php echo BASE_URL; ?>" class="text-gray-700 hover:text-cyan-600 font-medium transition">
                        <i class="fas fa-home mr-1"></i> Home
                    </a>
                    <a href="<?php echo BASE_URL; ?>/views/public/about.php" class="text-gray-700 hover:text-cyan-600 font-medium transition">
                        <i class="fas fa-info-circle mr-1"></i> About Us
                    </a>
                    <a href="<?php echo BASE_URL; ?>/views/public/services.php" class="text-gray-700 hover:text-cyan-600 font-medium transition">
                        <i class="fas fa-concierge-bell mr-1"></i> Services
                    </a>
                    <a href="<?php echo BASE_URL; ?>/views/public/products.php" class="text-cyan-600 font-bold border-b-2 border-cyan-600">
                        <i class="fas fa-fish mr-1"></i> Products
                    </a>
                    <a href="<?php echo BASE_URL; ?>/views/public/contact.php" class="text-gray-700 hover:text-cyan-600 font-medium transition">
                        <i class="fas fa-envelope mr-1"></i> Contact
                    </a>
                    <a href="<?php echo BASE_URL; ?>/views/auth/login.php" class="bg-gradient-to-r from-cyan-500 to-blue-600 text-white px-6 py-2.5 rounded-full font-semibold hover:shadow-lg transition">
                        <i class="fas fa-sign-in-alt mr-2"></i>Login
                    </a>
                </div>

                <button onclick="toggleMobileMenu()" class="lg:hidden text-gray-700">
                    <i class="fas fa-bars text-2xl"></i>
                </button>
            </div>

            <!-- Mobile Menu -->
            <div id="mobileMenu" class="hidden lg:hidden pb-4">
                <a href="<?php echo BASE_URL; ?>" class="block py-2 text-gray-700 hover:text-cyan-600">
                    <i class="fas fa-home mr-2"></i> Home
                </a>
                <a href="<?php echo BASE_URL; ?>/views/public/about.php" class="block py-2 text-gray-700 hover:text-cyan-600">
                    <i class="fas fa-info-circle mr-2"></i> About Us
                </a>
                <a href="<?php echo BASE_URL; ?>/views/public/services.php" class="block py-2 text-gray-700 hover:text-cyan-600">
                    <i class="fas fa-concierge-bell mr-2"></i> Services
                </a>
                <a href="<?php echo BASE_URL; ?>/views/public/products.php" class="block py-2 text-cyan-600 font-bold">
                    <i class="fas fa-fish mr-2"></i> Products
                </a>
                <a href="<?php echo BASE_URL; ?>/views/public/contact.php" class="block py-2 text-gray-700 hover:text-cyan-600">
                    <i class="fas fa-envelope mr-2"></i> Contact
                </a>
                <a href="<?php echo BASE_URL; ?>/views/auth/login.php" class="block mt-2 bg-gradient-to-r from-cyan-500 to-blue-600 text-white px-6 py-2.5 rounded-full font-semibold text-center">
                    <i class="fas fa-sign-in-alt mr-2"></i>Login
                </a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="bg-gradient-to-r from-cyan-600 to-blue-700 text-white py-20">
        <div class="container mx-auto px-4 lg:px-8 text-center">
            <h1 class="text-5xl lg:text-6xl font-bold mb-6">Our Products</h1>
            <p class="text-xl lg:text-2xl text-blue-50 max-w-3xl mx-auto">
                Premium quality tilapia fresh from Lake Victoria
            </p>
            <p class="text-lg text-blue-100 mt-4">
                <i class="fas fa-check-circle mr-2"></i>Fresh Daily
                <i class="fas fa-check-circle mx-4"></i>Quality Guaranteed
                <i class="fas fa-check-circle mx-2"></i>Competitively Priced
            </p>
        </div>
    </section>

    <!-- Featured Products -->
    <section class="py-20">
        <div class="container mx-auto px-4 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-4xl font-bold text-gray-800 mb-4">Featured Products</h2>
                <div class="w-24 h-1 bg-gradient-to-r from-cyan-500 to-blue-600 mx-auto"></div>
            </div>

            <div class="grid md:grid-cols-3 gap-8 max-w-6xl mx-auto mb-16">
                <!-- Small Tilapia -->
                <div class="product-card bg-gradient-to-br from-blue-50 to-cyan-50 rounded-3xl shadow-xl overflow-hidden">
                    <div class="relative h-64 overflow-hidden">
                        <img src="<?php echo BASE_URL; ?>/uploads/fresh_tilapia.jpg" alt="Small Tilapia" class="w-full h-full object-cover">
                        <div class="absolute top-4 right-4 bg-cyan-500 text-white px-4 py-2 rounded-full font-bold text-sm">
                            <i class="fas fa-star mr-1"></i>Popular
                        </div>
                        <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/70 to-transparent p-6">
                            <h3 class="text-white text-2xl font-bold">Small Tilapia</h3>
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-4">
                            <span class="inline-block bg-cyan-100 text-cyan-700 px-3 py-1 rounded-full text-sm font-semibold">
                                0.2 - 0.5 kg
                            </span>
                            <div class="text-cyan-600 text-2xl font-bold">Ksh 150-250</div>
                        </div>
                        <p class="text-gray-600 mb-4">
                            Perfect for individual meals and small families. Ideal for frying and grilling. Fresh catch every morning.
                        </p>
                        <ul class="space-y-2 mb-6 text-sm">
                            <li class="flex items-center text-gray-700">
                                <i class="fas fa-check text-cyan-500 mr-2"></i>
                                Best for 1-2 people
                            </li>
                            <li class="flex items-center text-gray-700">
                                <i class="fas fa-check text-cyan-500 mr-2"></i>
                                Quick cooking time
                            </li>
                            <li class="flex items-center text-gray-700">
                                <i class="fas fa-check text-cyan-500 mr-2"></i>
                                Great for frying whole
                            </li>
                        </ul>
                        <a href="<?php echo BASE_URL; ?>/views/auth/login.php" class="block text-center bg-gradient-to-r from-cyan-500 to-blue-600 text-white py-3 rounded-full font-bold hover:shadow-lg transition">
                            <i class="fas fa-shopping-cart mr-2"></i>Order Now
                        </a>
                    </div>
                </div>

                <!-- Medium Tilapia -->
                <div class="product-card bg-gradient-to-br from-emerald-50 to-teal-50 rounded-3xl shadow-xl overflow-hidden">
                    <div class="relative h-64 overflow-hidden">
                        <img src="<?php echo BASE_URL; ?>/uploads/68f8a9477ffe3_fish fillets.jpg" alt="Medium Tilapia" class="w-full h-full object-cover">
                        <div class="absolute top-4 right-4 bg-emerald-500 text-white px-4 py-2 rounded-full font-bold text-sm">
                            <i class="fas fa-trophy mr-1"></i>Best Value
                        </div>
                        <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/70 to-transparent p-6">
                            <h3 class="text-white text-2xl font-bold">Medium Tilapia</h3>
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-4">
                            <span class="inline-block bg-emerald-100 text-emerald-700 px-3 py-1 rounded-full text-sm font-semibold">
                                0.6 - 1.0 kg
                            </span>
                            <div class="text-emerald-600 text-2xl font-bold">Ksh 300-500</div>
                        </div>
                        <p class="text-gray-600 mb-4">
                            Most popular choice for family gatherings. Excellent for various cooking methods including grilling, frying, and baking.
                        </p>
                        <ul class="space-y-2 mb-6 text-sm">
                            <li class="flex items-center text-gray-700">
                                <i class="fas fa-check text-emerald-500 mr-2"></i>
                                Perfect for 3-4 people
                            </li>
                            <li class="flex items-center text-gray-700">
                                <i class="fas fa-check text-emerald-500 mr-2"></i>
                                Versatile cooking options
                            </li>
                            <li class="flex items-center text-gray-700">
                                <i class="fas fa-check text-emerald-500 mr-2"></i>
                                Excellent meat-to-bone ratio
                            </li>
                        </ul>
                        <a href="<?php echo BASE_URL; ?>/views/auth/login.php" class="block text-center bg-gradient-to-r from-emerald-500 to-teal-600 text-white py-3 rounded-full font-bold hover:shadow-lg transition">
                            <i class="fas fa-shopping-cart mr-2"></i>Order Now
                        </a>
                    </div>
                </div>

                <!-- Large Tilapia -->
                <div class="product-card bg-gradient-to-br from-purple-50 to-indigo-50 rounded-3xl shadow-xl overflow-hidden">
                    <div class="relative h-64 overflow-hidden">
                        <img src="<?php echo BASE_URL; ?>/uploads/68f8c98cdd35c_NilePerch.jpg" alt="Large Tilapia" class="w-full h-full object-cover">
                        <div class="absolute top-4 right-4 bg-purple-500 text-white px-4 py-2 rounded-full font-bold text-sm">
                            <i class="fas fa-crown mr-1"></i>Premium
                        </div>
                        <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/70 to-transparent p-6">
                            <h3 class="text-white text-2xl font-bold">Large Tilapia</h3>
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-4">
                            <span class="inline-block bg-purple-100 text-purple-700 px-3 py-1 rounded-full text-sm font-semibold">
                                1.1 - 2.0 kg
                            </span>
                            <div class="text-purple-600 text-2xl font-bold">Ksh 600-900</div>
                        </div>
                        <p class="text-gray-600 mb-4">
                            Premium choice for restaurants, hotels, and large events. Maximum flavor and impressive presentation.
                        </p>
                        <ul class="space-y-2 mb-6 text-sm">
                            <li class="flex items-center text-gray-700">
                                <i class="fas fa-check text-purple-500 mr-2"></i>
                                Ideal for 5+ people
                            </li>
                            <li class="flex items-center text-gray-700">
                                <i class="fas fa-check text-purple-500 mr-2"></i>
                                Restaurant quality
                            </li>
                            <li class="flex items-center text-gray-700">
                                <i class="fas fa-check text-purple-500 mr-2"></i>
                                Rich, full flavor
                            </li>
                        </ul>
                        <a href="<?php echo BASE_URL; ?>/views/auth/login.php" class="block text-center bg-gradient-to-r from-purple-500 to-indigo-600 text-white py-3 rounded-full font-bold hover:shadow-lg transition">
                            <i class="fas fa-shopping-cart mr-2"></i>Order Now
                        </a>
                    </div>
                </div>
            </div>

            <?php if (count($fish) > 0): ?>
                <!-- All Available Products -->
                <div class="mt-16">
                    <div class="text-center mb-12">
                        <h2 class="text-3xl font-bold text-gray-800 mb-4">All Available Products</h2>
                        <p class="text-gray-600">Currently in stock and ready for order</p>
                    </div>

                    <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <?php foreach ($fish as $item): ?>
                            <div class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-2xl transition">
                                <div class="relative h-48">
                                    <img src="<?php echo BASE_URL; ?>/<?php echo htmlspecialchars($item['image_url']); ?>"
                                        alt="<?php echo htmlspecialchars($item['name']); ?>"
                                        class="w-full h-full object-cover">
                                    <?php if ($item['stock_quantity'] < 10): ?>
                                        <div class="absolute top-2 right-2 bg-red-500 text-white px-3 py-1 rounded-full text-xs font-semibold">
                                            <i class="fas fa-fire mr-1"></i>Low Stock
                                        </div>
                                    <?php else: ?>
                                        <div class="absolute top-2 right-2 bg-green-500 text-white px-3 py-1 rounded-full text-xs font-semibold">
                                            <i class="fas fa-check-circle mr-1"></i>In Stock
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="p-4">
                                    <h3 class="font-bold text-lg mb-2 text-gray-800"><?php echo htmlspecialchars($item['name']); ?></h3>
                                    <p class="text-gray-600 text-sm mb-3 line-clamp-2"><?php echo htmlspecialchars($item['description']); ?></p>
                                    <div class="flex justify-between items-center mb-3">
                                        <span class="text-cyan-600 font-bold text-xl">Ksh <?php echo number_format($item['price'], 2); ?></span>
                                        <span class="text-gray-500 text-sm"><?php echo htmlspecialchars($item['category']); ?></span>
                                    </div>
                                    <a href="<?php echo BASE_URL; ?>/views/auth/login.php"
                                        class="block text-center bg-cyan-500 text-white py-2 rounded-lg font-semibold hover:bg-cyan-600 transition">
                                        <i class="fas fa-cart-plus mr-1"></i>Add to Cart
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Why Choose Us -->
    <section class="py-16 bg-gradient-to-br from-cyan-50 to-blue-50">
        <div class="container mx-auto px-4 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-800 mb-4">Why Choose Our Fish?</h2>
                <p class="text-gray-600">Quality and freshness guaranteed</p>
            </div>

            <div class="grid md:grid-cols-4 gap-6 max-w-5xl mx-auto">
                <div class="text-center bg-white p-6 rounded-xl shadow-lg">
                    <div class="bg-cyan-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-fish text-cyan-600 text-2xl"></i>
                    </div>
                    <h3 class="font-bold mb-2">Fresh Daily</h3>
                    <p class="text-sm text-gray-600">Caught fresh every morning from Lake Victoria</p>
                </div>

                <div class="text-center bg-white p-6 rounded-xl shadow-lg">
                    <div class="bg-emerald-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-award text-emerald-600 text-2xl"></i>
                    </div>
                    <h3 class="font-bold mb-2">Quality Checked</h3>
                    <p class="text-sm text-gray-600">Every fish inspected for quality and freshness</p>
                </div>

                <div class="text-center bg-white p-6 rounded-xl shadow-lg">
                    <div class="bg-purple-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-leaf text-purple-600 text-2xl"></i>
                    </div>
                    <h3 class="font-bold mb-2">Sustainable</h3>
                    <p class="text-sm text-gray-600">Supporting local fishermen and eco-friendly practices</p>
                </div>

                <div class="text-center bg-white p-6 rounded-xl shadow-lg">
                    <div class="bg-orange-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-tags text-orange-600 text-2xl"></i>
                    </div>
                    <h3 class="font-bold mb-2">Fair Prices</h3>
                    <p class="text-sm text-gray-600">Competitive pricing for retail and wholesale</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-16 bg-gradient-to-r from-cyan-600 to-blue-700 text-white">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-4xl font-bold mb-4">Ready to Order Fresh Fish?</h2>
            <p class="text-xl mb-8 text-blue-50">Login to place your order or contact us for bulk inquiries</p>
            <div class="flex flex-wrap justify-center gap-4">
                <a href="<?php echo BASE_URL; ?>/views/auth/login.php" class="bg-yellow-400 text-gray-900 px-8 py-4 rounded-full font-bold text-lg hover:bg-yellow-300 transform hover:scale-105 transition">
                    <i class="fas fa-sign-in-alt mr-2"></i>Login to Order
                </a>
                <a href="<?php echo BASE_URL; ?>/views/public/contact.php" class="bg-white/20 backdrop-blur-sm text-white border-2 border-white px-8 py-4 rounded-full font-bold text-lg hover:bg-white/30 transition">
                    <i class="fas fa-envelope mr-2"></i>Contact for Bulk Orders
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-12">
        <div class="container mx-auto px-4 lg:px-8">
            <div class="grid md:grid-cols-4 gap-8 mb-8">
                <div>
                    <h3 class="text-xl font-bold mb-4">Lake Victoria Tilapia</h3>
                    <p class="text-gray-400">Fresh tilapia from Lake Victoria, delivered with care and quality.</p>
                </div>
                <div>
                    <h4 class="font-bold mb-4">Quick Links</h4>
                    <ul class="space-y-2">
                        <li><a href="<?php echo BASE_URL; ?>" class="text-gray-400 hover:text-white transition">Home</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/views/public/about.php" class="text-gray-400 hover:text-white transition">About Us</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/views/public/services.php" class="text-gray-400 hover:text-white transition">Services</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/views/public/products.php" class="text-gray-400 hover:text-white transition">Products</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/views/public/contact.php" class="text-gray-400 hover:text-white transition">Contact</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-bold mb-4">Product Sizes</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li>Small (0.2-0.5 kg)</li>
                        <li>Medium (0.6-1.0 kg)</li>
                        <li>Large (1.1-2.0 kg)</li>
                        <li>Bulk Orders Available</li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-bold mb-4">Contact Us</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li><i class="fas fa-phone mr-2"></i><?php echo ADMIN_PHONE; ?></li>
                        <li><i class="fas fa-envelope mr-2"></i><?php echo ADMIN_EMAIL; ?></li>
                        <li><i class="fas fa-map-marker-alt mr-2"></i>Kisumu, Kenya</li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-800 pt-8 text-center text-gray-400">
                <p>&copy; <?php echo date('Y'); ?> Lake Victoria Tilapia Depot. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Floating WhatsApp -->
    <a href="https://wa.me/<?php echo str_replace('+', '', ADMIN_PHONE); ?>"
        class="fixed bottom-8 right-8 bg-green-500 text-white w-14 h-14 rounded-full shadow-lg hover:shadow-xl flex items-center justify-center z-50 hover:scale-110 transition">
        <i class="fab fa-whatsapp text-3xl"></i>
    </a>

    <script>
        function toggleMobileMenu() {
            document.getElementById('mobileMenu').classList.toggle('hidden');
        }
    </script>
</body>

</html>