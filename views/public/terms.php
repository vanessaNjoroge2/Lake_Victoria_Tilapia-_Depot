<?php
require_once '../../config/config.php';
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms and Conditions - <?php echo SITE_NAME; ?></title>
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
                    <a href="<?php echo BASE_URL; ?>/views/public/about.php" class="text-gray-700 hover:text-cyan-600 font-medium transition">
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
                <a href="<?php echo BASE_URL; ?>/views/public/about.php" class="block py-2 text-gray-700 hover:text-cyan-600">
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
            <h1 class="text-5xl font-bold mb-4">Terms and Conditions</h1>
            <p class="text-xl text-blue-100 max-w-3xl mx-auto">Please read these terms carefully before using our services</p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container mx-auto px-4 py-16">
        <div class="max-w-4xl mx-auto bg-white rounded-2xl shadow-lg p-8 md:p-12">
            <div class="mb-8">
                <p class="text-gray-600 mb-4"><strong>Last Updated:</strong> <?php echo date('F d, Y'); ?></p>
                <p class="text-gray-700 leading-relaxed">
                    Welcome to Lake Victoria Tilapia Depot. By accessing and using our website and services, you agree to be bound by these Terms and Conditions. Please read them carefully.
                </p>
            </div>

            <!-- Section 1 -->
            <div class="mb-10">
                <h2 class="text-2xl font-bold text-gray-800 mb-4 flex items-center">
                    <span class="bg-blue-100 text-blue-600 w-8 h-8 rounded-full flex items-center justify-center mr-3 text-sm">1</span>
                    Agreement to Terms
                </h2>
                <p class="text-gray-700 leading-relaxed ml-11">
                    By creating an account or placing an order on Lake Victoria Tilapia Depot, you agree to comply with and be legally bound by these Terms and Conditions. If you do not agree to these terms, please do not use our services.
                </p>
            </div>

            <!-- Section 2 -->
            <div class="mb-10">
                <h2 class="text-2xl font-bold text-gray-800 mb-4 flex items-center">
                    <span class="bg-blue-100 text-blue-600 w-8 h-8 rounded-full flex items-center justify-center mr-3 text-sm">2</span>
                    User Accounts
                </h2>
                <div class="ml-11 text-gray-700 leading-relaxed space-y-3">
                    <p><strong>2.1 Registration:</strong> You must provide accurate and complete information when creating an account. You are responsible for maintaining the confidentiality of your account credentials.</p>
                    <p><strong>2.2 Account Security:</strong> You agree to notify us immediately of any unauthorized use of your account. We are not liable for any loss or damage arising from your failure to protect your account information.</p>
                    <p><strong>2.3 Account Types:</strong> We offer different account types (Customer, Staff, Admin) with varying levels of access and privileges.</p>
                </div>
            </div>

            <!-- Section 3 -->
            <div class="mb-10">
                <h2 class="text-2xl font-bold text-gray-800 mb-4 flex items-center">
                    <span class="bg-blue-100 text-blue-600 w-8 h-8 rounded-full flex items-center justify-center mr-3 text-sm">3</span>
                    Orders and Payment
                </h2>
                <div class="ml-11 text-gray-700 leading-relaxed space-y-3">
                    <p><strong>3.1 Product Availability:</strong> All products are subject to availability. We reserve the right to limit quantities or discontinue products at any time.</p>
                    <p><strong>3.2 Pricing:</strong> Prices are displayed in Kenyan Shillings (KES) and may change without notice. The price applicable is the one displayed at the time of order confirmation.</p>
                    <p><strong>3.3 Payment Methods:</strong> We accept M-PESA and other approved payment methods. Payment must be completed before order processing begins.</p>
                    <p><strong>3.4 Order Confirmation:</strong> You will receive an email and/or SMS confirmation once your order is successfully placed.</p>
                </div>
            </div>

            <!-- Section 4 -->
            <div class="mb-10">
                <h2 class="text-2xl font-bold text-gray-800 mb-4 flex items-center">
                    <span class="bg-blue-100 text-blue-600 w-8 h-8 rounded-full flex items-center justify-center mr-3 text-sm">4</span>
                    Delivery
                </h2>
                <div class="ml-11 text-gray-700 leading-relaxed space-y-3">
                    <p><strong>4.1 Delivery Areas:</strong> We deliver to specified areas around Lake Victoria region. Delivery fees may vary based on location.</p>
                    <p><strong>4.2 Delivery Times:</strong> While we strive to deliver within the estimated timeframe, delays may occur due to unforeseen circumstances. We are not liable for delays beyond our control.</p>
                    <p><strong>4.3 Receiving Orders:</strong> Someone must be available to receive the order at the delivery address. We may require identification verification upon delivery.</p>
                </div>
            </div>

            <!-- Section 5 -->
            <div class="mb-10">
                <h2 class="text-2xl font-bold text-gray-800 mb-4 flex items-center">
                    <span class="bg-blue-100 text-blue-600 w-8 h-8 rounded-full flex items-center justify-center mr-3 text-sm">5</span>
                    Product Quality and Returns
                </h2>
                <div class="ml-11 text-gray-700 leading-relaxed space-y-3">
                    <p><strong>5.1 Quality Guarantee:</strong> We guarantee the freshness and quality of our tilapia. All fish are sourced daily from Lake Victoria and undergo strict quality checks.</p>
                    <p><strong>5.2 Complaints:</strong> Any complaints regarding product quality must be reported within 2 hours of delivery with photographic evidence.</p>
                    <p><strong>5.3 Refunds:</strong> Refunds or replacements will be provided for legitimate quality issues. Due to the perishable nature of our products, returns are not accepted once delivery is completed.</p>
                    <p><strong>5.4 Cancellations:</strong> Orders can be cancelled before processing begins. Once an order enters the "Processing" stage, cancellation may not be possible.</p>
                </div>
            </div>

            <!-- Section 6 -->
            <div class="mb-10">
                <h2 class="text-2xl font-bold text-gray-800 mb-4 flex items-center">
                    <span class="bg-blue-100 text-blue-600 w-8 h-8 rounded-full flex items-center justify-center mr-3 text-sm">6</span>
                    Privacy and Data Protection
                </h2>
                <div class="ml-11 text-gray-700 leading-relaxed space-y-3">
                    <p><strong>6.1 Personal Information:</strong> We collect and process personal information in accordance with our Privacy Policy and applicable data protection laws.</p>
                    <p><strong>6.2 Data Usage:</strong> Your information is used to process orders, provide customer service, and send important notifications about your orders.</p>
                    <p><strong>6.3 Third-Party Services:</strong> We use third-party services (M-PESA, email providers, SMS gateways) to facilitate transactions and communications. These services have their own privacy policies.</p>
                    <p><strong>6.4 Data Security:</strong> We implement appropriate security measures to protect your personal information from unauthorized access.</p>
                </div>
            </div>

            <!-- Section 7 -->
            <div class="mb-10">
                <h2 class="text-2xl font-bold text-gray-800 mb-4 flex items-center">
                    <span class="bg-blue-100 text-blue-600 w-8 h-8 rounded-full flex items-center justify-center mr-3 text-sm">7</span>
                    Prohibited Activities
                </h2>
                <div class="ml-11 text-gray-700 leading-relaxed space-y-3">
                    <p>You agree not to:</p>
                    <ul class="list-disc ml-6 space-y-2">
                        <li>Use our services for any illegal purposes</li>
                        <li>Attempt to gain unauthorized access to our systems</li>
                        <li>Interfere with the proper functioning of our website</li>
                        <li>Post false or misleading information</li>
                        <li>Violate any applicable laws or regulations</li>
                        <li>Impersonate another person or entity</li>
                    </ul>
                </div>
            </div>

            <!-- Section 8 -->
            <div class="mb-10">
                <h2 class="text-2xl font-bold text-gray-800 mb-4 flex items-center">
                    <span class="bg-blue-100 text-blue-600 w-8 h-8 rounded-full flex items-center justify-center mr-3 text-sm">8</span>
                    Limitation of Liability
                </h2>
                <div class="ml-11 text-gray-700 leading-relaxed space-y-3">
                    <p><strong>8.1 Service Availability:</strong> We strive to maintain continuous service availability but do not guarantee uninterrupted access to our website.</p>
                    <p><strong>8.2 Product Liability:</strong> Our liability is limited to the purchase price of the product. We are not liable for any indirect, consequential, or incidental damages.</p>
                    <p><strong>8.3 Force Majeure:</strong> We are not liable for failure to perform due to circumstances beyond our reasonable control, including natural disasters, strikes, or government actions.</p>
                </div>
            </div>

            <!-- Section 9 -->
            <div class="mb-10">
                <h2 class="text-2xl font-bold text-gray-800 mb-4 flex items-center">
                    <span class="bg-blue-100 text-blue-600 w-8 h-8 rounded-full flex items-center justify-center mr-3 text-sm">9</span>
                    Intellectual Property
                </h2>
                <div class="ml-11 text-gray-700 leading-relaxed space-y-3">
                    <p><strong>9.1 Ownership:</strong> All content on this website, including text, graphics, logos, and software, is the property of Lake Victoria Tilapia Depot and is protected by copyright laws.</p>
                    <p><strong>9.2 Usage Rights:</strong> You may not reproduce, distribute, or create derivative works from our content without written permission.</p>
                </div>
            </div>

            <!-- Section 10 -->
            <div class="mb-10">
                <h2 class="text-2xl font-bold text-gray-800 mb-4 flex items-center">
                    <span class="bg-blue-100 text-blue-600 w-8 h-8 rounded-full flex items-center justify-center mr-3 text-sm">10</span>
                    Changes to Terms
                </h2>
                <div class="ml-11 text-gray-700 leading-relaxed">
                    <p>We reserve the right to modify these Terms and Conditions at any time. Changes will be effective immediately upon posting to the website. Your continued use of our services after changes are posted constitutes acceptance of the modified terms.</p>
                </div>
            </div>

            <!-- Section 11 -->
            <div class="mb-10">
                <h2 class="text-2xl font-bold text-gray-800 mb-4 flex items-center">
                    <span class="bg-blue-100 text-blue-600 w-8 h-8 rounded-full flex items-center justify-center mr-3 text-sm">11</span>
                    Contact Information
                </h2>
                <div class="ml-11 text-gray-700 leading-relaxed">
                    <p class="mb-4">For questions about these Terms and Conditions, please contact us:</p>
                    <div class="bg-blue-50 border-l-4 border-blue-600 p-4 rounded">
                        <p class="mb-2"><i class="fas fa-envelope text-blue-600 mr-2"></i><strong>Email:</strong> <?php echo ADMIN_EMAIL; ?></p>
                        <p class="mb-2"><i class="fas fa-phone text-blue-600 mr-2"></i><strong>Phone:</strong> <?php echo ADMIN_PHONE; ?></p>
                        <p><i class="fas fa-map-marker-alt text-blue-600 mr-2"></i><strong>Address:</strong> Lake Victoria Region</p>
                    </div>
                </div>
            </div>

            <!-- Agreement Notice -->
            <div class="mt-12 bg-gradient-to-r from-blue-600 to-blue-800 text-white rounded-xl p-6">
                <h3 class="text-xl font-bold mb-2">Agreement Acknowledgment</h3>
                <p>By using Lake Victoria Tilapia Depot's services, you acknowledge that you have read, understood, and agree to be bound by these Terms and Conditions.</p>
            </div>
        </div>
    </div>

    <!-- CTA Section -->
    <section class="py-16 bg-gradient-to-r from-cyan-600 to-blue-700 text-white">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-4xl font-bold mb-4">Ready to Get Started?</h2>
            <p class="text-xl mb-8 text-blue-50">Join us today and enjoy fresh tilapia delivered to your doorstep</p>
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