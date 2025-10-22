<?php
// Staff sidebar component
?>
<div class="w-64 bg-blue-800 min-h-screen p-4">
    <div class="text-white text-2xl font-bold mb-8">
        <i class="fas fa-fish mr-2"></i>
        <?php echo SITE_NAME; ?>
    </div>
    <nav class="space-y-2">
        <a href="<?php echo BASE_URL; ?>/views/staff/dashboard.php"
            class="block text-white hover:bg-blue-700 p-3 rounded flex items-center <?php echo basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'bg-blue-700' : ''; ?>">
            <i class="fas fa-tachometer-alt mr-3"></i>Dashboard
        </a>
        <a href="<?php echo BASE_URL; ?>/views/staff/fish_list.php"
            class="block text-white hover:bg-blue-700 p-3 rounded flex items-center <?php echo basename($_SERVER['PHP_SELF']) === 'fish_list.php' ? 'bg-blue-700' : ''; ?>">
            <i class="fas fa-fish mr-3"></i>Fish Management
        </a>
        <a href="<?php echo BASE_URL; ?>/views/staff/orders.php"
            class="block text-white hover:bg-blue-700 p-3 rounded flex items-center <?php echo basename($_SERVER['PHP_SELF']) === 'orders.php' ? 'bg-blue-700' : ''; ?>">
            <i class="fas fa-shopping-cart mr-3"></i>Orders
        </a>

        <?php if ($_SESSION['role'] === 'admin'): ?>
            <a href="<?php echo BASE_URL; ?>/views/staff/users_list.php"
                class="block text-white hover:bg-blue-700 p-3 rounded flex items-center <?php echo basename($_SERVER['PHP_SELF']) === 'users_list.php' ? 'bg-blue-700' : ''; ?>">
                <i class="fas fa-users mr-3"></i>Staff Management
            </a>
        <?php endif; ?>

        <a href="<?php echo BASE_URL; ?>/handlers/auth_handler.php?action=logout"
            class="block text-white hover:bg-blue-700 p-3 rounded flex items-center mt-8">
            <i class="fas fa-sign-out-alt mr-3"></i>Logout
        </a>
    </nav>
</div>