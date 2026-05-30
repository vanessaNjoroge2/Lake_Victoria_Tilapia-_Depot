<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!-- Sidebar -->
<div class="fixed inset-y-0 left-0 w-64 bg-blue-800 shadow-lg z-40 flex flex-col">
    <div class="flex items-center justify-center h-16 bg-blue-900 flex-shrink-0">
        <div class="flex items-center space-x-2">
            <i class="fas fa-fish text-white text-2xl"></i>
            <span class="text-white text-xl font-bold"><?php echo SITE_NAME; ?></span>
        </div>
    </div>

    <!-- Scrollable Nav -->
    <nav class="flex-1 overflow-y-auto mt-4 pb-6 scrollbar-thin scrollbar-thumb-blue-600">
        <div class="px-4 space-y-6">
            <!-- Core Section -->
            <div>
                <span class="text-xs font-semibold text-blue-300 uppercase tracking-wider block mb-2 px-2">Core System</span>
                <div class="space-y-1">
                    <a href="dashboard.php"
                        class="flex items-center px-3 py-2 text-sm text-white rounded-lg <?php echo $current_page == 'dashboard.php' ? 'bg-blue-700' : 'hover:bg-blue-700 transition duration-150'; ?>">
                        <i class="fas fa-tachometer-alt w-5 text-center mr-3 text-blue-200"></i>
                        <span>Dashboard</span>
                    </a>
                    <a href="fish_list.php"
                        class="flex items-center px-3 py-2 text-sm text-white rounded-lg <?php echo $current_page == 'fish_list.php' ? 'bg-blue-700' : 'hover:bg-blue-700 transition duration-150'; ?>">
                        <i class="fas fa-fish w-5 text-center mr-3 text-blue-200"></i>
                        <span>Fish Inventory</span>
                    </a>
                    <a href="orders.php"
                        class="flex items-center px-3 py-2 text-sm text-white rounded-lg <?php echo $current_page == 'orders.php' ? 'bg-blue-700' : 'hover:bg-blue-700 transition duration-150'; ?>">
                        <i class="fas fa-shopping-cart w-5 text-center mr-3 text-blue-200"></i>
                        <span>Orders</span>
                    </a>
                </div>
            </div>

            <!-- POS Operations Section -->
            <div>
                <span class="text-xs font-semibold text-blue-300 uppercase tracking-wider block mb-2 px-2">POS Operations</span>
                <div class="space-y-1">
                    <a href="pos_sale.php"
                        class="flex items-center px-3 py-2 text-sm text-white rounded-lg <?php echo $current_page == 'pos_sale.php' ? 'bg-blue-700' : 'hover:bg-blue-700 transition duration-150'; ?>">
                        <i class="fas fa-cash-register w-5 text-center mr-3 text-blue-200"></i>
                        <span class="font-medium text-cyan-200">POS Cashier</span>
                    </a>
                    <a href="wholesale_customers.php"
                        class="flex items-center px-3 py-2 text-sm text-white rounded-lg <?php echo $current_page == 'wholesale_customers.php' || $current_page == 'customer_account.php' ? 'bg-blue-700' : 'hover:bg-blue-700 transition duration-150'; ?>">
                        <i class="fas fa-users w-5 text-center mr-3 text-blue-200"></i>
                        <span>Wholesale Ledger</span>
                    </a>
                    <a href="frying_batches.php"
                        class="flex items-center px-3 py-2 text-sm text-white rounded-lg <?php echo $current_page == 'frying_batches.php' ? 'bg-blue-700' : 'hover:bg-blue-700 transition duration-150'; ?>">
                        <i class="fas fa-fire-burner w-5 text-center mr-3 text-blue-200"></i>
                        <span>Frying Batches</span>
                    </a>
                    <a href="stock_deliveries.php"
                        class="flex items-center px-3 py-2 text-sm text-white rounded-lg <?php echo $current_page == 'stock_deliveries.php' ? 'bg-blue-700' : 'hover:bg-blue-700 transition duration-150'; ?>">
                        <i class="fas fa-truck-loading w-5 text-center mr-3 text-blue-200"></i>
                        <span>Stock Deliveries</span>
                    </a>
                    <a href="wastage.php"
                        class="flex items-center px-3 py-2 text-sm text-white rounded-lg <?php echo $current_page == 'wastage.php' ? 'bg-blue-700' : 'hover:bg-blue-700 transition duration-150'; ?>">
                        <i class="fas fa-trash-alt w-5 text-center mr-3 text-blue-200"></i>
                        <span>Wastage Logs</span>
                    </a>
                </div>
            </div>

            <!-- Admin and Security Section -->
            <div>
                <span class="text-xs font-semibold text-blue-300 uppercase tracking-wider block mb-2 px-2">Management & Security</span>
                <div class="space-y-1">
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                        <a href="users_list.php"
                            class="flex items-center px-3 py-2 text-sm text-white rounded-lg <?php echo $current_page == 'users_list.php' ? 'bg-blue-700' : 'hover:bg-blue-700 transition duration-150'; ?>">
                            <i class="fas fa-user-shield w-5 text-center mr-3 text-blue-200"></i>
                            <span>Staff Accounts</span>
                        </a>
                    <?php endif; ?>
                    
                    <?php if (in_array($_SESSION['role'] ?? '', ['admin', 'staff'])): ?>
                        <a href="pos_reports.php"
                            class="flex items-center px-3 py-2 text-sm text-white rounded-lg <?php echo $current_page == 'pos_reports.php' ? 'bg-blue-700' : 'hover:bg-blue-700 transition duration-150'; ?>">
                            <i class="fas fa-chart-line w-5 text-center mr-3 text-blue-200"></i>
                            <span>POS Reports</span>
                        </a>
                    <?php endif; ?>

                    <?php if ($_SESSION['role'] === 'admin'): ?>
                        <a href="eod_reconciliation.php"
                            class="flex items-center px-3 py-2 text-sm text-white rounded-lg <?php echo $current_page == 'eod_reconciliation.php' ? 'bg-blue-700' : 'hover:bg-blue-700 transition duration-150'; ?>">
                            <i class="fas fa-balance-scale w-5 text-center mr-3 text-blue-200"></i>
                            <span>EOD Reconcile</span>
                        </a>
                    <?php endif; ?>

                    <?php if (in_array($_SESSION['role'] ?? '', ['admin', 'staff'])): ?>
                        <a href="audit_log.php"
                            class="flex items-center px-3 py-2 text-sm text-white rounded-lg <?php echo $current_page == 'audit_log.php' ? 'bg-blue-700' : 'hover:bg-blue-700 transition duration-150'; ?>">
                            <i class="fas fa-history w-5 text-center mr-3 text-blue-200"></i>
                            <span>Security Audit</span>
                        </a>
                    <?php endif; ?>
                    <a href="security_settings.php"
                        class="flex items-center px-3 py-2 text-sm text-white rounded-lg <?php echo $current_page == 'security_settings.php' ? 'bg-blue-700' : 'hover:bg-blue-700 transition duration-150'; ?>">
                        <i class="fas fa-key w-5 text-center mr-3 text-blue-200"></i>
                        <span>Security Settings</span>
                    </a>
                </div>
            </div>

            <!-- Logout Link -->
            <div class="pt-4 border-t border-blue-700">
                <a href="../auth/logout.php"
                    class="flex items-center px-3 py-2 text-sm text-red-100 rounded-lg hover:bg-red-700 transition duration-150">
                    <i class="fas fa-sign-out-alt w-5 text-center mr-3"></i>
                    <span>Logout</span>
                </a>
            </div>
        </div>
    </nav>
</div>