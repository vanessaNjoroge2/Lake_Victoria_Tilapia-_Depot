<?php
require_once '../../config/config.php';
require_once '../../controllers/AuthController.php';
require_once '../../controllers/UserController.php';

$authController = new AuthController();
$authController->requireAuth(); // Any authenticated user can access their profile

$userController = new UserController();

// Get user data
$userData = $userController->getUserById($_SESSION['user_id']);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_profile') {
        $result = $userController->updateUser($_SESSION['user_id'], $_POST);
        if ($result['success']) {
            $success = $result['message'];
            // Refresh user data
            $userData = $userController->getUserById($_SESSION['user_id']);
            // Update session
            $_SESSION['full_name'] = $userData['full_name'];
        } else {
            $error = $result['message'];
        }
    } elseif ($action === 'change_password') {
        $result = $userController->changePassword(
            $_SESSION['user_id'],
            $_POST['current_password'],
            $_POST['new_password'],
            $_POST['confirm_password']
        );
        if ($result['success']) {
            $success = $result['message'];
        } else {
            $error = $result['message'];
        }
    }
}

// Determine user role-specific navigation
$isStaff = ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'staff');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - <?php echo SITE_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="bg-gray-100">
    <?php if ($isStaff): ?>
        <!-- Staff Sidebar -->
        <?php include '../../views/includes/staff_sidebar.php'; ?>
        <div class="ml-64 p-8">
        <?php else: ?>
            <!-- Customer Header and Navbar -->
            <?php include '../../views/includes/header.php'; ?>
            <?php include '../../views/includes/navbar.php'; ?>
            <div class="container mx-auto px-4 py-8">
            <?php endif; ?>

            <div class="max-w-4xl <?php echo !$isStaff ? 'mx-auto' : ''; ?>">
                <!-- Page Header -->
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-800">My Profile</h1>
                    <p class="text-gray-600 mt-2">Manage your account information and preferences</p>
                </div>

                <!-- Success/Error Messages -->
                <?php if (isset($success)): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                        <i class="fas fa-check-circle mr-2"></i><?php echo htmlspecialchars($success); ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($error)): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                        <i class="fas fa-exclamation-circle mr-2"></i><?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Left Column - Profile Overview -->
                    <div class="lg:col-span-1">
                        <div class="bg-white rounded-lg shadow p-6">
                            <div class="text-center">
                                <div class="w-24 h-24 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <i class="fas fa-user text-blue-600 text-3xl"></i>
                                </div>
                                <h2 class="text-xl font-semibold text-gray-800"><?php echo htmlspecialchars($userData['full_name'] ?? 'User'); ?></h2>
                                <p class="text-gray-600 mt-1"><?php echo htmlspecialchars($userData['email'] ?? ''); ?></p>
                                <p class="text-sm text-gray-500 mt-2">Member since <?php echo date('M Y', strtotime($userData['created_at'] ?? 'now')); ?></p>
                            </div>

                            <div class="mt-6 space-y-3">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Role:</span>
                                    <span class="font-medium capitalize
                                    <?php echo $userData['role'] === 'admin' ? 'text-red-600' : ($userData['role'] === 'staff' ? 'text-purple-600' : 'text-green-600'); ?>">
                                        <?php echo htmlspecialchars($userData['role'] ?? 'customer'); ?>
                                    </span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Status:</span>
                                    <span class="font-medium text-green-600">Active</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column - Profile Forms -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Profile Information Form -->
                        <div class="bg-white rounded-lg shadow p-6">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4">Profile Information</h3>
                            <form method="POST" action="">
                                <input type="hidden" name="action" value="update_profile">

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label for="full_name" class="block text-sm font-medium text-gray-700 mb-1">Full Name *</label>
                                        <input type="text" id="full_name" name="full_name"
                                            value="<?php echo htmlspecialchars($userData['full_name'] ?? ''); ?>"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                            required>
                                    </div>

                                    <div>
                                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address *</label>
                                        <input type="email" id="email" name="email"
                                            value="<?php echo htmlspecialchars($userData['email'] ?? ''); ?>"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                            required>
                                    </div>

                                    <div>
                                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                                        <input type="tel" id="phone" name="phone"
                                            value="<?php echo htmlspecialchars($userData['phone'] ?? ''); ?>"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                            placeholder="+254 XXX XXX XXX">
                                    </div>

                                    <div class="md:col-span-2">
                                        <label for="address" class="block text-sm font-medium text-gray-700 mb-1">Shipping Address</label>
                                        <textarea id="address" name="address" rows="3"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                            placeholder="Enter your delivery address"><?php echo htmlspecialchars($userData['address'] ?? ''); ?></textarea>
                                    </div>
                                </div>

                                <div class="mt-6">
                                    <button type="submit"
                                        class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        Update Profile
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Change Password Form -->
                        <div class="bg-white rounded-lg shadow p-6">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4">Change Password</h3>
                            <form method="POST" action="">
                                <input type="hidden" name="action" value="change_password">

                                <div class="space-y-4">
                                    <div>
                                        <label for="current_password" class="block text-sm font-medium text-gray-700 mb-1">Current Password *</label>
                                        <input type="password" id="current_password" name="current_password"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                            required>
                                    </div>

                                    <div>
                                        <label for="new_password" class="block text-sm font-medium text-gray-700 mb-1">New Password *</label>
                                        <input type="password" id="new_password" name="new_password"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                            required minlength="6">
                                        <p class="text-xs text-gray-500 mt-1">Password must be at least 6 characters long</p>
                                    </div>

                                    <div>
                                        <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password *</label>
                                        <input type="password" id="confirm_password" name="confirm_password"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                            required minlength="6">
                                    </div>
                                </div>

                                <div class="mt-6">
                                    <button type="submit"
                                        class="bg-green-600 text-white px-6 py-2 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500">
                                        Change Password
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Account Preferences -->
                        <div class="bg-white rounded-lg shadow p-6">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4">Account Preferences</h3>
                            <div class="space-y-4">
                                <div class="flex justify-between items-center">
                                    <div>
                                        <h4 class="font-medium text-gray-800">Email Notifications</h4>
                                        <p class="text-sm text-gray-600">Receive order updates and promotions</p>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" class="sr-only peer" checked>
                                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                    </label>
                                </div>

                                <div class="flex justify-between items-center">
                                    <div>
                                        <h4 class="font-medium text-gray-800">SMS Notifications</h4>
                                        <p class="text-sm text-gray-600">Receive order updates via SMS</p>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" class="sr-only peer">
                                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            </div>

            <!-- Include Footer -->
            <?php include '../../views/includes/footer.php'; ?>

            <script>
                // Simple password confirmation validation
                document.addEventListener('DOMContentLoaded', function() {
                    const newPassword = document.getElementById('new_password');
                    const confirmPassword = document.getElementById('confirm_password');

                    function validatePassword() {
                        if (newPassword.value !== confirmPassword.value) {
                            confirmPassword.setCustomValidity("Passwords don't match");
                        } else {
                            confirmPassword.setCustomValidity('');
                        }
                    }

                    newPassword.addEventListener('change', validatePassword);
                    confirmPassword.addEventListener('keyup', validatePassword);
                });
            </script>
</body>

</html>