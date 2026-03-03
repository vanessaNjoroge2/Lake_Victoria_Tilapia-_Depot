<?php

/**
 * Services Page - Lake Victoria Tilapia Depot
 * Displays all available services
 */
require_once '../../config/config.php';
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Services - <?php echo SITE_NAME; ?></title>
    <meta name="description" content="Explore our comprehensive fish services: fresh fish sales, cleaning & preparation, deep frying, and bulk supply for restaurants and events.">

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        * {
            font-family: 'Poppins', sans-serif;
        }

        .card-hover {
            transition: all 0.3s ease;
        }

        .card-hover:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(6, 182, 212, 0.3);
        }

        @keyframes pulse {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.1);
            }
        }

        .pulse-anim {
            animation: pulse 2s infinite;
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
                    <a href="<?php echo BASE_URL; ?>/views/public/services.php" class="text-cyan-600 font-bold border-b-2 border-cyan-600">
                        <i class="fas fa-concierge-bell mr-1"></i> Services
                    </a>
                    <a href="<?php echo BASE_URL; ?>/views/public/products.php" class="text-gray-700 hover:text-cyan-600 font-medium transition">
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
                <a href="<?php echo BASE_URL; ?>/views/public/services.php" class="block py-2 text-cyan-600 font-bold">
                    <i class="fas fa-concierge-bell mr-2"></i> Services
                </a>
                <a href="<?php echo BASE_URL; ?>/views/public/products.php" class="block py-2 text-gray-700 hover:text-cyan-600">
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
            <h1 class="text-5xl lg:text-6xl font-bold mb-6">Our Services</h1>
            <p class="text-xl lg:text-2xl text-blue-50 max-w-3xl mx-auto">
                Comprehensive fish services tailored to meet all your needs
            </p>
        </div>
    </section>

    <!-- Services Grid -->
    <section class="py-20 bg-gradient-to-br from-gray-50 to-blue-50">
        <div class="container mx-auto px-4 lg:px-8">
            <div class="grid md:grid-cols-2 gap-8 max-w-6xl mx-auto">

                <!-- Service 1: Fresh Fish Sales -->
                <div class="card-hover bg-white rounded-2xl shadow-xl overflow-hidden">
                    <div class="bg-gradient-to-br from-cyan-500 to-blue-600 p-8 text-white text-center">
                        <i class="fas fa-fish pulse-anim text-6xl mb-4"></i>
                        <h2 class="text-3xl font-bold">Fresh Fish Sales</h2>
                    </div>
                    <div class="p-8">
                        <p class="text-gray-700 text-lg mb-6 leading-relaxed">
                            We source our tilapia directly from Lake Victoria every morning, ensuring you receive the freshest fish possible.
                            Our commitment to quality means every fish is carefully inspected and handled with care.
                        </p>

                        <h3 class="font-bold text-xl mb-4 text-gray-800">What We Offer:</h3>
                        <ul class="space-y-3 mb-6">
                            <li class="flex items-start">
                                <i class="fas fa-check-circle text-cyan-500 mr-3 mt-1"></i>
                                <span><strong>Daily Fresh Catch:</strong> Fish caught same morning, never frozen</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-check-circle text-cyan-500 mr-3 mt-1"></i>
                                <span><strong>Multiple Sizes:</strong> Small (0.2-0.5kg), Medium (0.6-1.0kg), Large (1.1-2.0kg)</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-check-circle text-cyan-500 mr-3 mt-1"></i>
                                <span><strong>Quality Guaranteed:</strong> Each fish inspected for freshness and quality</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-check-circle text-cyan-500 mr-3 mt-1"></i>
                                <span><strong>Competitive Pricing:</strong> Fair prices for retail and wholesale</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-check-circle text-cyan-500 mr-3 mt-1"></i>
                                <span><strong>Sustainable Sourcing:</strong> Supporting local fishermen and eco-friendly practices</span>
                            </li>
                        </ul>

                        <div class="flex gap-4">
                            <a href="<?php echo BASE_URL; ?>/views/public/products.php" class="flex-1 text-center bg-cyan-500 text-white py-3 rounded-lg font-semibold hover:bg-cyan-600 transition">
                                View Products
                            </a>
                            <a href="<?php echo BASE_URL; ?>/views/auth/login.php" class="flex-1 text-center bg-gradient-to-r from-cyan-500 to-blue-600 text-white py-3 rounded-lg font-semibold hover:shadow-lg transition">
                                Order Now
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Service 2: Fish Cleaning & Preparation -->
                <div class="card-hover bg-white rounded-2xl shadow-xl overflow-hidden">
                    <div class="bg-gradient-to-br from-emerald-500 to-teal-600 p-8 text-white text-center">
                        <i class="fas fa-hands-wash pulse-anim text-6xl mb-4"></i>
                        <h2 class="text-3xl font-bold">Cleaning & Preparation</h2>
                    </div>
                    <div class="p-8">
                        <p class="text-gray-700 text-lg mb-6 leading-relaxed">
                            Save time and effort with our professional fish cleaning service. We prepare your fish exactly how you like it,
                            ensuring it's ready to cook when you get home.
                        </p>

                        <h3 class="font-bold text-xl mb-4 text-gray-800">Services Include:</h3>
                        <ul class="space-y-3 mb-6">
                            <li class="flex items-start">
                                <i class="fas fa-check-circle text-emerald-500 mr-3 mt-1"></i>
                                <span><strong>Scaling & Descaling:</strong> Complete removal of scales</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-check-circle text-emerald-500 mr-3 mt-1"></i>
                                <span><strong>Gutting & Cleaning:</strong> Thorough internal cleaning</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-check-circle text-emerald-500 mr-3 mt-1"></i>
                                <span><strong>Custom Cutting:</strong> Whole, filleted, or cut to your specification</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-check-circle text-emerald-500 mr-3 mt-1"></i>
                                <span><strong>Hygienic Process:</strong> Cleaned in sanitized environment with purified water</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-check-circle text-emerald-500 mr-3 mt-1"></i>
                                <span><strong>No Extra Charge:</strong> Free with your fish purchase</span>
                            </li>
                        </ul>

                        <div class="bg-emerald-50 border-l-4 border-emerald-500 p-4 mb-6">
                            <p class="text-sm text-gray-700">
                                <i class="fas fa-info-circle text-emerald-500 mr-2"></i>
                                <strong>Pro Tip:</strong> Ask for filleting if you're making fish stew or curry. We recommend whole fish for grilling.
                            </p>
                        </div>

                        <a href="<?php echo BASE_URL; ?>/views/public/contact.php" class="block text-center bg-emerald-500 text-white py-3 rounded-lg font-semibold hover:bg-emerald-600 transition">
                            <i class="fas fa-phone mr-2"></i>Call to Order
                        </a>
                    </div>
                </div>

                <!-- Service 3: Deep Frying -->
                <div class="card-hover bg-white rounded-2xl shadow-xl overflow-hidden">
                    <div class="bg-gradient-to-br from-orange-500 to-red-600 p-8 text-white text-center">
                        <i class="fas fa-fire-burner pulse-anim text-6xl mb-4"></i>
                        <h2 class="text-3xl font-bold">Deep Frying Service</h2>
                    </div>
                    <div class="p-8">
                        <p class="text-gray-700 text-lg mb-6 leading-relaxed">
                            Don't feel like cooking? Let us fry your fish to golden perfection! Our expert chefs use a special blend of
                            spices and high-quality oil to create delicious, crispy fried fish that's ready to enjoy.
                        </p>

                        <h3 class="font-bold text-xl mb-4 text-gray-800">Why Choose Our Frying:</h3>
                        <ul class="space-y-3 mb-6">
                            <li class="flex items-start">
                                <i class="fas fa-check-circle text-orange-500 mr-3 mt-1"></i>
                                <span><strong>Golden & Crispy:</strong> Perfectly fried with crispy exterior and tender interior</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-check-circle text-orange-500 mr-3 mt-1"></i>
                                <span><strong>Secret Seasoning:</strong> Our special blend of herbs and spices</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-check-circle text-orange-500 mr-3 mt-1"></i>
                                <span><strong>Fresh Cooking Oil:</strong> Changed regularly for best taste and health</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-check-circle text-orange-500 mr-3 mt-1"></i>
                                <span><strong>Quick Service:</strong> Fried while you wait (15-20 minutes)</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-check-circle text-orange-500 mr-3 mt-1"></i>
                                <span><strong>Packaging:</strong> Packed hot in food-safe containers</span>
                            </li>
                        </ul>

                        <div class="bg-orange-50 border-l-4 border-orange-500 p-4 mb-6">
                            <p class="text-sm text-gray-700">
                                <i class="fas fa-clock text-orange-500 mr-2"></i>
                                <strong>Average Wait Time:</strong> 15-20 minutes depending on size and quantity
                            </p>
                        </div>

                        <a href="<?php echo BASE_URL; ?>/views/auth/login.php" class="block text-center bg-orange-500 text-white py-3 rounded-lg font-semibold hover:bg-orange-600 transition">
                            <i class="fas fa-shopping-cart mr-2"></i>Order Fried Fish
                        </a>
                    </div>
                </div>

                <!-- Service 4: Bulk Supply -->
                <div class="card-hover bg-white rounded-2xl shadow-xl overflow-hidden">
                    <div class="bg-gradient-to-br from-purple-500 to-indigo-600 p-8 text-white text-center">
                        <i class="fas fa-truck-loading pulse-anim text-6xl mb-4"></i>
                        <h2 class="text-3xl font-bold">Bulk Supply Services</h2>
                    </div>
                    <div class="p-8">
                        <p class="text-gray-700 text-lg mb-6 leading-relaxed">
                            We provide large-scale fish supply for restaurants, hotels, catering services, and events.
                            With reliable delivery and competitive wholesale pricing, we're your trusted bulk supplier.
                        </p>

                        <h3 class="font-bold text-xl mb-4 text-gray-800">Bulk Services Include:</h3>
                        <ul class="space-y-3 mb-6">
                            <li class="flex items-start">
                                <i class="fas fa-check-circle text-purple-500 mr-3 mt-1"></i>
                                <span><strong>Wholesale Pricing:</strong> Special rates for bulk orders (50kg+)</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-check-circle text-purple-500 mr-3 mt-1"></i>
                                <span><strong>Scheduled Delivery:</strong> Daily, weekly, or custom delivery schedules</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-check-circle text-purple-500 mr-3 mt-1"></i>
                                <span><strong>Contract Options:</strong> Long-term supply agreements available</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-check-circle text-purple-500 mr-3 mt-1"></i>
                                <span><strong>Custom Processing:</strong> Cleaned, cut, or prepared to your specifications</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-check-circle text-purple-500 mr-3 mt-1"></i>
                                <span><strong>Quality Assurance:</strong> Consistent quality with every delivery</span>
                            </li>
                        </ul>

                        <div class="bg-purple-50 border-l-4 border-purple-500 p-4 mb-6">
                            <p class="text-sm text-gray-700 mb-2">
                                <strong>Ideal For:</strong>
                            </p>
                            <ul class="text-sm text-gray-700 space-y-1 ml-4">
                                <li>• Restaurants & Hotels</li>
                                <li>• Catering Companies</li>
                                <li>• Event Organizers</li>
                                <li>• Corporate Functions</li>
                                <li>• Weddings & Parties</li>
                            </ul>
                        </div>

                        <a href="<?php echo BASE_URL; ?>/views/public/contact.php" class="block text-center bg-purple-500 text-white py-3 rounded-lg font-semibold hover:bg-purple-600 transition">
                            <i class="fas fa-envelope mr-2"></i>Request Quote
                        </a>
                    </div>
                </div>
            </div>

            <!-- Additional Services Section -->
            <div class="mt-16 bg-white rounded-2xl shadow-xl p-8 max-w-6xl mx-auto">
                <h2 class="text-3xl font-bold text-center mb-8 text-gray-800">Additional Services</h2>
                <div class="grid md:grid-cols-3 gap-6">
                    <div class="text-center p-6 border-2 border-cyan-100 rounded-xl hover:border-cyan-500 transition">
                        <i class="fas fa-snowflake text-4xl text-blue-500 mb-4"></i>
                        <h3 class="font-bold text-lg mb-2">Cold Storage</h3>
                        <p class="text-gray-600 text-sm">Temporary storage available for bulk orders</p>
                    </div>
                    <div class="text-center p-6 border-2 border-emerald-100 rounded-xl hover:border-emerald-500 transition">
                        <i class="fas fa-truck text-4xl text-emerald-500 mb-4"></i>
                        <h3 class="font-bold text-lg mb-2">Delivery Service</h3>
                        <p class="text-gray-600 text-sm">Free delivery for orders over 20kg within Kisumu</p>
                    </div>
                    <div class="text-center p-6 border-2 border-purple-100 rounded-xl hover:border-purple-500 transition">
                        <i class="fas fa-headset text-4xl text-purple-500 mb-4"></i>
                        <h3 class="font-bold text-lg mb-2">24/7 Support</h3>
                        <p class="text-gray-600 text-sm">Customer support available via phone and WhatsApp</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-16 bg-gradient-to-r from-cyan-600 to-blue-700 text-white">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-4xl font-bold mb-4">Ready to Order?</h2>
            <p class="text-xl mb-8 text-blue-50">Experience our quality services today!</p>
            <div class="flex flex-wrap justify-center gap-4">
                <a href="<?php echo BASE_URL; ?>/views/auth/login.php" class="bg-yellow-400 text-gray-900 px-8 py-4 rounded-full font-bold text-lg hover:bg-yellow-300 transform hover:scale-105 transition">
                    <i class="fas fa-shopping-cart mr-2"></i>Place Order
                </a>
                <a href="<?php echo BASE_URL; ?>/views/public/contact.php" class="bg-white/20 backdrop-blur-sm text-white border-2 border-white px-8 py-4 rounded-full font-bold text-lg hover:bg-white/30 transition">
                    <i class="fas fa-phone mr-2"></i>Contact Us
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
                    <h4 class="font-bold mb-4">Our Services</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li>Fresh Fish Sales</li>
                        <li>Fish Cleaning & Prep</li>
                        <li>Deep Frying</li>
                        <li>Bulk Supply</li>
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