<footer class="bg-gray-800 text-white mt-12">
    <div class="container mx-auto px-4 py-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div>
                <h3 class="text-lg font-semibold mb-4"><?php echo SITE_NAME; ?></h3>
                <p class="text-gray-300">Fresh tilapia from Lake Victoria, delivered to your doorstep. Quality and freshness guaranteed.</p>
            </div>

            <div>
                <h3 class="text-lg font-semibold mb-4">Contact Info</h3>
                <div class="space-y-2 text-gray-300">
                    <p><i class="fas fa-phone mr-2"></i> +254 700 000 000</p>
                    <p><i class="fas fa-envelope mr-2"></i> info@tilapiadepot.com</p>
                    <p><i class="fas fa-map-marker-alt mr-2"></i> Kisumu, Kenya</p>
                </div>
            </div>

            <div>
                <h3 class="text-lg font-semibold mb-4">Quick Links</h3>
                <div class="space-y-2">
                    <a href="<?php echo BASE_URL; ?>/landing.php" class="block text-gray-300 hover:text-white"><i class="fas fa-home mr-2"></i>Home</a>
                    <a href="<?php echo BASE_URL; ?>/views/customer/browse_fish.php" class="block text-gray-300 hover:text-white"><i class="fas fa-fish mr-2"></i>Browse Fish</a>
                    <a href="<?php echo BASE_URL; ?>/views/public/about.php" class="block text-gray-300 hover:text-white"><i class="fas fa-info-circle mr-2"></i>About Us</a>
                    <a href="<?php echo BASE_URL; ?>/views/public/terms.php" class="block text-gray-300 hover:text-white"><i class="fas fa-file-contract mr-2"></i>Terms & Conditions</a>
                </div>
            </div>
        </div>

        <div class="border-t border-gray-700 mt-8 pt-6 text-center text-gray-300">
            <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</p>
        </div>
    </div>
</footer>