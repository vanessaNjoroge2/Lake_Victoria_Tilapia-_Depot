<?php

/**
 * Registration Page
 * Allows new users to create customer accounts
 */

require_once '../../config/config.php';
require_once '../../controllers/AuthController.php';

$authController = new AuthController();

// Redirect already logged-in users to their dashboard
$authController->redirectIfLoggedIn();

// Initialize variables for messages
$error = null;
$success = null;

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $full_name = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');

    // Validation
    if (empty($username) || empty($email) || empty($password) || empty($full_name) || empty($phone)) {
        $error = "All fields except address are required!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format!";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long!";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } else {
        // Attempt registration
        $result = $authController->register([
            'username' => $username,
            'email' => $email,
            'password' => $password,
            'full_name' => $full_name,
            'phone' => $phone,
            'address' => $address
        ]);

        if ($result === true) {
            // Registration successful - set success message and redirect to login
            // Session already started in config.php
            $_SESSION['registration_success'] = 'Account created successfully! Please login with your credentials.';
            header("Location: " . BASE_URL . "/views/auth/login.php");
            exit();
        } else {
            $error = is_string($result) ? $result : "Registration failed. Username or email may already exist.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - <?php echo SITE_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .fish-bg {
            background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
        }

        .register-card {
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
    <div class="container max-w-5xl mx-auto">
        <div class="register-card">
            <form method="POST" class="bg-white rounded-3xl shadow-2xl p-8 md:p-10">
                <div class="text-center mb-8">
                    <div class="inline-block bg-cyan-100 p-4 rounded-full mb-4">
                        <i class="fas fa-user-plus text-cyan-600 text-3xl"></i>
                    </div>
                    <h2 class="text-3xl font-bold text-gray-800">Create Account</h2>
                    <p class="text-gray-600 mt-2">Join Lake Victoria Tilapia Depot today!</p>
                </div>

                <?php if (isset($error)): ?>
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded">
                        <div class="flex items-center">
                            <i class="fas fa-exclamation-circle mr-2"></i>
                            <span><?php echo htmlspecialchars($error); ?></span>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="grid md:grid-cols-2 gap-4 mb-4">

                    <!-- Full Name -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1" for="full_name">Full Name <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 pointer-events-none"><i class="fas fa-user text-sm"></i></span>
                            <input type="text" id="full_name" name="full_name" required autocomplete="name"
                                placeholder="Your full name"
                                value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>"
                                class="w-full pl-9 pr-3 py-2.5 border-2 border-gray-200 rounded-lg text-sm focus:outline-none focus:border-cyan-500 transition" />
                        </div>
                    </div>

                    <!-- Username -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1" for="reg_username">Username <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 pointer-events-none"><i class="fas fa-at text-sm"></i></span>
                            <input type="text" id="reg_username" name="username" required autocomplete="username"
                                placeholder="Choose a username"
                                value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                                class="w-full pl-9 pr-3 py-2.5 border-2 border-gray-200 rounded-lg text-sm focus:outline-none focus:border-cyan-500 transition" />
                        </div>
                    </div>

                    <!-- Email -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1" for="email">Email Address <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 pointer-events-none"><i class="fas fa-envelope text-sm"></i></span>
                            <input type="email" id="email" name="email" required autocomplete="email"
                                placeholder="you@example.com"
                                value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                                class="w-full pl-9 pr-3 py-2.5 border-2 border-gray-200 rounded-lg text-sm focus:outline-none focus:border-cyan-500 transition" />
                        </div>
                    </div>

                    <!-- Phone -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1" for="phone">Phone Number <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 pointer-events-none"><i class="fas fa-phone text-sm"></i></span>
                            <input type="tel" id="phone" name="phone" required autocomplete="tel"
                                placeholder="07XXXXXXXX"
                                value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>"
                                class="w-full pl-9 pr-3 py-2.5 border-2 border-gray-200 rounded-lg text-sm focus:outline-none focus:border-cyan-500 transition" />
                        </div>
                    </div>

                    <!-- Password -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1" for="reg_password">Password <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 pointer-events-none"><i class="fas fa-lock text-sm"></i></span>
                            <input type="password" id="reg_password" name="password" required autocomplete="new-password"
                                minlength="6" placeholder="Min 6 characters"
                                class="w-full pl-9 pr-10 py-2.5 border-2 border-gray-200 rounded-lg text-sm focus:outline-none focus:border-cyan-500 transition" />
                            <button type="button" onclick="togglePwd('reg_password','icon1')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                                <i id="icon1" class="fas fa-eye text-sm"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1" for="confirm_password">Confirm Password <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 pointer-events-none"><i class="fas fa-lock text-sm"></i></span>
                            <input type="password" id="confirm_password" name="confirm_password" required autocomplete="new-password"
                                placeholder="Re-enter password"
                                class="w-full pl-9 pr-10 py-2.5 border-2 border-gray-200 rounded-lg text-sm focus:outline-none focus:border-cyan-500 transition" />
                            <button type="button" onclick="togglePwd('confirm_password','icon2')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                                <i id="icon2" class="fas fa-eye text-sm"></i>
                            </button>
                        </div>
                    </div>

                </div>

                <!-- Address -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1" for="address">Delivery Address <span class="text-gray-400">(Optional)</span></label>
                    <div class="relative">
                        <span class="absolute top-3 left-3 text-gray-400 pointer-events-none"><i class="fas fa-map-marker-alt text-sm"></i></span>
                        <textarea id="address" name="address" rows="2" placeholder="Street, area, city..."
                            class="w-full pl-9 pr-3 py-2.5 border-2 border-gray-200 rounded-lg text-sm focus:outline-none focus:border-cyan-500 transition resize-none"><?php echo htmlspecialchars($_POST['address'] ?? ''); ?></textarea>
                    </div>
                </div>

                <div class="mb-6 p-4 bg-cyan-50 rounded-lg border border-cyan-200">
                    <p class="text-sm text-gray-700">
                        <i class="fas fa-info-circle text-cyan-600 mr-2"></i>
                        By creating an account, you'll be able to browse fresh fish, place orders, and track your deliveries.
                    </p>
                </div>

                <button type="submit" class="w-full bg-gradient-to-r from-cyan-600 to-cyan-700 text-white py-3 rounded-lg font-semibold hover:from-cyan-700 hover:to-cyan-800 transition duration-300 transform hover:scale-105 shadow-lg">
                    <i class="fas fa-user-plus mr-2"></i>Create Account
                </button>

                <div class="mt-4 grid grid-cols-2 gap-4">
                    <a href="<?php echo BASE_URL; ?>/views/auth/login.php" class="block text-center bg-white text-cyan-600 py-3 rounded-lg font-semibold border-2 border-cyan-600 hover:bg-cyan-50 transition duration-300">
                        <i class="fas fa-sign-in-alt mr-2"></i>Login Instead
                    </a>
                    <a href="<?php echo BASE_URL; ?>/landing.php" class="block text-center bg-white text-gray-600 py-3 rounded-lg font-semibold border-2 border-gray-400 hover:bg-gray-50 transition duration-300">
                        <i class="fas fa-home mr-2"></i>Back to Home
                    </a>
                </div>

                <div class="text-center mt-6 space-y-2">
                    <p class="text-gray-600">Already have an account? <a href="<?php echo BASE_URL; ?>/views/auth/login.php" class="text-cyan-600 hover:text-cyan-800 font-semibold">Login here</a></p>
                    <p class="text-sm text-gray-500">Forgot your password? <a href="<?php echo BASE_URL; ?>/views/auth/forgot_password.php" class="text-cyan-600 hover:text-cyan-800 font-semibold">Reset it here</a></p>
                </div>
            </form>
        </div>
    </div>
    <script>
        function togglePwd(fieldId, iconId) {
            const f = document.getElementById(fieldId);
            const i = document.getElementById(iconId);
            if (f.type === 'password') {
                f.type = 'text';
                i.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                f.type = 'password';
                i.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }
    </script>
</body>

</html>