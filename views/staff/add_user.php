<?php
require_once '../../config/config.php';
require_once '../../controllers/AuthController.php';
require_once '../../controllers/UserController.php';

$authController = new AuthController();
$authController->requireRole(['admin']); // Only admin can access

$userController = new UserController();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $userController->addStaffUser($_POST);

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
    <title>Add New User - <?php echo SITE_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100">
    <!-- Include Sidebar -->
    <?php include '../includes/staff_sidebar.php'; ?>

    <!-- Main Content -->
    <div class="ml-64 p-8">
        <div class="max-w-3xl mx-auto">
            <!-- Header -->
            <div class="mb-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-800">Add New User</h1>
                        <p class="text-gray-600 mt-2">Create a new staff or admin account</p>
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

            <!-- Add User Form -->
            <div class="bg-white rounded-lg shadow p-6">
                <form method="POST" action="">
                    <!-- Full Name -->
                    <div class="mb-6">
                        <label for="full_name" class="block text-sm font-medium text-gray-700 mb-2">
                            Full Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="full_name" name="full_name" required
                            value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="John Doe">
                    </div>

                    <!-- Username -->
                    <div class="mb-6">
                        <label for="username" class="block text-sm font-medium text-gray-700 mb-2">
                            Username <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="username" name="username" required
                            value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="johndoe">
                        <p class="text-sm text-gray-500 mt-1">Unique username for login</p>
                    </div>

                    <!-- Email -->
                    <div class="mb-6">
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                            Email Address <span class="text-red-500">*</span>
                        </label>
                        <input type="email" id="email" name="email" required
                            value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="john@example.com">
                    </div>

                    <!-- Phone -->
                    <div class="mb-6">
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                            Phone Number
                        </label>
                        <input type="tel" id="phone" name="phone"
                            value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="+254712345678">
                    </div>

                    <!-- Address -->
                    <div class="mb-6">
                        <label for="address" class="block text-sm font-medium text-gray-700 mb-2">
                            Address
                        </label>
                        <textarea id="address" name="address" rows="3"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Enter address"><?php echo htmlspecialchars($_POST['address'] ?? ''); ?></textarea>
                    </div>

                    <!-- Role -->
                    <div class="mb-6">
                        <label for="role" class="block text-sm font-medium text-gray-700 mb-2">
                            Role <span class="text-red-500">*</span>
                        </label>
                        <select id="role" name="role" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="staff" <?php echo (isset($_POST['role']) && $_POST['role'] === 'staff') ? 'selected' : ''; ?>>Staff</option>
                            <option value="admin" <?php echo (isset($_POST['role']) && $_POST['role'] === 'admin') ? 'selected' : ''; ?>>Admin</option>
                            <option value="customer" <?php echo (isset($_POST['role']) && $_POST['role'] === 'customer') ? 'selected' : ''; ?>>Customer</option>
                        </select>
                        <p class="text-sm text-gray-500 mt-1">Select the user's role in the system</p>
                    </div>

                    <!-- Password -->
                    <div class="mb-6">
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                            Password <span class="text-red-500">*</span>
                        </label>
                        <input type="password" id="password" name="password" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Enter password" minlength="6">
                        <p class="text-sm text-gray-500 mt-1">Password must be at least 6 characters long</p>
                    </div>

                    <!-- Confirm Password -->
                    <div class="mb-6">
                        <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-2">
                            Confirm Password <span class="text-red-500">*</span>
                        </label>
                        <input type="password" id="confirm_password" name="confirm_password" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Confirm password" minlength="6">
                    </div>

                    <!-- Submit Buttons -->
                    <div class="flex justify-end space-x-4">
                        <a href="users_list.php"
                            class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition duration-200">
                            Cancel
                        </a>
                        <button type="submit"
                            class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition duration-200">
                            <i class="fas fa-user-plus mr-2"></i>Add User
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Password Match Validation Script -->
    <script>
        document.querySelector('form').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;

            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match!');
                return false;
            }
        });
    </script>
</body>

</html>