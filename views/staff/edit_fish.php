<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../controllers/AuthController.php';
require_once __DIR__ . '/../../controllers/FishController.php';
require_once __DIR__ . '/../../includes/csrf.php';

$authController = new AuthController();
$authController->requireRole(['admin', 'staff']);

$fishController = new FishController();
$error = '';
$success = '';

// Get fish ID from URL
$fish_id = $_GET['id'] ?? null;
if (!$fish_id) {
    header('Location: fish_list.php');
    exit();
}

// Get current fish data
$fish = $fishController->getFishById($fish_id);
if (!$fish) {
    header('Location: fish_list.php');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF verification
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($token)) {
        $error = "CSRF verification failed. Please try again.";
    } else {
        $name = trim($_POST['name'] ?? '');
        $size = trim($_POST['size'] ?? 'Size 1');
        $type = trim($_POST['type'] ?? 'raw');
        $cost_price = floatval($_POST['cost_price'] ?? 0.0);
        $retail_price = floatval($_POST['retail_price'] ?? 0.0);
        $wholesale_price = floatval($_POST['wholesale_price'] ?? 0.0);
        $description = trim($_POST['description'] ?? '');
        $stock_qty = intval($_POST['stock_qty'] ?? 0);
        $low_stock_threshold = intval($_POST['low_stock_threshold'] ?? 10);
        $unit = trim($_POST['unit'] ?? 'piece');
        $weight_range = trim($_POST['weight_range'] ?? '200-300g');
        $category = trim($_POST['category'] ?? 'Tilapia');
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        // Handle image upload
        $image_url = $fish['image_url'];
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../uploads/';
            $fileName = uniqid() . '_' . basename($_FILES['image']['name']);
            $uploadFile = $uploadDir . $fileName;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadFile)) {
                $image_url = $fileName;
            }
        }

        if (empty($name)) {
            $error = "Please fill in the product name.";
        } else {
            $fishData = [
                'name' => $name,
                'size' => $size,
                'type' => $type,
                'cost_price' => $cost_price,
                'retail_price' => $retail_price,
                'wholesale_price' => $wholesale_price,
                'price' => $retail_price, // legacy compatibility
                'description' => $description,
                'image_url' => $image_url,
                'category' => $category,
                'stock_qty' => $stock_qty,
                'stock_quantity' => $stock_qty, // legacy compatibility
                'low_stock_threshold' => $low_stock_threshold,
                'unit' => $unit,
                'weight_range' => $weight_range,
                'is_active' => $is_active
            ];

            if ($fishController->updateFish($fish_id, $fishData)) {
                $success = "Fish product updated successfully!";
                // Refresh fish data
                $fish = $fishController->getFishById($fish_id);
            } else {
                $error = "Failed to update fish product. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Fish Product - <?php echo SITE_NAME; ?></title>
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
        <div class="max-w-3xl mx-auto">
            
            <!-- Breadcrumbs / Back -->
            <div class="mb-6">
                <a href="<?php echo BASE_URL; ?>/views/staff/fish_list.php"
                    class="text-blue-600 hover:text-blue-800 font-bold mb-4 inline-block transition duration-150">
                    <i class="fas fa-arrow-left mr-2"></i> Back to Fish List
                </a>
                <h1 class="text-3xl font-bold text-gray-800">Edit Fish Product</h1>
                <p class="text-gray-600 font-medium">Update the size class, pricing, inventory thresholds, or status of this catalog entry</p>
            </div>

            <!-- Alerts -->
            <?php if ($error): ?>
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-6 font-semibold text-xs flex items-center">
                    <i class="fas fa-exclamation-circle mr-2 text-red-500 text-base"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl mb-6 font-semibold text-xs flex items-center">
                    <i class="fas fa-check-circle mr-2 text-green-500 text-base"></i> <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <!-- Form Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8">
                <form method="POST" action="" enctype="multipart/form-data" class="space-y-6 text-xs">
                    <?php echo csrf_field(); ?>
                    
                    <!-- Basic Information Section -->
                    <div>
                        <h3 class="text-slate-800 font-bold uppercase tracking-wider text-xs border-b border-slate-100 pb-2 mb-4"><i class="fas fa-circle-info mr-1 text-blue-500"></i> Basic Information</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Product Name -->
                            <div class="md:col-span-2">
                                <label for="name" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">
                                    Product Name *
                                </label>
                                <input type="text" id="name" name="name"
                                    value="<?php echo htmlspecialchars($fish['name']); ?>"
                                    placeholder="e.g. Fresh Lake Victoria Tilapia"
                                    class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 font-medium"
                                    required>
                            </div>

                            <!-- Category -->
                            <div>
                                <label for="category" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">
                                    Category
                                </label>
                                <select id="category" name="category"
                                    class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="Tilapia" <?php echo $fish['category'] === 'Tilapia' ? 'selected' : ''; ?>>Tilapia</option>
                                    <option value="Nile Perch" <?php echo $fish['category'] === 'Nile Perch' ? 'selected' : ''; ?>>Nile Perch</option>
                                    <option value="Catfish" <?php echo $fish['category'] === 'Catfish' ? 'selected' : ''; ?>>Catfish</option>
                                    <option value="Others" <?php echo $fish['category'] === 'Others' ? 'selected' : ''; ?>>Others</option>
                                </select>
                            </div>

                            <!-- Classification Type -->
                            <div>
                                <label for="type" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">
                                    Classification / State *
                                </label>
                                <select id="type" name="type"
                                    class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 font-bold">
                                    <option value="raw" <?php echo $fish['type'] === 'raw' ? 'selected' : ''; ?>>Raw (Chilled/Fresh)</option>
                                    <option value="fried" <?php echo $fish['type'] === 'fried' ? 'selected' : ''; ?>>Fried (Ready to Eat)</option>
                                </select>
                            </div>

                            <!-- Size Class -->
                            <div>
                                <label for="size" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">
                                    Fish Size Class *
                                </label>
                                <select id="size" name="size"
                                    class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 font-bold">
                                    <option value="Size 1" <?php echo $fish['size'] === 'Size 1' ? 'selected' : ''; ?>>Size 1 (Smallest)</option>
                                    <option value="Size 2" <?php echo $fish['size'] === 'Size 2' ? 'selected' : ''; ?>>Size 2</option>
                                    <option value="Size 3" <?php echo $fish['size'] === 'Size 3' ? 'selected' : ''; ?>>Size 3</option>
                                    <option value="Size 4" <?php echo $fish['size'] === 'Size 4' ? 'selected' : ''; ?>>Size 4</option>
                                    <option value="Size 5" <?php echo $fish['size'] === 'Size 5' ? 'selected' : ''; ?>>Size 5 (Largest)</option>
                                </select>
                            </div>

                            <!-- Weight range representation -->
                            <div>
                                <label for="weight_range" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">
                                    Weight range description
                                </label>
                                <input type="text" id="weight_range" name="weight_range"
                                    value="<?php echo htmlspecialchars($fish['weight_range'] ?? '200-300g'); ?>"
                                    placeholder="e.g. 200-300g"
                                    class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>
                    </div>

                    <!-- Pricing & Inventory Settings -->
                    <div>
                        <h3 class="text-slate-800 font-bold uppercase tracking-wider text-xs border-b border-slate-100 pb-2 mb-4"><i class="fas fa-coins mr-1 text-amber-500"></i> Pricing & Inventory Control</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <!-- Acquisition Cost Price -->
                            <div>
                                <label for="cost_price" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">
                                    Acquisition Cost (Ksh) *
                                </label>
                                <input type="number" id="cost_price" name="cost_price" step="0.01" min="0"
                                    value="<?php echo htmlspecialchars($fish['cost_price'] ?? '0.00'); ?>"
                                    class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 font-semibold"
                                    required>
                            </div>

                            <!-- Retail Selling Price -->
                            <div>
                                <label for="retail_price" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">
                                    Retail Selling Price (Ksh) *
                                </label>
                                <input type="number" id="retail_price" name="retail_price" step="0.01" min="0"
                                    value="<?php echo htmlspecialchars($fish['retail_price'] ?? $fish['price'] ?? '0.00'); ?>"
                                    class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 font-bold"
                                    required>
                            </div>

                            <!-- Wholesale Credit Price -->
                            <div>
                                <label for="wholesale_price" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">
                                    Wholesale Price (Ksh) *
                                </label>
                                <input type="number" id="wholesale_price" name="wholesale_price" step="0.01" min="0"
                                    value="<?php echo htmlspecialchars($fish['wholesale_price'] ?? '0.00'); ?>"
                                    class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 font-bold text-blue-600"
                                    required>
                            </div>

                            <!-- Current Stock Quantity -->
                            <div>
                                <label for="stock_qty" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">
                                    Stock Qty *
                                </label>
                                <input type="number" id="stock_qty" name="stock_qty" min="0"
                                    value="<?php echo htmlspecialchars($fish['stock_qty'] ?? $fish['stock_quantity'] ?? '0'); ?>"
                                    class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 font-bold text-slate-700"
                                    required>
                            </div>

                            <!-- Stock Measurement Unit -->
                            <div>
                                <label for="unit" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">
                                    Measurement Unit *
                                </label>
                                <select id="unit" name="unit"
                                    class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="piece" <?php echo ($fish['unit'] ?? '') === 'piece' ? 'selected' : ''; ?>>Piece (Single fish)</option>
                                    <option value="kg" <?php echo ($fish['unit'] ?? '') === 'kg' ? 'selected' : ''; ?>>Kilogram (Weight)</option>
                                    <option value="crate" <?php echo ($fish['unit'] ?? '') === 'crate' ? 'selected' : ''; ?>>Crate (Bulk cargo)</option>
                                </select>
                            </div>

                            <!-- Low Stock Warning Threshold -->
                            <div>
                                <label for="low_stock_threshold" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">
                                    Low Stock Warning Limit
                                </label>
                                <input type="number" id="low_stock_threshold" name="low_stock_threshold" min="1"
                                    value="<?php echo htmlspecialchars($fish['low_stock_threshold'] ?? '10'); ?>"
                                    class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 font-semibold">
                            </div>
                        </div>
                    </div>

                    <!-- Description and Presentation -->
                    <div>
                        <h3 class="text-slate-800 font-bold uppercase tracking-wider text-xs border-b border-slate-100 pb-2 mb-4"><i class="fas fa-image mr-1 text-purple-500"></i> Catalog Details & Presentation</h3>
                        
                        <div class="space-y-4">
                            <!-- Current Image Display -->
                            <div class="flex items-center gap-4 bg-slate-50 p-4 rounded-xl border border-slate-100">
                                <img src="<?php echo BASE_URL; ?>/uploads/<?php echo htmlspecialchars($fish['image_url'] ?? 'default_fish.png'); ?>"
                                    alt="<?php echo htmlspecialchars($fish['name']); ?>"
                                    class="w-20 h-20 object-cover rounded-lg border border-slate-200 shadow-sm"
                                    onerror="this.src='<?php echo BASE_URL; ?>/public/images/default_fish.png'">
                                <div>
                                    <span class="block text-[10px] text-slate-400 font-bold uppercase">Current Image URL</span>
                                    <span class="font-medium text-slate-600 break-all"><?php echo htmlspecialchars($fish['image_url'] ?? 'default_fish.png'); ?></span>
                                </div>
                            </div>

                            <!-- Description -->
                            <div>
                                <label for="description" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">
                                    Product Description
                                </label>
                                <textarea id="description" name="description" rows="2" placeholder="Describe quality markers, origin, or preparation details..."
                                    class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500"><?php echo htmlspecialchars($fish['description']); ?></textarea>
                            </div>

                            <!-- Image Upload -->
                            <div>
                                <label for="image" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">
                                    Update Product Image Card
                                </label>
                                <input type="file" id="image" name="image" accept="image/*"
                                    class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 file:mr-4 file:py-1 file:px-3 file:rounded-lg file:border-0 file:bg-blue-50 file:text-blue-700 file:font-bold hover:file:bg-blue-100">
                                <p class="text-[10px] text-slate-400 mt-1">Leave empty to keep the current image. Accepts PNG, JPG, JPEG. Max 2MB</p>
                            </div>

                            <!-- Active Status Toggle -->
                            <div class="pt-2">
                                <label class="flex items-center cursor-pointer">
                                    <input type="checkbox" name="is_active" value="1"
                                        <?php echo $fish['is_active'] ? 'checked' : ''; ?>
                                        class="rounded-lg border-slate-200 text-blue-600 focus:ring-blue-500 h-4 w-4">
                                    <span class="ml-2 text-slate-700 font-semibold">Active Catalog Visibility (Allows cashiers to select item)</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="pt-6 border-t border-slate-100 flex gap-4">
                        <button type="submit"
                            class="bg-blue-600 text-white px-6 py-3.5 rounded-xl hover:bg-blue-700 font-bold shadow-md shadow-blue-500/10 flex items-center">
                            <i class="fas fa-save mr-2"></i> Update Product
                        </button>
                        <a href="<?php echo BASE_URL; ?>/views/staff/fish_list.php"
                            class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-6 py-3.5 rounded-xl font-bold">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>