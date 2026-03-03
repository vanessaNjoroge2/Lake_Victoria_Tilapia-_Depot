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
            margin-bottom: 20px;
        }

        .input-field input,
        .input-field textarea {
            width: 100%;
            padding: 15px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s;
            background: white;
        }

        .input-field input:focus,
        .input-field textarea:focus {
            outline: none;
            border-color: #06b6d4;
            box-shadow: 0 0 0 3px rgba(6, 182, 212, 0.1);
        }

        .input-field input:focus+label,
        .input-field input:valid+label,
        .input-field textarea:focus+label,
        .input-field textarea:valid+label {
            top: -10px;
            left: 10px;
            font-size: 12px;
            color: #06b6d4;
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

                <div class="grid md:grid-cols-2 gap-4">
                    <div class="input-field">
                        <input type="text" name="full_name" required value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>" />
                        <label>Full Name *</label>
                    </div>

                    <div class="input-field">
                        <input type="text" name="username" required value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" />
                        <label>Username *</label>
                    </div>

                    <div class="input-field">
                        <input type="email" name="email" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" />
                        <label>Email Address *</label>
                    </div>

                    <div class="input-field">
                        <input type="tel" name="phone" required value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>" />
                        <label>Phone Number *</label>
                    </div>

                    <div class="input-field">
                        <input type="password" name="password" required />
                        <label>Password * (min 6 characters)</label>
                    </div>

                    <div class="input-field">
                        <input type="password" name="confirm_password" required />
                        <label>Confirm Password *</label>
                    </div>
                </div>

                <div class="input-field">
                    <textarea name="address" rows="3"><?php echo htmlspecialchars($_POST['address'] ?? ''); ?></textarea>
                    <label>Delivery Address (Optional)</label>
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

                <div class="text-center mt-6">
                    <p class="text-gray-600">Already have an account? <a href="<?php echo BASE_URL; ?>/views/auth/login.php" class="text-cyan-600 hover:text-cyan-800 font-semibold">Login here</a></p>
                </div>
            </form>
        </div>
    </div>
</body>

</html>