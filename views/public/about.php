<?php
require_once '../../config/config.php';
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - <?php echo SITE_NAME; ?></title>
    <meta name="description" content="Learn about Lake Victoria Tilapia Depot - our story, mission, values, and commitment to delivering fresh tilapia with quality and sustainability.">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        * {
            font-family: 'Poppins', sans-serif;
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
                    <a href="<?php echo BASE_URL; ?>/views/public/about.php" class="text-cyan-600 font-bold border-b-2 border-cyan-600">
                        <i class="fas fa-info-circle mr-1"></i> About Us
                    </a>
                    <a href="<?php echo BASE_URL; ?>/views/public/services.php" class="text-gray-700 hover:text-cyan-600 font-medium transition">
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
                <a href="<?php echo BASE_URL; ?>/views/public/about.php" class="block py-2 text-cyan-600 font-bold">
                    <i class="fas fa-info-circle mr-2"></i> About Us
                </a>
                <a href="<?php echo BASE_URL; ?>/views/public/services.php" class="block py-2 text-gray-700 hover:text-cyan-600">
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
    <div class="bg-gradient-to-r from-blue-600 to-blue-800 text-white py-20">
        <div class="container mx-auto px-4 text-center">
            <h1 class="text-5xl font-bold mb-4">About Lake Victoria Tilapia Depot</h1>
            <p class="text-xl text-blue-100 max-w-3xl mx-auto">Bringing the freshest tilapia from Lake Victoria directly to your doorstep since our inception</p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container mx-auto px-4 py-16">
        <!-- Our Story -->
        <div class="max-w-6xl mx-auto">
            <div class="grid md:grid-cols-2 gap-12 items-center mb-20">
                <div>
                    <h2 class="text-4xl font-bold text-gray-800 mb-6">Our Story</h2>
                    <p class="text-gray-600 leading-relaxed mb-4">
                        Lake Victoria Tilapia Depot was born from a passion for delivering the finest quality fish to families across the region. What started as a small lakeside operation has grown into a trusted name in fresh fish supply.
                    </p>
                    <p class="text-gray-600 leading-relaxed mb-4">
                        We work directly with local fishermen around Lake Victoria, ensuring sustainable fishing practices and fair compensation. Every fish we sell represents our commitment to quality, freshness, and community support.
                    </p>
                    <p class="text-gray-600 leading-relaxed">
                        Our modern online platform makes it easier than ever to order fresh tilapia, with convenient delivery options and secure payment methods including M-PESA integration.
                    </p>
                </div>
                <div class="bg-gradient-to-br from-blue-100 to-blue-200 rounded-3xl p-8 text-center overflow-hidden">
                    <img src="<?php echo BASE_URL; ?>/uploads/tilapia_in_water.jpg"
                        alt="Fresh Tilapia in Water"
                        class="w-full h-64 object-cover rounded-2xl mb-4 shadow-lg animate-pulse hover:animate-none transition-all">
                    <h3 class="text-2xl font-bold text-gray-800">Fresh Daily Catch</h3>
                    <p class="text-gray-600 mt-2">From Lake Victoria to Your Table</p>
                </div>
            </div>

            <!-- Mission & Vision -->
            <div class="grid md:grid-cols-2 gap-8 mb-20">
                <div class="bg-white rounded-2xl shadow-lg p-8">
                    <div class="bg-blue-100 w-16 h-16 rounded-full flex items-center justify-center mb-6">
                        <i class="fas fa-bullseye text-blue-600 text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-4">Our Mission</h3>
                    <p class="text-gray-600 leading-relaxed">
                        To provide the highest quality fresh tilapia while supporting local fishing communities and promoting sustainable fishing practices around Lake Victoria. We aim to make fresh, nutritious fish accessible to everyone through our convenient online platform.
                    </p>
                </div>
                <div class="bg-white rounded-2xl shadow-lg p-8">
                    <div class="bg-green-100 w-16 h-16 rounded-full flex items-center justify-center mb-6">
                        <i class="fas fa-eye text-green-600 text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-4">Our Vision</h3>
                    <p class="text-gray-600 leading-relaxed">
                        To become the leading sustainable fish supplier in the region, recognized for our unwavering commitment to quality, customer satisfaction, and environmental responsibility. We envision a future where fresh fish is just a click away for every household.
                    </p>
                </div>
            </div>

            <!-- Why Choose Us -->
            <div class="mb-20">
                <h2 class="text-4xl font-bold text-gray-800 text-center mb-12">Why Choose Us?</h2>
                <div class="grid md:grid-cols-3 gap-8">
                    <div class="text-center">
                        <div class="bg-blue-100 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-fish text-blue-600 text-3xl"></i>
                        </div>
                        <h4 class="text-xl font-bold text-gray-800 mb-3">Premium Quality</h4>
                        <p class="text-gray-600">Only the freshest tilapia, carefully selected and quality-checked before delivery</p>
                    </div>
                    <div class="text-center">
                        <div class="bg-green-100 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-leaf text-green-600 text-3xl"></i>
                        </div>
                        <h4 class="text-xl font-bold text-gray-800 mb-3">Sustainable Practices</h4>
                        <p class="text-gray-600">We support eco-friendly fishing methods that protect Lake Victoria's ecosystem</p>
                    </div>
                    <div class="text-center">
                        <div class="bg-purple-100 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-truck-fast text-purple-600 text-3xl"></i>
                        </div>
                        <h4 class="text-xl font-bold text-gray-800 mb-3">Fast Delivery</h4>
                        <p class="text-gray-600">Quick and reliable delivery service to ensure fish reaches you at peak freshness</p>
                    </div>
                    <div class="text-center">
                        <div class="bg-yellow-100 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-mobile-alt text-yellow-600 text-3xl"></i>
                        </div>
                        <h4 class="text-xl font-bold text-gray-800 mb-3">M-PESA Integration</h4>
                        <p class="text-gray-600">Secure and convenient payment options including M-PESA for hassle-free transactions</p>
                    </div>
                    <div class="text-center">
                        <div class="bg-red-100 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-users text-red-600 text-3xl"></i>
                        </div>
                        <h4 class="text-xl font-bold text-gray-800 mb-3">Community Support</h4>
                        <p class="text-gray-600">We empower local fishing communities through fair trade and direct partnerships</p>
                    </div>
                    <div class="text-center">
                        <div class="bg-indigo-100 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-headset text-indigo-600 text-3xl"></i>
                        </div>
                        <h4 class="text-xl font-bold text-gray-800 mb-3">24/7 Support</h4>
                        <p class="text-gray-600">Our dedicated customer service team is always ready to assist you</p>
                    </div>
                </div>
            </div>

            <!-- Our Values -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-800 rounded-3xl p-12 text-white">
                <h2 class="text-4xl font-bold text-center mb-12">Our Core Values</h2>
                <div class="grid md:grid-cols-4 gap-8">
                    <div class="text-center">
                        <div class="text-5xl mb-4">🎯</div>
                        <h4 class="text-xl font-bold mb-2">Quality First</h4>
                        <p class="text-blue-100">Excellence in every fish we deliver</p>
                    </div>
                    <div class="text-center">
                        <div class="text-5xl mb-4">🤝</div>
                        <h4 class="text-xl font-bold mb-2">Integrity</h4>
                        <p class="text-blue-100">Honest and transparent in all dealings</p>
                    </div>
                    <div class="text-center">
                        <div class="text-5xl mb-4">🌍</div>
                        <h4 class="text-xl font-bold mb-2">Sustainability</h4>
                        <p class="text-blue-100">Protecting our lake for future generations</p>
                    </div>
                    <div class="text-center">
                        <div class="text-5xl mb-4">❤️</div>
                        <h4 class="text-xl font-bold mb-2">Customer Care</h4>
                        <p class="text-blue-100">Your satisfaction is our priority</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- CTA Section -->
    <section class="py-16 bg-gradient-to-r from-cyan-600 to-blue-700 text-white">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-4xl font-bold mb-4">Ready to Experience Fresh Tilapia?</h2>
            <p class="text-xl mb-8 text-blue-50">Join thousands of satisfied customers who trust us for their fish needs</p>
            <div class="flex flex-wrap justify-center gap-4">
                <a href="<?php echo BASE_URL; ?>/views/auth/login.php" class="bg-yellow-400 text-gray-900 px-8 py-4 rounded-full font-bold text-lg hover:bg-yellow-300 transform hover:scale-105 transition">
                    <i class="fas fa-sign-in-alt mr-2"></i>Login Now
                </a>
                <a href="<?php echo BASE_URL; ?>/views/public/products.php" class="bg-white/20 backdrop-blur-sm text-white border-2 border-white px-8 py-4 rounded-full font-bold text-lg hover:bg-white/30 transition">
                    <i class="fas fa-fish mr-2"></i>View Products
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