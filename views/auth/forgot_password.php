<?php

/**
 * Forgot Password Page
 * Accepts an email, generates a reset token, and either sends an email or
 * shows the reset link directly (when SMTP is not yet configured).
 */

require_once '../../config/config.php';
require_once '../../controllers/AuthController.php';

$authController = new AuthController();
$authController->redirectIfLoggedIn();

$error   = null;
$result  = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (empty($email)) {
        $error = "Please enter your email address.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {
        $result = $authController->initPasswordReset($email);
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - <?php echo SITE_NAME; ?></title>
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
                    <i class="fas fa-lock text-cyan-600 text-3xl"></i>
                </div>
                <h2 class="text-3xl font-bold text-gray-800">Forgot Password</h2>
                <p class="text-gray-500 mt-2 text-sm">Enter the email linked to your account and we'll send you a reset link.</p>
            </div>

            <?php if ($error): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-exclamation-circle"></i>
                        <span><?php echo htmlspecialchars($error); ?></span>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($result && $result['success']): ?>
                <!-- Success state -->
                <div class="bg-green-50 border border-green-200 rounded-xl p-6 text-center mb-6">
                    <i class="fas fa-check-circle text-green-500 text-4xl mb-3"></i>
                    <p class="text-green-800 font-semibold"><?php echo htmlspecialchars($result['message']); ?></p>

                    <?php if (!empty($result['dev_mode']) && !empty($result['reset_url'])): ?>
                        <div class="mt-4 p-3 bg-yellow-50 border border-yellow-300 rounded-lg text-left">
                            <p class="text-xs font-bold text-yellow-700 mb-2">
                                <i class="fas fa-tools mr-1"></i>Dev Mode — Reset Link:
                            </p>
                            <a href="<?php echo htmlspecialchars($result['reset_url']); ?>"
                                class="text-xs text-blue-600 underline break-all">
                                <?php echo htmlspecialchars($result['reset_url']); ?>
                            </a>
                            <p class="text-xs text-yellow-600 mt-2">
                                Configure SMTP in <code>config/config.php</code> to send this link by email instead.
                            </p>
                        </div>
                    <?php endif; ?>
                </div>

                <a href="<?php echo BASE_URL; ?>/views/auth/login.php"
                    class="block w-full text-center bg-cyan-600 text-white py-3 rounded-lg font-semibold hover:bg-cyan-700 transition">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Login
                </a>

            <?php else: ?>
                <!-- Email input form -->
                <form method="POST">
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2" for="email">
                            Email Address
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-gray-400">
                                <i class="fas fa-envelope"></i>
                            </span>
                            <input
                                type="email"
                                id="email"
                                name="email"
                                required
                                autocomplete="email"
                                value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                                placeholder="you@example.com"
                                class="w-full pl-11 pr-4 py-3 border-2 border-gray-200 rounded-lg focus:outline-none focus:border-cyan-500 transition text-sm" />
                        </div>
                    </div>

                    <button type="submit"
                        class="w-full bg-gradient-to-r from-cyan-600 to-cyan-700 text-white py-3 rounded-lg font-semibold hover:from-cyan-700 hover:to-cyan-800 transition transform hover:scale-105 shadow-lg">
                        <i class="fas fa-paper-plane mr-2"></i>Send Reset Link
                    </button>
                </form>

                <div class="mt-6 text-center space-y-3">
                    <a href="<?php echo BASE_URL; ?>/views/auth/login.php"
                        class="block text-sm text-cyan-600 hover:text-cyan-800 font-semibold">
                        <i class="fas fa-arrow-left mr-1"></i>Back to Login
                    </a>
                    <a href="<?php echo BASE_URL; ?>/views/auth/register.php"
                        class="block text-sm text-gray-500 hover:text-gray-700">
                        <i class="fas fa-user-plus mr-1"></i>Don't have an account? Create one
                    </a>
                </div>
            <?php endif; ?>

        </div>
    </div>
</body>

</html>