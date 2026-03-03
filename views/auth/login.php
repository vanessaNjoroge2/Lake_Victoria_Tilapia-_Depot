<?php

/**
 * Login Page
 * Handles user authentication for all roles (Customer, Staff, Admin)
 */

require_once '../../config/config.php';
require_once '../../controllers/AuthController.php';

$authController = new AuthController();

// Redirect already logged-in users to their appropriate dashboard
$authController->redirectIfLoggedIn();

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Initialize variables for messages
$error   = null;
$success = null;

// Flash messages
if (isset($_SESSION['logout_success'])) {
    $success = $_SESSION['logout_success'];
    unset($_SESSION['logout_success']);
}
if (isset($_SESSION['registration_success'])) {
    $success = $_SESSION['registration_success'];
    unset($_SESSION['registration_success']);
}

// Auth-required redirect message
if (isset($_GET['error']) && $_GET['error'] === 'auth_required') {
    $error = "Please log in to access that page.";
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF check
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        $error = "Security token mismatch. Please refresh and try again.";
    } else {
        $username = trim(strip_tags($_POST['username'] ?? ''));
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            $error = "Username and password are required.";
        } elseif ($authController->login($username, $password)) {
            // Regenerate session ID after successful login (session fixation prevention)
            session_regenerate_id(true);
            $authController->postLoginRedirect();
        } else {
            $error = "Invalid username or password.";
        }
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
    <style>
        .fish-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            position: relative;
            overflow: hidden;
        }

        .fish-bg::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: url('<?php echo BASE_URL; ?>/uploads/tilapia_in_water.jpg');
            background-size: 100px;
            opacity: 0.05;
        }

        .login-card {
            animation: slideUp 0.5s ease-out;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to   { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>

<body class="fish-bg min-h-screen flex items-center justify-center p-4">
    <div class="container max-w-6xl mx-auto">
        <div class="grid md:grid-cols-2 gap-8 items-center">
            <!-- Left Side - Fish Image/Branding -->
            <div class="hidden md:block text-center">
                <div class="bg-white/10 backdrop-blur-sm rounded-3xl p-8">
                    <img src="<?php echo BASE_URL; ?>/uploads/fresh_tilapia.jpg"
                        alt="Fresh Tilapia"
                        class="w-64 h-64 object-cover rounded-full mx-auto mb-6 border-4 border-white/30 shadow-2xl animate-pulse">
                    <h1 class="text-4xl font-bold text-white mb-4">Lake Victoria<br />Tilapia Depot</h1>
                    <p class="text-white/90 text-lg">Fresh from the lake to your plate</p>
                    <div class="mt-8 flex justify-center space-x-4">
                        <div class="bg-white/20 backdrop-blur-sm rounded-xl p-4 text-center">
                            <i class="fas fa-fish text-white text-3xl mb-2"></i>
                            <p class="text-white text-sm">Fresh Daily</p>
                        </div>
                        <div class="bg-white/20 backdrop-blur-sm rounded-xl p-4 text-center">
                            <i class="fas fa-truck text-white text-3xl mb-2"></i>
                            <p class="text-white text-sm">Fast Delivery</p>
                        </div>
                        <div class="bg-white/20 backdrop-blur-sm rounded-xl p-4 text-center">
                            <i class="fas fa-star text-white text-3xl mb-2"></i>
                            <p class="text-white text-sm">Premium Quality</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side - Login Form -->
            <div class="login-card">
                <form method="POST" autocomplete="on" class="bg-white rounded-3xl shadow-2xl p-8 md:p-10">

                    <!-- CSRF token -->
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>" />

                    <div class="text-center mb-8">
                        <div class="inline-block bg-blue-100 p-4 rounded-full mb-4">
                            <i class="fas fa-fish text-blue-600 text-3xl"></i>
                        </div>
                        <h2 class="text-3xl font-bold text-gray-800">Welcome Back</h2>
                        <p class="text-gray-500 mt-2 text-sm">Sign in to your account to continue</p>
                    </div>

                    <?php if ($success): ?>
                        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded">
                            <div class="flex items-center gap-2">
                                <i class="fas fa-check-circle"></i>
                                <span><?php echo htmlspecialchars($success); ?></span>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($error): ?>
                        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded">
                            <div class="flex items-center gap-2">
                                <i class="fas fa-exclamation-circle"></i>
                                <span><?php echo htmlspecialchars($error); ?></span>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Username -->
                    <div class="mb-5">
                        <label for="username" class="block text-sm font-medium text-gray-700 mb-2">
                            Username
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-gray-400 pointer-events-none">
                                <i class="fas fa-user"></i>
                            </span>
                            <input
                                type="text"
                                id="username"
                                name="username"
                                required
                                autocomplete="username"
                                placeholder="Enter your username"
                                value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                                class="w-full pl-11 pr-4 py-3 border-2 border-gray-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 transition"
                            />
                        </div>
                    </div>

                    <!-- Password -->
                    <div class="mb-5">
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                            Password
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-gray-400 pointer-events-none">
                                <i class="fas fa-lock"></i>
                            </span>
                            <input
                                type="password"
                                id="password"
                                name="password"
                                required
                                autocomplete="current-password"
                                placeholder="Enter your password"
                                class="w-full pl-11 pr-12 py-3 border-2 border-gray-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 transition"
                            />
                            <button type="button"
                                onclick="togglePassword()"
                                class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 hover:text-gray-600"
                                aria-label="Toggle password visibility">
                                <i id="toggle-icon" class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Forgot password -->
                    <div class="flex justify-end mb-6">
                        <a href="<?php echo BASE_URL; ?>/views/auth/forgot_password.php"
                           class="text-sm text-blue-600 hover:text-blue-800 hover:underline">
                            Forgot password?
                        </a>
                    </div>

                    <!-- Submit -->
                    <button type="submit"
                        class="w-full bg-gradient-to-r from-blue-600 to-blue-700 text-white py-3 rounded-lg font-semibold hover:from-blue-700 hover:to-blue-800 transition duration-300 transform hover:scale-105 shadow-lg">
                        <i class="fas fa-sign-in-alt mr-2"></i>Log In
                    </button>

                    <!-- Divider -->
                    <div class="relative my-6">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-gray-200"></div>
                        </div>
                        <div class="relative flex justify-center text-xs text-gray-400 bg-white px-3">OR</div>
                    </div>

                    <!-- Create account -->
                    <a href="<?php echo BASE_URL; ?>/views/auth/register.php"
                       class="block w-full text-center border-2 border-blue-600 text-blue-600 py-3 rounded-lg font-semibold hover:bg-blue-50 transition duration-300">
                        <i class="fas fa-user-plus mr-2"></i>Create Account
                    </a>

                    <!-- Back to home -->
                    <div class="text-center mt-5">
                        <a href="<?php echo BASE_URL; ?>/landing.php"
                           class="text-sm text-gray-500 hover:text-gray-700">
                            <i class="fas fa-arrow-left mr-1"></i>Back to Home
                        </a>
                    </div>

                </form>
            </div>
        </div>
    </div>
    <script>
        function togglePassword() {
            const field = document.getElementById('password');
            const icon  = document.getElementById('toggle-icon');
            if (field.type === 'password') {
                field.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                field.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }
    </script>
</body>

</html>