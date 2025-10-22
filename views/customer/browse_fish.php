<?php
require_once '../../config/config.php';
require_once '../../controllers/AuthController.php';
require_once '../../controllers/FishController.php';

$authController = new AuthController();
$authController->requireAuth();

$fishController = new FishController();
$fish = $fishController->getActiveFish();

// Handle search
if (isset($_GET['search'])) {
    $fish = $fishController->searchFish($_GET['search']);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Fish - <?php echo SITE_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>

<body class="bg-gray-50">
    <?php include '../includes/navbar.php'; ?>

    <div class="container mx-auto px-4 py-8">
        <!-- Search and Filter Section -->
        <div class="mb-8 bg-white rounded-lg shadow p-6">
            <form method="GET" class="flex flex-col md:flex-row gap-4">
                <div class="flex-1">
                    <input type="text" name="search" placeholder="Search fish by name, description, or category..."
                        value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition duration-200">
                    <i class="fas fa-search mr-2"></i>Search
                </button>
                <?php if (isset($_GET['search'])): ?>
                    <a href="browse_fish.php" class="bg-gray-500 text-white px-6 py-2 rounded-lg hover:bg-gray-600 transition duration-200">
                        Clear
                    </a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Fish Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <?php foreach ($fish as $item): ?>
                <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition duration-300">
                    <div class="relative">
                        <img src="<?php echo BASE_URL . '/uploads/' . ($item['image_url'] ?: 'default_fish.png'); ?>"
                            alt="<?php echo htmlspecialchars($item['name']); ?>"
                            class="w-full h-48 object-cover">
                        <div class="absolute top-2 right-2 bg-blue-600 text-white px-2 py-1 rounded text-sm">
                            Ksh <?php echo number_format($item['price'], 2); ?>
                        </div>
                    </div>

                    <div class="p-4">
                        <h3 class="text-lg font-semibold text-gray-800 mb-2"><?php echo htmlspecialchars($item['name']); ?></h3>
                        <p class="text-gray-600 text-sm mb-3"><?php echo htmlspecialchars($item['description']); ?></p>

                        <div class="flex justify-between items-center text-sm text-gray-500 mb-4">
                            <span><i class="fas fa-weight mr-1"></i><?php echo htmlspecialchars($item['weight_range']); ?></span>
                            <span class="<?php echo $item['stock_quantity'] > 0 ? 'text-green-600' : 'text-red-600'; ?>">
                                <i class="fas fa-box mr-1"></i><?php echo $item['stock_quantity'] > 0 ? 'In Stock' : 'Out of Stock'; ?>
                            </span>
                        </div>

                        <form action="../../handlers/cart_handler.php" method="POST" class="flex items-center gap-2">
                            <input type="hidden" name="fish_id" value="<?php echo $item['id']; ?>">
                            <input type="hidden" name="action" value="add">

                            <div class="flex-1">
                                <input type="number" name="quantity" value="1" min="1" max="<?php echo $item['stock_quantity']; ?>"
                                    class="w-full px-3 py-1 border border-gray-300 rounded text-center"
                                    <?php echo $item['stock_quantity'] == 0 ? 'disabled' : ''; ?>>
                            </div>

                            <button type="submit"
                                class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 transition duration-200 flex items-center gap-2"
                                <?php echo $item['stock_quantity'] == 0 ? 'disabled' : ''; ?>>
                                <i class="fas fa-cart-plus"></i>
                                <span class="hidden sm:inline">Add to Cart</span>
                            </button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if (empty($fish)): ?>
            <div class="text-center py-12">
                <i class="fas fa-fish text-gray-400 text-6xl mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-600 mb-2">No fish found</h3>
                <p class="text-gray-500">Try adjusting your search terms or browse all products.</p>
            </div>
        <?php endif; ?>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>

</html>