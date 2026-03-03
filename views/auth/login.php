<?php

/**
 * Login Page
 * Handles user authentication for all roles (Customer, Staff, Admin)
 * 
 * Features:
 * - Redirects logged-in users to their dashboard
 * - Shows logout success message
 * - Displays authentication errors
 * - Links to public landing page
 */

require_once '../../config/config.php';
require_once '../../controllers/AuthController.php';

$authController = new AuthController();

// Redirect already logged-in users to their appropriate dashboard
$authController->redirectIfLoggedIn();

// Initialize variables for messages
$error = null;
$success = null;

// Check for logout success message
if (isset($_SESSION['logout_success'])) {
    $success = $_SESSION['logout_success'];
    unset($_SESSION['logout_success']);
}

// Check for registration success message
if (isset($_SESSION['registration_success'])) {
    $success = $_SESSION['registration_success'];
    unset($_SESSION['registration_success']);
}

// Check for authentication required error
if (isset($_GET['error']) && $_GET['error'] === 'auth_required') {
    $error = "Please login to access that page.";
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = "Username and password are required!";
    } elseif ($authController->login($username, $password)) {
        // Login successful - use the postLoginRedirect method
        $authController->postLoginRedirect();
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

        .input-field {
            position: relative;
            margin-bottom: 1.5rem;
        }

        .input-field input {
            width: 100%;
            padding: 15px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s;
        }

        .input-field input:focus {
            outline: none;
            border-color: #3b82f6;
        }

        .input-field input:focus+label,
        .input-field input:valid+label {
            top: -10px;
            left: 10px;
            font-size: 12px;
            color: #3b82f6;
            background: white;
            padding: 0 5px;
        }

        .input-field label {
            position: absolute;
            top: 15px;
            left: 15px;
            color: #6b7280;
            pointer-events: none;
            transition: all 0.3s;
        }

        .login-card {
            animation: slideUp 0.5s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
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
                <form method="POST" class="bg-white rounded-3xl shadow-2xl p-8 md:p-10">
                    <div class="text-center mb-8">
                        <div class="inline-block bg-blue-100 p-4 rounded-full mb-4">
                            <i class="fas fa-fish text-blue-600 text-3xl"></i>
                        </div>
                        <h2 class="text-3xl font-bold text-gray-800">Login Form</h2>
                        <p class="text-gray-600 mt-2">Welcome back! Please login to your account</p>
                    </div>

                    <?php if (isset($success)): ?>
                        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded animate-pulse">
                            <div class="flex items-center">
                                <i class="fas fa-check-circle mr-2"></i>
                                <span><?php echo htmlspecialchars($success); ?></span>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($error)): ?>
                        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded">
                            <div class="flex items-center">
                                <i class="fas fa-exclamation-circle mr-2"></i>
                                <span><?php echo htmlspecialchars($error); ?></span>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="input-field">
                        <input type="text" name="username" required />
                        <label>Enter username</label>
                    </div>

                    <div class="input-field">
                        <input type="password" name="password" required />
                        <label>Enter password</label>
                    </div>

                    <div class="forget flex justify-between items-center mb-6">
                        <label for="Save-login" class="flex items-center cursor-pointer">
                            <input type="checkbox" id="Save-login" class="mr-2 w-4 h-4 text-blue-600" />
                            <p class="text-sm text-gray-600">Save login information</p>
                        </label>
                        <a href="<?php echo BASE_URL; ?>/views/auth/forgot_password.php" class="text-sm text-blue-600 hover:text-blue-800">Forgot password?</a>
                    </div>

                    <button type="submit" class="w-full bg-gradient-to-r from-blue-600 to-blue-700 text-white py-3 rounded-lg font-semibold hover:from-blue-700 hover:to-blue-800 transition duration-300 transform hover:scale-105 shadow-lg">
                        <i class="fas fa-sign-in-alt mr-2"></i>Log In
                    </button>
                    <div class="Create-account text-center mt-6 px-8 pb-6">
                        <p class="text-gray-600">Don't have an account? <a href="<?php echo BASE_URL; ?>/views/auth/register.php" class="text-blue-600 hover:text-blue-800 font-semibold underline">Create Account</a></p>
                    </div>

                    <!-- Demo Accounts -->
                    <div class="mt-6 p-4 bg-gradient-to-r from-gray-50 to-blue-50 rounded-lg border border-blue-100">
                        <p class="text-sm font-semibold text-gray-700 mb-2 flex items-center">
                            <i class="fas fa-info-circle text-blue-600 mr-2"></i>Demo Accounts:
                        </p>

                        <div class="text-xs text-gray-600 space-y-1 ml-6">
                            <p><strong>Admin:</strong> admin / password</p>
                            <p><strong>Staff:</strong> staff1 / password</p>
                            <p><strong>Customer:</strong> customer1 / password</p>
                        </div>

                    </div>
                </form>
                <!-- Create Account Link - Moved outside form for proper navigation -->
            </div>
        </div>
    </div>
</body>

</html>