<?php

/**
 * Lake Victoria Tilapia Depot - Modern Landing Page
 * Created: January 2026
 */
require_once 'config/config.php';
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Fresh Tilapia from Lake Victoria</title>
    <meta name="description" content="Lake Victoria Tilapia Depot - Fresh tilapia fish daily. Fish cleaning, deep frying, and bulk supply services.">

    <!-- Tailwind CSS & Icons -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        * {
            font-family: 'Poppins', sans-serif;
        }

        /* Hero Section Styling */
        .hero-bg {
            background: linear-gradient(135deg, rgba(6, 182, 212, 0.95) 0%, rgba(8, 145, 178, 0.95) 100%),
                url('<?php echo BASE_URL; ?>/uploads/tilapia_in_water.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }

        /* Animations */
        @keyframes float {

            0%,
            100% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-20px);
            }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
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

        .float-anim {
            animation: float 6s ease-in-out infinite;
        }

        .fade-in {
            animation: fadeInUp 0.8s ease-out;
        }

        .pulse-anim {
            animation: pulse 2s infinite;
        }

        /* Hover Effects */
        .card-hover {
            transition: all 0.3s ease;
        }

        .card-hover:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(6, 182, 212, 0.3);
        }

        .product-hover {
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .product-hover:hover {
            transform: translateY(-15px);
        }

        /* Mobile Menu */
        .mobile-menu {
            transition: max-height 0.3s ease-in-out;
            max-height: 0;
            overflow: hidden;
        }

        .mobile-menu.active {
            max-height: 500px;
        }

        /* Sticky Header */
        .sticky-nav {
            transition: all 0.3s ease;
        }

        .sticky-nav.scrolled {
            background: rgba(255, 255, 255, 0.98);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 10px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }

        ::-webkit-scrollbar-thumb {
            background: #06b6d4;
            border-radius: 5px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #0891b2;
        }
    </style>
</head>

<body class="bg-gray-50">

    <!-- Navigation -->
    <nav class="sticky-nav fixed w-full top-0 z-50 bg-white shadow-md">
        <div class="container mx-auto px-4 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center space-x-3">
                    <img src="<?php echo BASE_URL; ?>/uploads/fresh_tilapia_logo.jpg" alt="Logo" class="w-12 h-12 rounded-full object-cover shadow-md">
                    <div>
                        <h1 class="text-xl font-bold text-cyan-600">Lake Victoria</h1>
                        <p class="text-xs text-gray-600">Tilapia Depot</p>
                    </div>
                </div>

                <!-- Desktop Menu -->
                <div class="hidden lg:flex items-center space-x-8">
                    <a href="#home" class="text-gray-700 hover:text-cyan-600 font-medium transition"><i class="fas fa-home mr-1"></i> Home</a>
                    <a href="<?php echo BASE_URL; ?>/views/public/about.php" class="text-gray-700 hover:text-cyan-600 font-medium transition"><i class="fas fa-info-circle mr-1"></i> About Us</a>
                    <a href="<?php echo BASE_URL; ?>/views/public/services.php" class="text-gray-700 hover:text-cyan-600 font-medium transition"><i class="fas fa-concierge-bell mr-1"></i> Services</a>
                    <a href="<?php echo BASE_URL; ?>/views/public/products.php" class="text-gray-700 hover:text-cyan-600 font-medium transition"><i class="fas fa-fish mr-1"></i> Products</a>
                    <a href="<?php echo BASE_URL; ?>/views/public/contact.php" class="text-gray-700 hover:text-cyan-600 font-medium transition"><i class="fas fa-envelope mr-1"></i> Contact</a>
                    <a href="<?php echo BASE_URL; ?>/views/auth/login.php" class="bg-gradient-to-r from-cyan-500 to-blue-600 text-white px-6 py-2.5 rounded-full font-semibold hover:shadow-lg transform hover:scale-105 transition">
                        <i class="fas fa-sign-in-alt mr-2"></i>Login
                    </a>
                </div>

                <button onclick="toggleMenu()" class="lg:hidden text-gray-700"><i class="fas fa-bars text-2xl"></i></button>
            </div>

            <!-- Mobile Menu -->
            <div id="mobileMenu" class="mobile-menu lg:hidden">
                <div class="py-4 space-y-3">
                    <a href="#home" class="block text-gray-700 hover:text-cyan-600 py-2"><i class="fas fa-home mr-2"></i> Home</a>
                    <a href="<?php echo BASE_URL; ?>/views/public/about.php" class="block text-gray-700 hover:text-cyan-600 py-2"><i class="fas fa-info-circle mr-2"></i> About Us</a>
                    <a href="<?php echo BASE_URL; ?>/views/public/services.php" class="block text-gray-700 hover:text-cyan-600 py-2"><i class="fas fa-concierge-bell mr-2"></i> Services</a>
                    <a href="<?php echo BASE_URL; ?>/views/public/products.php" class="block text-gray-700 hover:text-cyan-600 py-2"><i class="fas fa-fish mr-2"></i> Products</a>
                    <a href="<?php echo BASE_URL; ?>/views/public/contact.php" class="block text-gray-700 hover:text-cyan-600 py-2"><i class="fas fa-envelope mr-2"></i> Contact</a>
                    <a href="<?php echo BASE_URL; ?>/views/auth/login.php" class="block bg-gradient-to-r from-cyan-500 to-blue-600 text-white px-6 py-2.5 rounded-full font-semibold text-center mt-4">
                        <i class="fas fa-sign-in-alt mr-2"></i>Login
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section (HOME) -->
    <section id="home" class="hero-bg min-h-screen flex items-center justify-center pt-20">
        <div class="container mx-auto px-4 lg:px-8">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <div class="text-white fade-in">
                    <div class="inline-block bg-white/20 backdrop-blur-sm px-4 py-2 rounded-full mb-6">
                        <span class="text-yellow-300 font-semibold">🐟 Fresh from Lake Victoria</span>
                    </div>
                    <h1 class="text-5xl lg:text-6xl font-bold mb-6 leading-tight">
                        Fresh Tilapia<br />
                        <span class="text-yellow-300">Delivered Daily</span>
                    </h1>
                    <p class="text-xl lg:text-2xl mb-8 text-blue-50">
                        Experience the finest tilapia fish from Lake Victoria. Fresh, sustainable, and delicious.
                    </p>
                    <div class="flex flex-wrap gap-4">
                        <a href="<?php echo BASE_URL; ?>/views/auth/register.php" class="bg-yellow-400 text-gray-900 px-8 py-4 rounded-full font-bold text-lg hover:bg-yellow-300 transform hover:scale-105 transition shadow-2xl">
                            <i class="fas fa-user-plus mr-2"></i>Create Account
                        </a>
                        <a href="<?php echo BASE_URL; ?>/views/auth/login.php" class="bg-cyan-500 text-white px-8 py-4 rounded-full font-bold text-lg hover:bg-cyan-600 transform hover:scale-105 transition shadow-2xl">
                            <i class="fas fa-sign-in-alt mr-2"></i>Login
                        </a>
                        <a href="#about" class="bg-white/20 backdrop-blur-sm text-white border-2 border-white px-8 py-4 rounded-full font-bold text-lg hover:bg-white/30 transition">
                            <i class="fas fa-arrow-circle-down mr-2"></i>Learn More
                        </a>
                    </div>

                    <!-- Stats -->
                    <div class="grid grid-cols-3 gap-4 mt-12">
                        <div class="text-center">
                            <div class="text-3xl font-bold text-yellow-300">1000+</div>
                            <div class="text-sm text-blue-100">Customers</div>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl font-bold text-yellow-300">500kg+</div>
                            <div class="text-sm text-blue-100">Daily Fresh</div>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl font-bold text-yellow-300">24/7</div>
                            <div class="text-sm text-blue-100">Support</div>
                        </div>
                    </div>
                </div>

                <div class="hidden lg:block">
                    <div class="float-anim">
                        <img src="<?php echo BASE_URL; ?>/uploads/fresh_tilapia.jpg" alt="Fresh Tilapia" class="rounded-3xl shadow-2xl">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="py-20 bg-white">
        <div class="container mx-auto px-4 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl lg:text-5xl font-bold text-gray-800 mb-4">
                    About <span class="text-cyan-600">Us</span>
                </h2>
                <div class="w-24 h-1 bg-gradient-to-r from-cyan-500 to-blue-600 mx-auto mb-6"></div>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    Your trusted source for fresh tilapia from Lake Victoria
                </p>
            </div>

            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <div>
                    <div class="bg-gradient-to-br from-cyan-50 to-blue-50 rounded-3xl p-8 shadow-lg">
                        <h3 class="text-2xl font-bold text-gray-800 mb-4">Who We Are</h3>
                        <p class="text-gray-700 leading-relaxed mb-4">
                            Lake Victoria Tilapia Depot is a family-owned business committed to bringing the finest, freshest tilapia fish directly from Lake Victoria to your table.
                        </p>
                        <p class="text-gray-700 leading-relaxed mb-4">
                            With years of experience, we've built lasting relationships with local fishermen who share our commitment to quality and sustainability.
                        </p>
                        <p class="text-gray-700 leading-relaxed">
                            Our mission: deliver fresh, high-quality tilapia while maintaining the highest standards of customer service, environmental responsibility, and fair trade practices.
                        </p>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mt-8">
                        <div class="bg-white rounded-xl p-6 shadow-lg border-l-4 border-cyan-500">
                            <i class="fas fa-bullseye text-3xl text-cyan-500 mb-3"></i>
                            <h4 class="font-bold text-gray-800 mb-2">Our Mission</h4>
                            <p class="text-sm text-gray-600">Fresh, sustainable tilapia with exceptional service</p>
                        </div>
                        <div class="bg-white rounded-xl p-6 shadow-lg border-l-4 border-emerald-500">
                            <i class="fas fa-heart text-3xl text-emerald-500 mb-3"></i>
                            <h4 class="font-bold text-gray-800 mb-2">Our Values</h4>
                            <p class="text-sm text-gray-600">Quality, sustainability, community, satisfaction</p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="card-hover bg-white rounded-2xl p-4 shadow-lg">
                        <img src="<?php echo BASE_URL; ?>/uploads/fresh_tilapia.jpg" alt="Fresh" class="w-full h-48 object-cover rounded-xl mb-3">
                        <h4 class="font-bold text-gray-800 text-center">Fresh Daily</h4>
                    </div>
                    <div class="card-hover bg-white rounded-2xl p-4 shadow-lg">
                        <img src="<?php echo BASE_URL; ?>/uploads/tilapia_in_water.jpg" alt="Lake" class="w-full h-48 object-cover rounded-xl mb-3">
                        <h4 class="font-bold text-gray-800 text-center">Lake Victoria</h4>
                    </div>
                    <div class="card-hover bg-white rounded-2xl p-4 shadow-lg">
                        <img src="<?php echo BASE_URL; ?>/uploads/68f8a9477ffe3_fish fillets.jpg" alt="Quality" class="w-full h-48 object-cover rounded-xl mb-3">
                        <h4 class="font-bold text-gray-800 text-center">Premium Quality</h4>
                    </div>
                    <div class="card-hover bg-white rounded-2xl p-4 shadow-lg">
                        <img src="<?php echo BASE_URL; ?>/uploads/68f8c98cdd35c_NilePerch.jpg" alt="Trade" class="w-full h-48 object-cover rounded-xl mb-3">
                        <h4 class="font-bold text-gray-800 text-center">Fair Trade</h4>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section id="services" class="py-20 bg-gradient-to-br from-cyan-50 via-blue-50 to-emerald-50">
        <div class="container mx-auto px-4 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl lg:text-5xl font-bold text-gray-800 mb-4">
                    Our <span class="text-cyan-600">Services</span>
                </h2>
                <div class="w-24 h-1 bg-gradient-to-r from-cyan-500 to-blue-600 mx-auto mb-6"></div>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    Comprehensive fish services tailored to your needs
                </p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
                <!-- Service 1 -->
                <div class="card-hover bg-white rounded-2xl shadow-xl overflow-hidden">
                    <div class="bg-gradient-to-br from-cyan-500 to-blue-600 p-8 text-white text-center">
                        <i class="fas fa-fish pulse-anim text-5xl mb-4"></i>
                        <h3 class="text-2xl font-bold">Fresh Fish Sales</h3>
                    </div>
                    <div class="p-6">
                        <p class="text-gray-700 mb-4">
                            Daily fresh tilapia from Lake Victoria. All fish caught fresh every morning.
                        </p>
                        <ul class="space-y-2 text-sm text-gray-600 mb-4">
                            <li><i class="fas fa-check-circle text-cyan-500 mr-2"></i>Fresh guarantee</li>
                            <li><i class="fas fa-check-circle text-cyan-500 mr-2"></i>Multiple sizes</li>
                            <li><i class="fas fa-check-circle text-cyan-500 mr-2"></i>Quality checked</li>
                        </ul>
                        <a href="<?php echo BASE_URL; ?>/views/auth/login.php" class="block text-center bg-cyan-500 text-white py-2 rounded-lg hover:bg-cyan-600 transition">
                            Order Now
                        </a>
                    </div>
                </div>

                <!-- Service 2 -->
                <div class="card-hover bg-white rounded-2xl shadow-xl overflow-hidden">
                    <div class="bg-gradient-to-br from-emerald-500 to-teal-600 p-8 text-white text-center">
                        <i class="fas fa-hands-wash pulse-anim text-5xl mb-4"></i>
                        <h3 class="text-2xl font-bold">Cleaning & Prep</h3>
                    </div>
                    <div class="p-6">
                        <p class="text-gray-700 mb-4">
                            Professional fish cleaning and preparation. Ready-to-cook fish at home.
                        </p>
                        <ul class="space-y-2 text-sm text-gray-600 mb-4">
                            <li><i class="fas fa-check-circle text-emerald-500 mr-2"></i>Scaling & gutting</li>
                            <li><i class="fas fa-check-circle text-emerald-500 mr-2"></i>Custom cutting</li>
                            <li><i class="fas fa-check-circle text-emerald-500 mr-2"></i>Hygienic prep</li>
                        </ul>
                        <a href="<?php echo BASE_URL; ?>/views/auth/login.php" class="block text-center bg-emerald-500 text-white py-2 rounded-lg hover:bg-emerald-600 transition">
                            Get Service
                        </a>
                    </div>
                </div>

                <!-- Service 3 -->
                <div class="card-hover bg-white rounded-2xl shadow-xl overflow-hidden">
                    <div class="bg-gradient-to-br from-orange-500 to-red-600 p-8 text-white text-center">
                        <i class="fas fa-fire-burner pulse-anim text-5xl mb-4"></i>
                        <h3 class="text-2xl font-bold">Deep Frying</h3>
                    </div>
                    <div class="p-6">
                        <p class="text-gray-700 mb-4">
                            Expertly deep-fried to golden perfection. Ready-to-eat delicious fish.
                        </p>
                        <ul class="space-y-2 text-sm text-gray-600 mb-4">
                            <li><i class="fas fa-check-circle text-orange-500 mr-2"></i>Golden crispy</li>
                            <li><i class="fas fa-check-circle text-orange-500 mr-2"></i>Secret seasoning</li>
                            <li><i class="fas fa-check-circle text-orange-500 mr-2"></i>Fresh oil</li>
                        </ul>
                        <a href="<?php echo BASE_URL; ?>/views/auth/login.php" class="block text-center bg-orange-500 text-white py-2 rounded-lg hover:bg-orange-600 transition">
                            Order Fried
                        </a>
                    </div>
                </div>

                <!-- Service 4 -->
                <div class="card-hover bg-white rounded-2xl shadow-xl overflow-hidden">
                    <div class="bg-gradient-to-br from-purple-500 to-indigo-600 p-8 text-white text-center">
                        <i class="fas fa-truck-loading pulse-anim text-5xl mb-4"></i>
                        <h3 class="text-2xl font-bold">Bulk Supply</h3>
                    </div>
                    <div class="p-6">
                        <p class="text-gray-700 mb-4">
                            Large-scale supply for restaurants, hotels, events, and catering services.
                        </p>
                        <ul class="space-y-2 text-sm text-gray-600 mb-4">
                            <li><i class="fas fa-check-circle text-purple-500 mr-2"></i>Wholesale pricing</li>
                            <li><i class="fas fa-check-circle text-purple-500 mr-2"></i>Scheduled delivery</li>
                            <li><i class="fas fa-check-circle text-purple-500 mr-2"></i>Contract options</li>
                        </ul>
                        <a href="#contact" class="block text-center bg-purple-500 text-white py-2 rounded-lg hover:bg-purple-600 transition">
                            Request Quote
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Products Section -->
    <section id="products" class="py-20 bg-white">
        <div class="container mx-auto px-4 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl lg:text-5xl font-bold text-gray-800 mb-4">
                    Our <span class="text-cyan-600">Products</span>
                </h2>
                <div class="w-24 h-1 bg-gradient-to-r from-cyan-500 to-blue-600 mx-auto mb-6"></div>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    Premium quality tilapia in various sizes
                </p>
            </div>

            <div class="grid md:grid-cols-3 gap-8 max-w-6xl mx-auto">
                <!-- Small Tilapia -->
                <div class="product-hover bg-gradient-to-br from-blue-50 to-cyan-50 rounded-3xl shadow-xl overflow-hidden">
                    <div class="relative h-64 overflow-hidden">
                        <img src="<?php echo BASE_URL; ?>/uploads/fresh_tilapia.jpg" alt="Small" class="w-full h-full object-cover">
                        <div class="absolute top-4 right-4 bg-cyan-500 text-white px-4 py-2 rounded-full font-bold">Popular</div>
                    </div>
                    <div class="p-6">
                        <div class="flex justify-between mb-4">
                            <h3 class="text-2xl font-bold text-gray-800">Small Tilapia</h3>
                            <div class="text-cyan-600 text-2xl font-bold">Ksh 150-250</div>
                        </div>
                        <span class="inline-block bg-cyan-100 text-cyan-700 px-3 py-1 rounded-full text-sm mb-4">0.2 - 0.5 kg</span>
                        <p class="text-gray-600 mb-4">Perfect for individual meals and small families. Great for frying and grilling.</p>
                        <a href="<?php echo BASE_URL; ?>/views/auth/login.php" class="block text-center bg-gradient-to-r from-cyan-500 to-blue-600 text-white py-3 rounded-full font-bold hover:shadow-lg transition">
                            <i class="fas fa-shopping-cart mr-2"></i>Order Now
                        </a>
                    </div>
                </div>

                <!-- Medium Tilapia -->
                <div class="product-hover bg-gradient-to-br from-emerald-50 to-teal-50 rounded-3xl shadow-xl overflow-hidden">
                    <div class="relative h-64 overflow-hidden">
                        <img src="<?php echo BASE_URL; ?>/uploads/68f8a9477ffe3_fish fillets.jpg" alt="Medium" class="w-full h-full object-cover">
                        <div class="absolute top-4 right-4 bg-emerald-500 text-white px-4 py-2 rounded-full font-bold">Best Value</div>
                    </div>
                    <div class="p-6">
                        <div class="flex justify-between mb-4">
                            <h3 class="text-2xl font-bold text-gray-800">Medium Tilapia</h3>
                            <div class="text-emerald-600 text-2xl font-bold">Ksh 300-500</div>
                        </div>
                        <span class="inline-block bg-emerald-100 text-emerald-700 px-3 py-1 rounded-full text-sm mb-4">0.6 - 1.0 kg</span>
                        <p class="text-gray-600 mb-4">Most popular for family gatherings. Excellent for various cooking methods.</p>
                        <a href="<?php echo BASE_URL; ?>/views/auth/login.php" class="block text-center bg-gradient-to-r from-emerald-500 to-teal-600 text-white py-3 rounded-full font-bold hover:shadow-lg transition">
                            <i class="fas fa-shopping-cart mr-2"></i>Order Now
                        </a>
                    </div>
                </div>

                <!-- Large Tilapia -->
                <div class="product-hover bg-gradient-to-br from-purple-50 to-indigo-50 rounded-3xl shadow-xl overflow-hidden">
                    <div class="relative h-64 overflow-hidden">
                        <img src="<?php echo BASE_URL; ?>/uploads/68f8c98cdd35c_NilePerch.jpg" alt="Large" class="w-full h-full object-cover">
                        <div class="absolute top-4 right-4 bg-purple-500 text-white px-4 py-2 rounded-full font-bold">Premium</div>
                    </div>
                    <div class="p-6">
                        <div class="flex justify-between mb-4">
                            <h3 class="text-2xl font-bold text-gray-800">Large Tilapia</h3>
                            <div class="text-purple-600 text-2xl font-bold">Ksh 600-900</div>
                        </div>
                        <span class="inline-block bg-purple-100 text-purple-700 px-3 py-1 rounded-full text-sm mb-4">1.1 - 2.0 kg</span>
                        <p class="text-gray-600 mb-4">Premium for restaurants, hotels, and large events. Maximum flavor!</p>
                        <a href="<?php echo BASE_URL; ?>/views/auth/login.php" class="block text-center bg-gradient-to-r from-purple-500 to-indigo-600 text-white py-3 rounded-full font-bold hover:shadow-lg transition">
                            <i class="fas fa-shopping-cart mr-2"></i>Order Now
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="py-20 bg-gradient-to-br from-gray-50 to-blue-50">
        <div class="container mx-auto px-4 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl lg:text-5xl font-bold text-gray-800 mb-4">
                    Get In <span class="text-cyan-600">Touch</span>
                </h2>
                <div class="w-24 h-1 bg-gradient-to-r from-cyan-500 to-blue-600 mx-auto mb-6"></div>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    Contact us for orders, inquiries, or bulk supply needs
                </p>
            </div>

            <div class="grid lg:grid-cols-3 gap-8 max-w-6xl mx-auto">
                <!-- Contact Info -->
                <div class="space-y-6">
                    <div class="card-hover bg-white rounded-2xl p-6 shadow-lg">
                        <div class="flex items-start">
                            <i class="fas fa-map-marker-alt text-3xl text-cyan-500 mr-4"></i>
                            <div>
                                <h3 class="font-bold text-lg mb-2">Visit Us</h3>
                                <p class="text-gray-600">Lake Victoria Region<br />Kisumu, Kenya</p>
                            </div>
                        </div>
                    </div>

                    <div class="card-hover bg-white rounded-2xl p-6 shadow-lg">
                        <div class="flex items-start">
                            <i class="fas fa-phone-alt text-3xl text-emerald-500 mr-4"></i>
                            <div>
                                <h3 class="font-bold text-lg mb-2">Call Us</h3>
                                <p class="text-gray-600"><?php echo ADMIN_PHONE; ?></p>
                                <a href="https://wa.me/<?php echo str_replace('+', '', ADMIN_PHONE); ?>" class="inline-block mt-2 bg-green-500 text-white px-4 py-2 rounded-full text-sm hover:bg-green-600 transition">
                                    <i class="fab fa-whatsapp mr-1"></i> WhatsApp
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="card-hover bg-white rounded-2xl p-6 shadow-lg">
                        <div class="flex items-start">
                            <i class="fas fa-envelope text-3xl text-purple-500 mr-4"></i>
                            <div>
                                <h3 class="font-bold text-lg mb-2">Email Us</h3>
                                <p class="text-gray-600 break-words"><?php echo ADMIN_EMAIL; ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contact Form -->
                <div class="lg:col-span-2 bg-white rounded-2xl shadow-xl p-8">
                    <h3 class="text-2xl font-bold mb-6">Send Message</h3>
                    <form id="contactForm" class="space-y-6">
                        <div class="grid md:grid-cols-2 gap-6">
                            <input type="text" placeholder="Your Name" required class="w-full px-4 py-3 rounded-lg border focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none">
                            <input type="tel" placeholder="Phone Number" required class="w-full px-4 py-3 rounded-lg border focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none">
                        </div>
                        <input type="email" placeholder="Email Address" required class="w-full px-4 py-3 rounded-lg border focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none">
                        <select required class="w-full px-4 py-3 rounded-lg border focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none">
                            <option value="">Select Subject</option>
                            <option>General Inquiry</option>
                            <option>Place an Order</option>
                            <option>Bulk Supply Request</option>
                            <option>Service Question</option>
                        </select>
                        <textarea rows="5" placeholder="Your Message" required class="w-full px-4 py-3 rounded-lg border focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none resize-none"></textarea>
                        <button type="submit" class="w-full bg-gradient-to-r from-cyan-500 to-blue-600 text-white py-4 rounded-lg font-bold hover:shadow-xl transform hover:scale-105 transition">
                            <i class="fas fa-paper-plane mr-2"></i>Send Message
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-12">
        <div class="container mx-auto px-4 lg:px-8">
            <div class="grid md:grid-cols-4 gap-8 mb-8">
                <div>
                    <h3 class="text-xl font-bold mb-4">Lake Victoria Tilapia</h3>
                    <p class="text-gray-400">Fresh tilapia from Lake Victoria, delivered with care.</p>
                </div>
                <div>
                    <h4 class="font-bold mb-4">Quick Links</h4>
                    <ul class="space-y-2">
                        <li><a href="#home" class="text-gray-400 hover:text-white transition">Home</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/views/public/about.php" class="text-gray-400 hover:text-white transition">About Us</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/views/public/services.php" class="text-gray-400 hover:text-white transition">Services</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/views/public/products.php" class="text-gray-400 hover:text-white transition">Products</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/views/public/contact.php" class="text-gray-400 hover:text-white transition">Contact</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-bold mb-4">Services</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li>Fresh Fish Sales</li>
                        <li>Fish Cleaning</li>
                        <li>Deep Frying</li>
                        <li>Bulk Supply</li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-bold mb-4">Contact</h4>
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

    <!-- Floating WhatsApp Button -->
    <a href="https://wa.me/<?php echo str_replace('+', '', ADMIN_PHONE); ?>" class="fixed bottom-8 left-8 bg-green-500 text-white w-14 h-14 rounded-full shadow-lg hover:shadow-xl transform hover:scale-110 transition flex items-center justify-center z-50 pulse-anim">
        <i class="fab fa-whatsapp text-3xl"></i>
    </a>

    <!-- Scroll to Top -->
    <button id="scrollTop" class="fixed bottom-8 right-8 bg-gradient-to-r from-cyan-500 to-blue-600 text-white w-12 h-12 rounded-full shadow-lg hover:shadow-xl transform hover:scale-110 transition opacity-0 pointer-events-none z-50">
        <i class="fas fa-arrow-up"></i>
    </button>

    <script>
        // Mobile Menu Toggle
        function toggleMenu() {
            document.getElementById('mobileMenu').classList.toggle('active');
        }

        // Close menu on link click
        document.querySelectorAll('#mobileMenu a').forEach(link => {
            link.addEventListener('click', () => toggleMenu());
        });

        // Sticky Nav & Scroll to Top
        window.addEventListener('scroll', () => {
            const nav = document.querySelector('.sticky-nav');
            const scrollBtn = document.getElementById('scrollTop');

            if (window.scrollY > 100) {
                nav.classList.add('scrolled');
                scrollBtn.classList.remove('opacity-0', 'pointer-events-none');
            } else {
                nav.classList.remove('scrolled');
                scrollBtn.classList.add('opacity-0', 'pointer-events-none');
            }
        });

        // Scroll to Top
        document.getElementById('scrollTop').addEventListener('click', () => {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });

        // Smooth Scrolling
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    const offset = 80;
                    const position = target.getBoundingClientRect().top + window.pageYOffset - offset;
                    window.scrollTo({
                        top: position,
                        behavior: 'smooth'
                    });
                }
            });
        });

        // Contact Form
        document.getElementById('contactForm').addEventListener('submit', function(e) {
            e.preventDefault();
            alert('Thank you! We will contact you soon.');
            this.reset();
        });

        // Animate on Scroll
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, {
            threshold: 0.1
        });

        document.querySelectorAll('.card-hover, .product-hover').forEach(el => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(30px)';
            el.style.transition = 'all 0.6s ease';
            observer.observe(el);
        });
    </script>
</body>

</html>