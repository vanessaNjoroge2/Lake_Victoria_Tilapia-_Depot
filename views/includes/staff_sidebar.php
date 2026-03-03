<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!-- Sidebar -->
<div class="fixed inset-y-0 left-0 w-64 bg-blue-800 shadow-lg z-40">
    <div class="flex items-center justify-center h-16 bg-blue-900">
        <div class="flex items-center space-x-2">
            <i class="fas fa-fish text-white text-2xl"></i>
            <span class="text-white text-xl font-bold"><?php echo SITE_NAME; ?></span>
        </div>
    </div>

    <nav class="mt-8">
        <div class="px-4 space-y-2">
            <a href="dashboard.php"
                class="flex items-center px-4 py-3 text-white rounded-lg <?php echo $current_page == 'dashboard.php' ? 'bg-blue-700' : 'hover:bg-blue-700'; ?>">
                <i class="fas fa-tachometer-alt mr-3"></i>
                <span>Dashboard</span>
            </a>

            <a href="fish_list.php"
                class="flex items-center px-4 py-3 text-white rounded-lg <?php echo $current_page == 'fish_list.php' ? 'bg-blue-700' : 'hover:bg-blue-700'; ?>">
                <i class="fas fa-fish mr-3"></i>
                <span>Fish Management</span>
            </a>

            <a href="orders.php"
                class="flex items-center px-4 py-3 text-white rounded-lg <?php echo $current_page == 'orders.php' ? 'bg-blue-700' : 'hover:bg-blue-700'; ?>">
                <i class="fas fa-shopping-cart mr-3"></i>
                <span>Order Management</span>
            </a>

            <?php if ($_SESSION['role'] == 'admin'): ?>
                <a href="users_list.php"
                    class="flex items-center px-4 py-3 text-white rounded-lg <?php echo $current_page == 'users_list.php' ? 'bg-blue-700' : 'hover:bg-blue-700'; ?>">
                    <i class="fas fa-users mr-3"></i>
                    <span>User Management</span>
                </a>
            <?php endif; ?>

            <a href="../auth/logout.php"
                class="flex items-center px-4 py-3 text-white rounded-lg hover:bg-red-600 mt-8">
                <i class="fas fa-sign-out-alt mr-3"></i>
                <span>Logout</span>
            </a>
        </div>
    </nav>
</div>