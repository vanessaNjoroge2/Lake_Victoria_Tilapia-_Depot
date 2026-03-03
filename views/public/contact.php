<?php

/**
 * Contact Page - Lake Victoria Tilapia Depot
 * Contact form, location, and contact information
 */
require_once '../../config/config.php';
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - <?php echo SITE_NAME; ?></title>
    <meta name="description" content="Get in touch with Lake Victoria Tilapia Depot. Contact us for orders, inquiries, or bulk supply needs.">

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
                    <a href="<?php echo BASE_URL; ?>/views/public/contact.php" class="text-cyan-600 font-bold border-b-2 border-cyan-600">
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
                <a href="<?php echo BASE_URL; ?>/views/public/contact.php" class="block py-2 text-cyan-600 font-bold">
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
            <h1 class="text-5xl lg:text-6xl font-bold mb-6">Get In Touch</h1>
            <p class="text-xl lg:text-2xl text-blue-50 max-w-3xl mx-auto">
                We're here to help! Contact us for orders, inquiries, or bulk supply needs
            </p>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="py-20">
        <div class="container mx-auto px-4 lg:px-8">
            <div class="grid lg:grid-cols-3 gap-8 mb-16">
                <!-- Contact Cards -->
                <div class="card-hover bg-white rounded-2xl p-8 shadow-xl text-center">
                    <div class="bg-gradient-to-br from-cyan-500 to-blue-600 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-map-marker-alt text-3xl text-white"></i>
                    </div>
                    <h3 class="font-bold text-xl mb-3 text-gray-800">Visit Us</h3>
                    <p class="text-gray-600 mb-2">Lake Victoria Fish Market</p>
                    <p class="text-gray-600 mb-2">Kisumu, Kenya</p>
                    <p class="text-sm text-gray-500 italic">Near the main landing site</p>
                </div>

                <div class="card-hover bg-white rounded-2xl p-8 shadow-xl text-center">
                    <div class="bg-gradient-to-br from-emerald-500 to-teal-600 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-phone-alt text-3xl text-white"></i>
                    </div>
                    <h3 class="font-bold text-xl mb-3 text-gray-800">Call Us</h3>
                    <p class="text-gray-600 mb-4"><?php echo ADMIN_PHONE; ?></p>
                    <a href="tel:<?php echo ADMIN_PHONE; ?>" class="inline-block bg-cyan-500 text-white px-6 py-2 rounded-full text-sm font-semibold hover:bg-cyan-600 transition mb-2">
                        <i class="fas fa-phone mr-2"></i>Call Now
                    </a>
                    <br>
                    <a href="https://wa.me/<?php echo str_replace('+', '', ADMIN_PHONE); ?>" class="inline-block bg-green-500 text-white px-6 py-2 rounded-full text-sm font-semibold hover:bg-green-600 transition">
                        <i class="fab fa-whatsapp mr-2"></i>WhatsApp
                    </a>
                </div>

                <div class="card-hover bg-white rounded-2xl p-8 shadow-xl text-center">
                    <div class="bg-gradient-to-br from-purple-500 to-indigo-600 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-envelope text-3xl text-white"></i>
                    </div>
                    <h3 class="font-bold text-xl mb-3 text-gray-800">Email Us</h3>
                    <p class="text-gray-600 mb-4 break-words"><?php echo ADMIN_EMAIL; ?></p>
                    <a href="mailto:<?php echo ADMIN_EMAIL; ?>" class="inline-block bg-purple-500 text-white px-6 py-2 rounded-full text-sm font-semibold hover:bg-purple-600 transition">
                        <i class="fas fa-envelope mr-2"></i>Send Email
                    </a>
                </div>
            </div>

            <!-- Contact Form and Map -->
            <div class="grid lg:grid-cols-2 gap-8">
                <!-- Contact Form -->
                <div class="bg-white rounded-2xl shadow-xl p-8">
                    <h2 class="text-3xl font-bold mb-6 text-gray-800">Send Us a Message</h2>
                    <form id="contactForm" class="space-y-6">
                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">
                                <i class="fas fa-user mr-2 text-cyan-500"></i>Your Name *
                            </label>
                            <input type="text" name="name" required
                                class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none transition"
                                placeholder="John Doe">
                        </div>

                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">
                                <i class="fas fa-phone mr-2 text-cyan-500"></i>Phone Number *
                            </label>
                            <input type="tel" name="phone" required
                                class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none transition"
                                placeholder="+254 700 000 000">
                        </div>

                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">
                                <i class="fas fa-envelope mr-2 text-cyan-500"></i>Email Address *
                            </label>
                            <input type="email" name="email" required
                                class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none transition"
                                placeholder="john@example.com">
                        </div>

                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">
                                <i class="fas fa-tag mr-2 text-cyan-500"></i>Subject *
                            </label>
                            <select name="subject" required
                                class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none transition">
                                <option value="">Select a subject</option>
                                <option value="general">General Inquiry</option>
                                <option value="order">Place an Order</option>
                                <option value="bulk">Bulk Supply Request</option>
                                <option value="service">Service Question</option>
                                <option value="feedback">Feedback/Complaint</option>
                                <option value="other">Other</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">
                                <i class="fas fa-comment mr-2 text-cyan-500"></i>Your Message *
                            </label>
                            <textarea name="message" rows="5" required
                                class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none resize-none transition"
                                placeholder="Tell us how we can help you..."></textarea>
                        </div>

                        <button type="submit"
                            class="w-full bg-gradient-to-r from-cyan-500 to-blue-600 text-white py-4 rounded-lg font-bold text-lg hover:shadow-xl transform hover:scale-105 transition">
                            <i class="fas fa-paper-plane mr-2"></i>Send Message
                        </button>

                        <p class="text-sm text-gray-500 text-center">
                            <i class="fas fa-info-circle mr-1"></i>
                            We typically respond within 24 hours
                        </p>
                    </form>

                    <div id="formMessage" class="hidden mt-4"></div>
                </div>

                <!-- Map and Additional Info -->
                <div class="space-y-6">
                    <!-- Google Map -->
                    <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
                        <div class="bg-gradient-to-r from-cyan-600 to-blue-700 text-white p-4">
                            <h3 class="font-bold text-xl">
                                <i class="fas fa-map-marked-alt mr-2"></i>Find Us Here
                            </h3>
                        </div>
                        <div class="relative h-96">
                            <!-- Google Maps Embed - Replace with actual coordinates -->
                            <iframe
                                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d127635.13894623456!2d34.75!3d-0.1!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x182aa471db.967%3A0x2d65b30ec85e2f55!2sKisumu%2C%20Kenya!5e0!3m2!1sen!2ske!4v1234567890"
                                class="w-full h-full border-0"
                                allowfullscreen=""
                                loading="lazy"
                                referrerpolicy="no-referrer-when-downgrade">
                            </iframe>
                        </div>
                    </div>

                    <!-- Business Hours -->
                    <div class="bg-white rounded-2xl shadow-xl p-6">
                        <h3 class="font-bold text-xl mb-4 text-gray-800">
                            <i class="fas fa-clock mr-2 text-cyan-600"></i>Business Hours
                        </h3>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center pb-2 border-b">
                                <span class="font-semibold text-gray-700">Monday - Friday</span>
                                <span class="text-cyan-600 font-bold">6:00 AM - 8:00 PM</span>
                            </div>
                            <div class="flex justify-between items-center pb-2 border-b">
                                <span class="font-semibold text-gray-700">Saturday</span>
                                <span class="text-cyan-600 font-bold">6:00 AM - 9:00 PM</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="font-semibold text-gray-700">Sunday</span>
                                <span class="text-cyan-600 font-bold">7:00 AM - 6:00 PM</span>
                            </div>
                        </div>
                        <div class="mt-4 bg-cyan-50 border-l-4 border-cyan-500 p-4">
                            <p class="text-sm text-gray-700">
                                <i class="fas fa-info-circle text-cyan-500 mr-2"></i>
                                <strong>Note:</strong> Fish is freshest in the morning! Visit us early for the best selection.
                            </p>
                        </div>
                    </div>

                    <!-- Quick Contact Options -->
                    <div class="bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl shadow-xl p-6 text-white text-center">
                        <i class="fab fa-whatsapp text-5xl mb-4"></i>
                        <h3 class="font-bold text-2xl mb-3">Prefer WhatsApp?</h3>
                        <p class="mb-6 text-green-50">Get instant responses to your questions</p>
                        <a href="https://wa.me/<?php echo str_replace('+', '', ADMIN_PHONE); ?>?text=Hello%20Lake%20Victoria%20Tilapia%20Depot!%20I%20would%20like%20to%20inquire%20about..."
                            class="inline-block bg-white text-green-600 px-8 py-3 rounded-full font-bold hover:shadow-xl transform hover:scale-105 transition">
                            <i class="fab fa-whatsapp mr-2"></i>Chat on WhatsApp
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="py-16 bg-gradient-to-br from-cyan-50 to-blue-50">
        <div class="container mx-auto px-4 lg:px-8 max-w-4xl">
            <div class="text-center mb-12">
                <h2 class="text-4xl font-bold text-gray-800 mb-4">Frequently Asked Questions</h2>
                <p class="text-gray-600">Quick answers to common questions</p>
            </div>

            <div class="space-y-4">
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <h3 class="font-bold text-lg mb-2 text-gray-800">
                        <i class="fas fa-question-circle text-cyan-500 mr-2"></i>
                        Do you deliver?
                    </h3>
                    <p class="text-gray-600">Yes! We offer free delivery within Kisumu for orders over 20kg. For smaller orders or deliveries outside Kisumu, please contact us for delivery fees.</p>
                </div>

                <div class="bg-white rounded-xl shadow-lg p-6">
                    <h3 class="font-bold text-lg mb-2 text-gray-800">
                        <i class="fas fa-question-circle text-cyan-500 mr-2"></i>
                        Can I order in bulk for an event?
                    </h3>
                    <p class="text-gray-600">Absolutely! We specialize in bulk supply for restaurants, hotels, weddings, and events. Contact us at least 24 hours in advance for large orders.</p>
                </div>

                <div class="bg-white rounded-xl shadow-lg p-6">
                    <h3 class="font-bold text-lg mb-2 text-gray-800">
                        <i class="fas fa-question-circle text-cyan-500 mr-2"></i>
                        Do you clean the fish?
                    </h3>
                    <p class="text-gray-600">Yes, we provide free cleaning service with every purchase. We can scale, gut, and cut the fish to your specifications.</p>
                </div>

                <div class="bg-white rounded-xl shadow-lg p-6">
                    <h3 class="font-bold text-lg mb-2 text-gray-800">
                        <i class="fas fa-question-circle text-cyan-500 mr-2"></i>
                        What payment methods do you accept?
                    </h3>
                    <p class="text-gray-600">We accept cash, M-Pesa, and bank transfers. Online orders through our system can be paid via M-Pesa.</p>
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

        // Handle form submission
        document.getElementById('contactForm').addEventListener('submit', function(e) {
            e.preventDefault();

            // Get form data
            const formData = new FormData(this);
            const data = Object.fromEntries(formData);

            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Sending...';
            submitBtn.disabled = true;

            // Simulate form submission (in production, send to backend)
            setTimeout(() => {
                // Show success message
                const messageDiv = document.getElementById('formMessage');
                messageDiv.className = 'bg-green-50 border-l-4 border-green-500 p-4 rounded';
                messageDiv.innerHTML = `
                    <div class="flex items-center">
                        <i class="fas fa-check-circle text-green-500 text-xl mr-3"></i>
                        <div>
                            <p class="font-bold text-green-800">Message sent successfully!</p>
                            <p class="text-sm text-green-700">We'll get back to you within 24 hours.</p>
                        </div>
                    </div>
                `;
                messageDiv.classList.remove('hidden');

                // Reset form
                this.reset();

                // Reset button
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;

                // Hide message after 5 seconds
                setTimeout(() => {
                    messageDiv.classList.add('hidden');
                }, 5000);
            }, 1500);
        });
    </script>
</body>

</html>