<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../controllers/AuthController.php';
require_once __DIR__ . '/../../controllers/FishController.php';

$authController = new AuthController();
$authController->requireRole(['admin', 'staff']);

$fishController = new FishController();
$fishList = $fishController->getAllFish();

// Handle status toggle or delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $fish_id = $_POST['fish_id'] ?? '';

    if ($action === 'toggle_status' && $fish_id) {
        $fishController->toggleFishStatus($fish_id);
        header('Location: fish_list.php');
        exit();
    }

    if ($action === 'delete' && $fish_id) {
        $fishController->deleteFish($fish_id);
        header('Location: fish_list.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fish Management - <?php echo SITE_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex">

    <!-- Sidebar Layout -->
    <?php include '../includes/staff_sidebar.php'; ?>

    <!-- Main Workspace Container -->
    <div class="flex-1 ml-64 p-8">
        
        <!-- Header -->
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-800 font-sans">Fish Catalog & Inventory</h1>
                <p class="text-gray-600">Configure fish sizes, classifications (raw vs fried), retail/wholesale prices, and stock limits</p>
            </div>
            
            <a href="<?php echo BASE_URL; ?>/views/staff/add_fish.php" 
               class="bg-blue-600 text-white px-5 py-2.5 rounded-xl font-bold hover:bg-blue-700 shadow-md shadow-blue-500/20 transition duration-150 flex items-center">
                <i class="fas fa-plus mr-2"></i> Add New Product
            </a>
        </div>

        <!-- Inventory Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 flex items-center">
                <div class="p-3.5 bg-blue-100 text-blue-600 rounded-xl mr-4 flex-shrink-0">
                    <i class="fas fa-fish text-xl"></i>
                </div>
                <div>
                    <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Total Catalog</span>
                    <h3 class="text-xl font-bold text-slate-800 mt-0.5"><?php echo count($fishList); ?> products</h3>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 flex items-center">
                <div class="p-3.5 bg-green-100 text-green-600 rounded-xl mr-4 flex-shrink-0">
                    <i class="fas fa-eye text-xl"></i>
                </div>
                <div>
                    <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Active Status</span>
                    <h3 class="text-xl font-bold text-green-600 mt-0.5">
                        <?php echo count(array_filter($fishList, fn($fish) => $fish['is_active'])); ?> active
                    </h3>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 flex items-center">
                <div class="p-3.5 bg-red-100 text-red-600 rounded-xl mr-4 flex-shrink-0">
                    <i class="fas fa-triangle-exclamation text-xl"></i>
                </div>
                <div>
                    <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Low Stock Warnings</span>
                    <h3 class="text-xl font-bold text-red-600 mt-0.5">
                        <?php echo count(array_filter($fishList, fn($fish) => ($fish['stock_qty'] ?? $fish['stock_quantity']) < ($fish['low_stock_threshold'] ?? 10))); ?> items
                    </h3>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 flex items-center">
                <div class="p-3.5 bg-slate-100 text-slate-600 rounded-xl mr-4 flex-shrink-0">
                    <i class="fas fa-eye-slash text-xl"></i>
                </div>
                <div>
                    <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Archived / Inactive</span>
                    <h3 class="text-xl font-bold text-slate-600 mt-0.5">
                        <?php echo count(array_filter($fishList, fn($fish) => !$fish['is_active'])); ?> hidden
                    </h3>
                </div>
            </div>
        </div>

        <!-- Filter Controls -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 mb-6 flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Filter Catalog:</span>
                <button onclick="applyCatalogFilter('all')" id="btn-filter-all" class="px-4 py-2 rounded-xl text-xs font-bold bg-blue-50 text-blue-700 border border-blue-100">
                    All Fish
                </button>
                <button onclick="applyCatalogFilter('raw')" id="btn-filter-raw" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-50">
                    Raw Inventory
                </button>
                <button onclick="applyCatalogFilter('fried')" id="btn-filter-fried" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-50">
                    Fried Ready-to-Eat
                </button>
            </div>
            
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
                    <i class="fas fa-search text-xs"></i>
                </span>
                <input type="text" id="catalog-search" oninput="applyCatalogSearch()" placeholder="Search product name or size..."
                       class="pl-8 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-blue-500 w-64">
            </div>
        </div>

        <!-- Fish Inventory Ledger -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-5 border-b border-slate-200 bg-slate-50 flex justify-between items-center">
                <h3 class="font-bold text-slate-800 text-base"><i class="fas fa-list mr-2 text-slate-500"></i> Master Fish Catalog</h3>
                <span class="text-xs text-slate-500 font-semibold">Active Catalog</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-100 text-slate-500 text-[10px] font-bold uppercase tracking-wider border-b border-slate-200">
                            <th class="py-3.5 px-6">Product Details</th>
                            <th class="py-3.5 px-6">Classification</th>
                            <th class="py-3.5 px-6 text-right">Acquisition Cost</th>
                            <th class="py-3.5 px-6 text-right">Retail Price</th>
                            <th class="py-3.5 px-6 text-right">Wholesale Price</th>
                            <th class="py-3.5 px-6 text-center">Available Stock</th>
                            <th class="py-3.5 px-6 text-center">Status</th>
                            <th class="py-3.5 px-6 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="catalog-table-body" class="divide-y divide-slate-100 text-xs">
                        <?php if (empty($fishList)): ?>
                            <tr>
                                <td colspan="8" class="py-12 text-center text-slate-400">
                                    <i class="fas fa-fish text-4xl mb-3"></i>
                                    <p class="text-sm">No products found in the catalog.</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($fishList as $fish): ?>
                                <?php 
                                    $currentStock = $fish['stock_qty'] ?? $fish['stock_quantity'] ?? 0;
                                    $threshold = $fish['low_stock_threshold'] ?? 10;
                                    $isLow = $currentStock < $threshold;
                                ?>
                                <tr class="catalog-row hover:bg-slate-50/50 transition duration-100" 
                                    data-type="<?php echo htmlspecialchars($fish['type'] ?? 'raw'); ?>" 
                                    data-name="<?php echo htmlspecialchars(strtolower($fish['name'] . ' ' . ($fish['size'] ?? ''))); ?>">
                                    
                                    <td class="py-4 px-6">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <img class="h-10 w-10 rounded-full object-cover border border-slate-100 shadow-sm"
                                                    src="<?php echo BASE_URL . '/uploads/' . ($fish['image_url'] ?: 'default_fish.png'); ?>"
                                                    alt="<?php echo htmlspecialchars($fish['name']); ?>">
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-bold text-slate-800"><?php echo htmlspecialchars($fish['name']); ?></div>
                                                <div class="text-[10px] text-slate-400 font-semibold mt-0.5">Size: <?php echo htmlspecialchars($fish['size'] ?? 'Size 1'); ?> | Weight: <?php echo htmlspecialchars($fish['weight_range']); ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-4 px-6">
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wide
                                            <?php echo ($fish['type'] ?? 'raw') === 'fried' ? 'bg-amber-100 text-amber-800 border border-amber-200' : 'bg-blue-100 text-blue-800 border border-blue-200'; ?>">
                                            <?php echo htmlspecialchars($fish['type'] ?? 'raw'); ?>
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 text-right font-semibold text-slate-500">
                                        Ksh <?php echo number_format($fish['cost_price'] ?? 0.0, 2); ?>
                                    </td>
                                    <td class="py-4 px-6 text-right font-bold text-slate-800">
                                        Ksh <?php echo number_format($fish['retail_price'] ?? $fish['price'] ?? 0.0, 2); ?>
                                    </td>
                                    <td class="py-4 px-6 text-right font-bold text-blue-600">
                                        Ksh <?php echo number_format($fish['wholesale_price'] ?? ($fish['price'] ?? 0) * 0.90, 2); ?>
                                    </td>
                                    <td class="py-4 px-6 text-center">
                                        <div class="inline-block text-center">
                                            <span class="font-extrabold text-xs px-2.5 py-1 rounded-lg <?php echo $isLow ? 'bg-red-50 text-red-700 border border-red-100' : 'bg-slate-50 text-slate-700 border border-slate-100'; ?> border">
                                                <?php echo $currentStock; ?> <?php echo htmlspecialchars($fish['unit'] ?? 'piece'); ?>s
                                            </span>
                                            <?php if ($isLow): ?>
                                                <div class="text-[9px] text-red-500 font-bold mt-1 uppercase tracking-wider"><i class="fas fa-exclamation-triangle"></i> Low stock (min: <?php echo $threshold; ?>)</div>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="py-4 px-6 text-center">
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wide <?php echo $fish['is_active'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                            <?php echo $fish['is_active'] ? 'Active' : 'Inactive'; ?>
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <a href="<?php echo BASE_URL; ?>/views/staff/edit_fish.php?id=<?php echo $fish['id']; ?>"
                                               class="bg-slate-100 text-slate-700 px-2.5 py-1.5 rounded-lg hover:bg-blue-600 hover:text-white font-bold transition duration-150" title="Edit Product">
                                                <i class="fas fa-edit mr-1"></i> Edit
                                            </a>
                                            <form method="POST" class="inline" onsubmit="return confirm('Are you sure you want to change the status of this product?');">
                                                <input type="hidden" name="fish_id" value="<?php echo $fish['id']; ?>">
                                                <input type="hidden" name="action" value="toggle_status">
                                                <button type="submit" class="px-2.5 py-1.5 rounded-lg font-bold transition duration-150 
                                                    <?php echo $fish['is_active'] ? 'bg-yellow-50 text-yellow-700 border border-yellow-100 hover:bg-yellow-600 hover:text-white' : 'bg-green-50 text-green-700 border border-green-100 hover:bg-green-600 hover:text-white'; ?>">
                                                    <i class="fas fa-<?php echo $fish['is_active'] ? 'pause' : 'play'; ?> mr-1"></i>
                                                    <?php echo $fish['is_active'] ? 'Deactivate' : 'Activate'; ?>
                                                </button>
                                            </form>
                                            <form method="POST" class="inline" onsubmit="return confirm('WARNING: Are you absolutely sure you want to permanently delete this product? All sales logs referencing this item will maintain record, but it will be wiped from inventory.');">
                                                <input type="hidden" name="fish_id" value="<?php echo $fish['id']; ?>">
                                                <input type="hidden" name="action" value="delete">
                                                <button type="submit" class="bg-red-50 text-red-700 px-2.5 py-1.5 rounded-lg hover:bg-red-600 hover:text-white font-bold border border-red-100 transition duration-150" title="Delete Product">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- Scripts Logic -->
    <script>
        let currentTypeFilter = 'all';

        function applyCatalogFilter(type) {
            currentTypeFilter = type;
            
            // Highlight active button
            ['all', 'raw', 'fried'].forEach(t => {
                const btn = document.getElementById(`btn-filter-${t}`);
                if (t === type) {
                    btn.className = "px-4 py-2 rounded-xl text-xs font-bold bg-blue-50 text-blue-700 border border-blue-100";
                } else {
                    btn.className = "px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-50";
                }
            });

            filterRows();
        }

        function applyCatalogSearch() {
            filterRows();
        }

        function filterRows() {
            const searchQuery = document.getElementById('catalog-search').value.toLowerCase().trim();
            const rows = document.querySelectorAll('.catalog-row');

            rows.forEach(row => {
                const rowType = row.getAttribute('data-type');
                const rowName = row.getAttribute('data-name');

                const matchesType = (currentTypeFilter === 'all' || rowType === currentTypeFilter);
                const matchesSearch = (!searchQuery || rowName.includes(searchQuery));

                if (matchesType && matchesSearch) {
                    row.classList.remove('hidden');
                } else {
                    row.classList.add('hidden');
                }
            });
        }
    </script>
</body>
</html>