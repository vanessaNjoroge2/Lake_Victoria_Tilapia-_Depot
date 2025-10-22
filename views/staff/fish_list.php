<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../controllers/AuthController.php';
require_once __DIR__ . '/../../controllers/FishController.php';

$authController = new AuthController();
$authController->requireRole(['admin', 'staff']);

$fishController = new FishController();
$fishList = $fishController->getAllFish();

// Handle actions
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
</head>

<body class="bg-gray-100">
    <div class="flex">
        <!-- Sidebar -->
        <?php include 'staff_sidebar.php'; ?>

        <!-- Main Content -->
        <div class="flex-1 p-8">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">Fish Management</h1>
                    <p class="text-gray-600">Manage your fish products and inventory</p>
                </div>
                <a href="<?php echo BASE_URL; ?>/views/staff/add_fish.php"
                    class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                    <i class="fas fa-plus mr-2"></i>Add New Fish
                </a>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="flex items-center">
                        <div class="p-2 bg-blue-100 rounded-lg">
                            <i class="fas fa-fish text-blue-600"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-gray-600">Total Products</p>
                            <h3 class="text-lg font-bold"><?php echo count($fishList); ?></h3>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-4">
                    <div class="flex items-center">
                        <div class="p-2 bg-green-100 rounded-lg">
                            <i class="fas fa-check text-green-600"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-gray-600">Active Products</p>
                            <h3 class="text-lg font-bold">
                                <?php echo count(array_filter($fishList, fn($fish) => $fish['is_active'])); ?>
                            </h3>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-4">
                    <div class="flex items-center">
                        <div class="p-2 bg-red-100 rounded-lg">
                            <i class="fas fa-exclamation-triangle text-red-600"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-gray-600">Low Stock</p>
                            <h3 class="text-lg font-bold">
                                <?php echo count(array_filter($fishList, fn($fish) => $fish['stock_quantity'] <= 10)); ?>
                            </h3>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-4">
                    <div class="flex items-center">
                        <div class="p-2 bg-gray-100 rounded-lg">
                            <i class="fas fa-times text-gray-600"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-gray-600">Inactive</p>
                            <h3 class="text-lg font-bold">
                                <?php echo count(array_filter($fishList, fn($fish) => !$fish['is_active'])); ?>
                            </h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Fish List -->
            <div class="bg-white rounded-lg shadow">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-xl font-semibold text-gray-800">All Fish Products</h2>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php if (empty($fishList)): ?>
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                        No fish products found. <a href="<?php echo BASE_URL; ?>/views/staff/add_fish.php" class="text-blue-600 hover:text-blue-800">Add your first product</a>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($fishList as $fish): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10">
                                                    <img class="h-10 w-10 rounded-full object-cover"
                                                        src="<?php echo BASE_URL . '/uploads/' . ($fish['image_url'] ?: 'default_fish.png'); ?>"
                                                        alt="<?php echo htmlspecialchars($fish['name']); ?>">
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($fish['name']); ?></div>
                                                    <div class="text-sm text-gray-500"><?php echo htmlspecialchars($fish['weight_range']); ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">Ksh <?php echo number_format($fish['price'], 2); ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900 <?php echo $fish['stock_quantity'] <= 10 ? 'text-red-600 font-semibold' : ''; ?>">
                                                <?php echo $fish['stock_quantity']; ?>
                                            </div>
                                            <?php if ($fish['stock_quantity'] <= 10): ?>
                                                <div class="text-xs text-red-500">Low stock!</div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $fish['is_active'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                                <?php echo $fish['is_active'] ? 'Active' : 'Inactive'; ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <a href="<?php echo BASE_URL; ?>/views/staff/edit_fish.php?id=<?php echo $fish['id']; ?>"
                                                class="text-blue-600 hover:text-blue-900 mr-3">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <form method="POST" class="inline" onsubmit="return confirm('Are you sure?');">
                                                <input type="hidden" name="fish_id" value="<?php echo $fish['id']; ?>">
                                                <input type="hidden" name="action" value="toggle_status">
                                                <button type="submit" class="text-<?php echo $fish['is_active'] ? 'yellow' : 'green'; ?>-600 hover:text-<?php echo $fish['is_active'] ? 'yellow' : 'green'; ?>-900 mr-3">
                                                    <i class="fas fa-<?php echo $fish['is_active'] ? 'pause' : 'play'; ?>"></i>
                                                    <?php echo $fish['is_active'] ? 'Deactivate' : 'Activate'; ?>
                                                </button>
                                            </form>
                                            <form method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this product?');">
                                                <input type="hidden" name="fish_id" value="<?php echo $fish['id']; ?>">
                                                <input type="hidden" name="action" value="delete">
                                                <button type="submit" class="text-red-600 hover:text-red-900">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>

</html>