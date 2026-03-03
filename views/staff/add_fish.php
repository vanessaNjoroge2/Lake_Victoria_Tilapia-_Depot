<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../controllers/AuthController.php';
require_once __DIR__ . '/../../controllers/FishController.php';

$authController = new AuthController();
$authController->requireRole(['admin', 'staff']);

$fishController = new FishController();
$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $stock_quantity = intval($_POST['stock_quantity']);
    $weight_range = trim($_POST['weight_range']);
    $category = trim($_POST['category']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    // Handle image upload
    $image_url = 'default_fish.png';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../../uploads/';
        $fileName = uniqid() . '_' . basename($_FILES['image']['name']);
        $uploadFile = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadFile)) {
            $image_url = $fileName;
        }
    }

    if (empty($name) || empty($price) || empty($stock_quantity)) {
        $error = "Please fill in all required fields.";
    } else {
        $fishData = [
            'name' => $name,
            'description' => $description,
            'price' => $price,
            'image_url' => $image_url,
            'category' => $category,
            'stock_quantity' => $stock_quantity,
            'weight_range' => $weight_range,
            'is_active' => $is_active
        ];

        if ($fishController->createFish($fishData)) {
            $success = "Fish product added successfully!";
            // Clear form
            $_POST = [];
        } else {
            $error = "Failed to add fish product. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Fish - <?php echo SITE_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100">
    <div class="flex">
        <!-- Sidebar -->
        <?php include '../includes/staff_sidebar.php'; ?>

        <!-- Main Content -->
        <div class="flex-1 ml-64 p-8">
            <div class="max-w-4xl mx-auto">
                <div class="mb-6">
                    <a href="<?php echo BASE_URL; ?>/views/staff/fish_list.php"
                        class="text-blue-600 hover:text-blue-800 mb-4 inline-block">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Fish List
                    </a>
                    <h1 class="text-3xl font-bold text-gray-800">Add New Fish Product</h1>
                    <p class="text-gray-600">Add a new fish product to your inventory</p>
                </div>

                <?php if ($error): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                        <?php echo htmlspecialchars($success); ?>
                    </div>
                <?php endif; ?>

                <div class="bg-white rounded-lg shadow p-6">
                    <form method="POST" action="" enctype="multipart/form-data">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Product Name -->
                            <div class="md:col-span-2">
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                                    Product Name *
                                </label>
                                <input type="text" id="name" name="name"
                                    value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    required>
                            </div>

                            <!-- Description -->
                            <div class="md:col-span-2">
                                <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                                    Description
                                </label>
                                <textarea id="description" name="description" rows="3"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                            </div>

                            <!-- Price -->
                            <div>
                                <label for="price" class="block text-sm font-medium text-gray-700 mb-1">
                                    Price (Ksh) *
                                </label>
                                <input type="number" id="price" name="price" step="0.01" min="0"
                                    value="<?php echo htmlspecialchars($_POST['price'] ?? ''); ?>"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    required>
                            </div>

                            <!-- Stock Quantity -->
                            <div>
                                <label for="stock_quantity" class="block text-sm font-medium text-gray-700 mb-1">
                                    Stock Quantity *
                                </label>
                                <input type="number" id="stock_quantity" name="stock_quantity" min="0"
                                    value="<?php echo htmlspecialchars($_POST['stock_quantity'] ?? '0'); ?>"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    required>
                            </div>

                            <!-- Weight Range -->
                            <div>
                                <label for="weight_range" class="block text-sm font-medium text-gray-700 mb-1">
                                    Weight Range *
                                </label>
                                <input type="text" id="weight_range" name="weight_range"
                                    value="<?php echo htmlspecialchars($_POST['weight_range'] ?? '200-300g'); ?>"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    required placeholder="e.g., 200-300g">
                            </div>

                            <!-- Category -->
                            <div>
                                <label for="category" class="block text-sm font-medium text-gray-700 mb-1">
                                    Category
                                </label>
                                <select id="category" name="category"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="Tilapia" <?php echo ($_POST['category'] ?? '') === 'Tilapia' ? 'selected' : ''; ?>>Tilapia</option>
                                    <option value="Nile Perch" <?php echo ($_POST['category'] ?? '') === 'Nile Perch' ? 'selected' : ''; ?>>Nile Perch</option>
                                    <option value="Catfish" <?php echo ($_POST['category'] ?? '') === 'Catfish' ? 'selected' : ''; ?>>Catfish</option>
                                    <option value="Others" <?php echo ($_POST['category'] ?? '') === 'Others' ? 'selected' : ''; ?>>Others</option>
                                </select>
                            </div>

                            <!-- Image Upload -->
                            <div class="md:col-span-2">
                                <label for="image" class="block text-sm font-medium text-gray-700 mb-1">
                                    Product Image
                                </label>
                                <input type="file" id="image" name="image"
                                    accept="image/*"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <p class="text-xs text-gray-500 mt-1">Recommended size: 500x500px. Max size: 2MB</p>
                            </div>

                            <!-- Active Status -->
                            <div class="md:col-span-2">
                                <label class="flex items-center">
                                    <input type="checkbox" name="is_active" value="1"
                                        <?php echo isset($_POST['is_active']) ? 'checked' : 'checked'; ?>
                                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    <span class="ml-2 text-sm text-gray-700">Active (visible to customers)</span>
                                </label>
                            </div>
                        </div>

                        <div class="mt-8 flex gap-4">
                            <button type="submit"
                                class="bg-blue-600 text-white px-6 py-3 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <i class="fas fa-plus mr-2"></i>Add Fish Product
                            </button>
                            <a href="<?php echo BASE_URL; ?>/views/staff/fish_list.php"
                                class="bg-gray-300 text-gray-700 px-6 py-3 rounded-md hover:bg-gray-400">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>

</html>