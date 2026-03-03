<?php

/**
 * Reset Password Page
 * Validates the reset token from the URL and allows the user to set a new password.
 */

require_once '../../config/config.php';
require_once '../../controllers/AuthController.php';

$authController = new AuthController();
$authController->redirectIfLoggedIn();

$token   = trim($_GET['token'] ?? '');
$error   = null;
$success = null;

// If no token supplied, redirect away
if (empty($token)) {
    header("Location: " . BASE_URL . "/views/auth/forgot_password.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_password     = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $post_token       = $_POST['token'] ?? '';

    if (empty($new_password) || empty($confirm_password)) {
        $error = "Both password fields are required.";
    } elseif (strlen($new_password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } elseif ($new_password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        $result = $authController->completePasswordReset($post_token, $new_password);
        if ($result['success']) {
            $_SESSION['registration_success'] = $result['message'];
            header("Location: " . BASE_URL . "/views/auth/login.php");
            exit();
        } else {
            $error = $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - <?php echo SITE_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .fish-bg {
            background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
        }
    </style>
</head>

<body class="fish-bg min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <div class="bg-white rounded-3xl shadow-2xl p-8 md:p-10">

            <!-- Header -->
            <div class="text-center mb-8">
                <div class="inline-block bg-cyan-100 p-4 rounded-full mb-4">
                    <i class="fas fa-key text-cyan-600 text-3xl"></i>
                </div>
                <h2 class="text-3xl font-bold text-gray-800">Set New Password</h2>
                <p class="text-gray-500 mt-2 text-sm">Choose a strong password for your account.</p>
            </div>

            <?php if ($error): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-exclamation-circle"></i>
                        <span><?php echo htmlspecialchars($error); ?></span>
                    </div>
                    <?php if (str_contains($error, 'invalid or has expired')): ?>
                        <div class="mt-3">
                            <a href="<?php echo BASE_URL; ?>/views/auth/forgot_password.php"
                                class="text-sm underline text-red-700 hover:text-red-900">
                                Request a new reset link
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>" />

                <div class="mb-5">
                    <label class="block text-sm font-medium text-gray-700 mb-2" for="new_password">
                        New Password
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-gray-400">
                            <i class="fas fa-lock"></i>
                        </span>
                        <input
                            type="password"
                            id="new_password"
                            name="new_password"
                            required
                            minlength="6"
                            placeholder="Minimum 6 characters"
                            class="w-full pl-11 pr-12 py-3 border-2 border-gray-200 rounded-lg focus:outline-none focus:border-cyan-500 transition text-sm" />
                        <button type="button"
                            onclick="togglePassword('new_password', this)"
                            class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 hover:text-gray-600">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2" for="confirm_password">
                        Confirm New Password
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-gray-400">
                            <i class="fas fa-lock"></i>
                        </span>
                        <input
                            type="password"
                            id="confirm_password"
                            name="confirm_password"
                            required
                            placeholder="Re-enter your new password"
                            class="w-full pl-11 pr-12 py-3 border-2 border-gray-200 rounded-lg focus:outline-none focus:border-cyan-500 transition text-sm" />
                        <button type="button"
                            onclick="togglePassword('confirm_password', this)"
                            class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 hover:text-gray-600">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <button type="submit"
                    class="w-full bg-gradient-to-r from-cyan-600 to-cyan-700 text-white py-3 rounded-lg font-semibold hover:from-cyan-700 hover:to-cyan-800 transition transform hover:scale-105 shadow-lg">
                    <i class="fas fa-check mr-2"></i>Reset Password
                </button>
            </form>

            <div class="mt-6 text-center">
                <a href="<?php echo BASE_URL; ?>/views/auth/login.php"
                    class="text-sm text-cyan-600 hover:text-cyan-800 font-semibold">
                    <i class="fas fa-arrow-left mr-1"></i>Back to Login
                </a>
            </div>
        </div>
    </div>

    <script>
        function togglePassword(fieldId, btn) {
            const field = document.getElementById(fieldId);
            const icon = btn.querySelector('i');
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