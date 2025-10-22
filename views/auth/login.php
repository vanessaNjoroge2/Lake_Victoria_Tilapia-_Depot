<?php
require_once '../../config/config.php';
require_once '../../controllers/AuthController.php';

$authController = new AuthController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if ($authController->login($username, $password)) {
        if (in_array($_SESSION['role'], ['admin', 'staff'])) {
            header('Location: ../staff/dashboard.php');
        } else {
            header('Location: ../customer/browse_fish.php');
        }
        exit();
    } else {
        $error = "Invalid username or password!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo SITE_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>

<body class="bg-gradient-to-br from-blue-50 to-green-50 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full space-y-8 p-8">
        <!-- Header -->
        <div class="text-center">
            <div class="mx-auto h-20 w-20 bg-blue-600 rounded-full flex items-center justify-center mb-4">
                <i class="fas fa-fish text-white text-2xl"></i>
            </div>
            <h2 class="text-3xl font-bold text-gray-900">Welcome Back</h2>
            <p class="mt-2 text-gray-600">Sign in to your account</p>
        </div>

        <!-- Login Form -->
        <form class="mt-8 space-y-6 bg-white p-8 rounded-xl shadow-lg" method="POST">
            <?php if (isset($error)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <div>
                <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
                <input id="username" name="username" type="text" required
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                <input id="password" name="password" type="password" required
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
            </div>

            <div>
                <button type="submit"
                    class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-200">
                    <i class="fas fa-sign-in-alt mr-2"></i>
                    Sign In
                </button>
            </div>

            <div class="text-center">
                <p class="text-sm text-gray-600">
                    Don't have an account?
                    <a href="register.php" class="font-medium text-blue-600 hover:text-blue-500">
                        Sign up here
                    </a>
                </p>
            </div>

            <!-- Demo Accounts -->
            <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                <p class="text-sm font-semibold text-gray-700 mb-2">Demo Accounts:</p>
                <div class="text-xs text-gray-600 space-y-1">
                    <p><strong>Admin:</strong> admin / password</p>
                    <p><strong>Staff:</strong> staff1 / password</p>
                    <p><strong>Customer:</strong> customer1 / password</p>
                </div>
            </div>
        </form>
    </div>
</body>

</html>