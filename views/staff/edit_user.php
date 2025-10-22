<?php
require_once '../../config/config.php';
require_once '../../controllers/AuthController.php';
require_once '../../controllers/UserController.php';

$authController = new AuthController();
$authController->requireRole(['admin']); // Only admin can access

$userController = new UserController();

// Get user ID from URL
$user_id = $_GET['id'] ?? null;

if (!$user_id) {
    $_SESSION['error'] = 'User ID not provided.';
    header('Location: users_list.php');
    exit();
}

// Get user details
$user = $userController->getUserById($user_id);

if (!$user) {
    $_SESSION['error'] = 'User not found.';
    header('Location: users_list.php');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $userController->updateUser($user_id, $_POST);

    if ($result['success']) {
        $_SESSION['success'] = $result['message'];
        header('Location: users_list.php');
        exit();
    } else {
        $error = $result['message'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User - <?php echo SITE_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100">
    <!-- Include Sidebar -->
    <?php include '../../views/includes/staff_sidebar.php'; ?>

    <!-- Main Content -->
    <div class="ml-64 p-8">
        <div class="max-w-3xl mx-auto">
            <!-- Header -->
            <div class="mb-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-800">Edit User</h1>
                        <p class="text-gray-600 mt-2">Update user information</p>
                    </div>
                    <a href="users_list.php" class="text-blue-600 hover:text-blue-800">
                        <i class="fas fa-arrow-left mr-2"></i>Back to User List
                    </a>
                </div>
            </div>

            <!-- Error Message -->
            <?php if (isset($error)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                    <i class="fas fa-exclamation-circle mr-2"></i><?php echo $error; ?>
                </div>
            <?php endif; ?>

            <!-- Edit User Form -->
            <div class="bg-white rounded-lg shadow p-6">
                <form method="POST" action="">
                    <!-- Full Name -->
                    <div class="mb-6">
                        <label for="full_name" class="block text-sm font-medium text-gray-700 mb-2">
                            Full Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="full_name" name="full_name" required
                            value="<?php echo htmlspecialchars($_POST['full_name'] ?? $user['full_name']); ?>"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <!-- Username (Read-only) -->
                    <div class="mb-6">
                        <label for="username" class="block text-sm font-medium text-gray-700 mb-2">
                            Username
                        </label>
                        <input type="text" id="username" name="username" readonly
                            value="<?php echo htmlspecialchars($user['username']); ?>"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100 cursor-not-allowed">
                        <p class="text-sm text-gray-500 mt-1">Username cannot be changed</p>
                    </div>

                    <!-- Email -->
                    <div class="mb-6">
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                            Email Address <span class="text-red-500">*</span>
                        </label>
                        <input type="email" id="email" name="email" required
                            value="<?php echo htmlspecialchars($_POST['email'] ?? $user['email']); ?>"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <!-- Phone -->
                    <div class="mb-6">
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                            Phone Number
                        </label>
                        <input type="tel" id="phone" name="phone"
                            value="<?php echo htmlspecialchars($_POST['phone'] ?? $user['phone'] ?? ''); ?>"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <!-- Address -->
                    <div class="mb-6">
                        <label for="address" class="block text-sm font-medium text-gray-700 mb-2">
                            Address
                        </label>
                        <textarea id="address" name="address" rows="3"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"><?php echo htmlspecialchars($_POST['address'] ?? $user['address'] ?? ''); ?></textarea>
                    </div>

                    <!-- Role -->
                    <div class="mb-6">
                        <label for="role" class="block text-sm font-medium text-gray-700 mb-2">
                            Role <span class="text-red-500">*</span>
                        </label>
                        <select id="role" name="role" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            <?php echo ($user['id'] == $_SESSION['user_id']) ? 'disabled' : ''; ?>>
                            <option value="staff" <?php echo ($user['role'] === 'staff') ? 'selected' : ''; ?>>Staff</option>
                            <option value="admin" <?php echo ($user['role'] === 'admin') ? 'selected' : ''; ?>>Admin</option>
                            <option value="customer" <?php echo ($user['role'] === 'customer') ? 'selected' : ''; ?>>Customer</option>
                        </select>
                        <?php if ($user['id'] == $_SESSION['user_id']): ?>
                            <p class="text-sm text-red-500 mt-1">You cannot change your own role</p>
                            <input type="hidden" name="role" value="<?php echo $user['role']; ?>">
                        <?php else: ?>
                            <p class="text-sm text-gray-500 mt-1">Select the user's role in the system</p>
                        <?php endif; ?>
                    </div>

                    <!-- User Info -->
                    <div class="bg-gray-50 p-4 rounded-lg mb-6">
                        <p class="text-sm text-gray-600">
                            <i class="fas fa-info-circle mr-2"></i>
                            <strong>Account Created:</strong> <?php echo date('F j, Y g:i A', strtotime($user['created_at'])); ?>
                        </p>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="flex justify-end space-x-4">
                        <a href="users_list.php"
                            class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition duration-200">
                            Cancel
                        </a>
                        <button type="submit"
                            class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition duration-200">
                            <i class="fas fa-save mr-2"></i>Update User
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>

</html>