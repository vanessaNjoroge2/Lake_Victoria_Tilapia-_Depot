<?php
require_once '../../config/config.php';
require_once '../../controllers/AuthController.php';
require_once '../../controllers/UserController.php';

$authController = new AuthController();
$authController->requireRole(['admin']); // Only admin can access

$userController = new UserController();

// Handle delete user
if (isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['user_id'])) {
    $result = $userController->deleteUser($_POST['user_id']);
    if ($result['success']) {
        $_SESSION['success'] = $result['message'];
    } else {
        $_SESSION['error'] = $result['message'];
    }
    header('Location: users_list.php');
    exit();
}

// Get all users or search results
$searchTerm = $_GET['search'] ?? '';
if (!empty($searchTerm)) {
    $users = $userController->searchUsers($searchTerm);
} else {
    $users = $userController->getAllUsers();
}

$userStats = $userController->getUserStatistics();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - <?php echo SITE_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100">
    <!-- Include Sidebar -->
    <?php include '../includes/staff_sidebar.php'; ?>

    <!-- Main Content -->
    <div class="ml-64 p-8">
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-800">User Management</h1>
            <p class="text-gray-600 mt-2">Manage all system users</p>
        </div>

        <!-- Success/Error Messages -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <i class="fas fa-check-circle mr-2"></i><?php echo $_SESSION['success'];
                                                        unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <i class="fas fa-exclamation-circle mr-2"></i><?php echo $_SESSION['error'];
                                                                unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Total Users</p>
                        <p class="text-3xl font-bold text-gray-800"><?php echo $userStats['total_users']; ?></p>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-full">
                        <i class="fas fa-users text-blue-600 text-2xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Customers</p>
                        <p class="text-3xl font-bold text-gray-800"><?php echo $userStats['total_customers']; ?></p>
                    </div>
                    <div class="bg-green-100 p-3 rounded-full">
                        <i class="fas fa-user text-green-600 text-2xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Staff</p>
                        <p class="text-3xl font-bold text-gray-800"><?php echo $userStats['total_staff']; ?></p>
                    </div>
                    <div class="bg-purple-100 p-3 rounded-full">
                        <i class="fas fa-user-tie text-purple-600 text-2xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Admins</p>
                        <p class="text-3xl font-bold text-gray-800"><?php echo $userStats['total_admins']; ?></p>
                    </div>
                    <div class="bg-red-100 p-3 rounded-full">
                        <i class="fas fa-user-shield text-red-600 text-2xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search and Add User -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                <form method="GET" action="" class="flex-1 w-full md:w-auto">
                    <div class="relative">
                        <input type="text" name="search" placeholder="Search users by name, email, or username..."
                            value="<?php echo htmlspecialchars($searchTerm); ?>"
                            class="w-full md:w-96 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <button type="submit" class="absolute right-2 top-2 text-gray-500 hover:text-blue-600">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>

                <a href="add_user.php" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition duration-200">
                    <i class="fas fa-user-plus mr-2"></i>Add New User
                </a>
            </div>
        </div>

        <!-- Users Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Joined</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($users)): ?>
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                    <i class="fas fa-users text-4xl mb-2"></i>
                                    <p>No users found</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($users as $user): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10 bg-blue-100 rounded-full flex items-center justify-center">
                                                <span class="text-blue-600 font-semibold">
                                                    <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
                                                </span>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">
                                                    <?php echo htmlspecialchars($user['full_name']); ?>
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    @<?php echo htmlspecialchars($user['username']); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900"><?php echo htmlspecialchars($user['email']); ?></div>
                                        <div class="text-sm text-gray-500"><?php echo htmlspecialchars($user['phone'] ?? 'N/A'); ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            <?php
                                            echo $user['role'] === 'admin' ? 'bg-red-100 text-red-800' : ($user['role'] === 'staff' ? 'bg-purple-100 text-purple-800' :
                                                'bg-green-100 text-green-800');
                                            ?>">
                                            <i class="fas fa-<?php
                                                                echo $user['role'] === 'admin' ? 'user-shield' : ($user['role'] === 'staff' ? 'user-tie' : 'user');
                                                                ?> mr-1"></i>
                                            <?php echo ucfirst($user['role']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo date('M j, Y', strtotime($user['created_at'])); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="edit_user.php?id=<?php echo $user['id']; ?>"
                                            class="text-blue-600 hover:text-blue-900 mr-3">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                            <form method="POST" action="" class="inline" onsubmit="return confirm('Are you sure you want to delete this user? This action cannot be undone.');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                <button type="submit" class="text-red-600 hover:text-red-900">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>

</html>