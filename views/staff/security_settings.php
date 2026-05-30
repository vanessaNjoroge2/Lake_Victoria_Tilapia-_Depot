<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../controllers/AuthController.php';
require_once __DIR__ . '/../../controllers/UserController.php';
require_once __DIR__ . '/../../controllers/TwoFactorController.php';
require_once __DIR__ . '/../../includes/csrf.php';

$authController = new AuthController();
// Accessible by staff and admin
$authController->requireRole(['admin', 'staff']);

$userController = new UserController();
$twoFactorController = new TwoFactorController();

$error = '';
$success = '';

// Get current user details
$user = $userController->getUserById($_SESSION['user_id']);
if (!$user) {
    header('Location: dashboard.php');
    exit();
}

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($token)) {
        $error = "CSRF verification failed. Please try again.";
    } else {
        $action = $_POST['action'] ?? '';

        if ($action === 'change_password') {
            // Password change logic
            $current_password = $_POST['current_password'] ?? '';
            $new_password = $_POST['new_password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';

            $result = $userController->changePassword(
                $_SESSION['user_id'],
                $current_password,
                $new_password,
                $confirm_password
            );

            if ($result['success']) {
                $success = "Your password has been changed successfully!";
                // Log to audit log
                AuditController::logActivity($_SESSION['user_id'], 'Password Reset Completed', 'users', $_SESSION['user_id']);
            } else {
                $error = $result['message'];
            }
        } elseif ($action === 'toggle_2fa') {
            // Optional 2FA management for customers, but for staff/admin we remind them it's mandatory
            if (in_array($user['role'], ['admin', 'staff'])) {
                $error = "Security Policy: 2FA is strictly mandatory for all administrative and store staff accounts.";
            } else {
                // Toggle optional 2FA for customer role
                $db = (new Database())->getConnection();
                $newState = empty($user['two_factor_enabled']) ? 1 : 0;
                $query = "UPDATE users SET two_factor_enabled = :state WHERE id = :id";
                $stmt = $db->prepare($query);
                if ($stmt->execute([':state' => $newState, ':id' => $_SESSION['user_id']])) {
                    $success = $newState ? "Two-Factor Authentication is now enabled!" : "Two-Factor Authentication is now disabled.";
                    $user = $userController->getUserById($_SESSION['user_id']);
                    AuditController::logActivity($_SESSION['user_id'], '2FA Optional Toggle modified to ' . $newState, 'users', $_SESSION['user_id']);
                } else {
                    $error = "Failed to update 2FA status.";
                }
            }
        }
    }
}

// Gather Active Session details
$ipAddress = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown Agent';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Security Settings - <?php echo SITE_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex">

    <!-- Sidebar Layout -->
    <?php include '../includes/staff_sidebar.php'; ?>

    <!-- Main Workspace -->
    <div class="flex-1 ml-64 p-8">
        <div class="max-w-4xl mx-auto">
            
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-800">Security Settings</h1>
                <p class="text-gray-600">Configure account authentication keys, view active login sessions, and review 2FA compliance</p>
            </div>

            <!-- Alerts -->
            <?php if ($error): ?>
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-6 font-semibold text-xs flex items-center">
                    <i class="fas fa-exclamation-circle mr-2 text-red-500 text-base"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl mb-6 font-semibold text-xs flex items-center">
                    <i class="fas fa-check-circle mr-2 text-green-500 text-base"></i> <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-xs font-semibold">
                
                <!-- Security Compliance Overview -->
                <div class="md:col-span-1 space-y-6">
                    <!-- Card 1: 2FA State -->
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                        <div class="flex items-center gap-2 mb-4">
                            <i class="fas fa-shield-halved text-blue-600 text-lg"></i>
                            <h3 class="text-slate-800 font-bold text-sm uppercase tracking-wider">MFA Profile</h3>
                        </div>
                        
                        <div class="space-y-4">
                            <div class="flex justify-between items-center bg-slate-50 p-3 rounded-xl border border-slate-100">
                                <span class="text-slate-500">OTP Auth State:</span>
                                <?php if ($twoFactorController->is2faRequired($user)): ?>
                                    <span class="bg-blue-50 text-blue-700 px-2 py-0.5 rounded text-[9px] font-bold uppercase border border-blue-100">Mandatory</span>
                                <?php else: ?>
                                    <span class="bg-slate-100 text-slate-600 px-2 py-0.5 rounded text-[9px] font-bold uppercase">Optional</span>
                                <?php endif; ?>
                            </div>
                            
                            <p class="text-[10px] text-slate-400 font-semibold leading-relaxed">
                                Under Store Security Policy, all Admin and Staff personnel are <strong>required</strong> to authenticate logins using 6-digit SMS / Email verification codes (2FA) automatically.
                            </p>
                        </div>
                    </div>

                    <!-- Card 2: Session Data -->
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                        <div class="flex items-center gap-2 mb-4">
                            <i class="fas fa-network-wired text-purple-600 text-lg"></i>
                            <h3 class="text-slate-800 font-bold text-sm uppercase tracking-wider">Active Session</h3>
                        </div>
                        
                        <div class="space-y-3 font-semibold text-slate-600">
                            <div>
                                <span class="block text-[10px] text-slate-400 uppercase tracking-wider mb-0.5">Connection IP</span>
                                <span class="text-slate-800 font-bold"><?php echo htmlspecialchars($ipAddress); ?></span>
                            </div>
                            <div class="pt-2 border-t border-slate-100">
                                <span class="block text-[10px] text-slate-400 uppercase tracking-wider mb-0.5">Browser Access Agent</span>
                                <span class="text-slate-700 text-[10px] block leading-relaxed max-w-[220px] break-all"><?php echo htmlspecialchars($userAgent); ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Password and profile settings forms -->
                <div class="md:col-span-2 space-y-6">
                    <!-- Password Reset Card -->
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                        <div class="flex items-center gap-2 border-b border-slate-100 pb-3 mb-6">
                            <i class="fas fa-key text-amber-500 text-lg"></i>
                            <h3 class="text-slate-800 font-bold text-sm uppercase tracking-wider">Change Account Password</h3>
                        </div>

                        <form method="POST" action="" class="space-y-4">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="action" value="change_password">

                            <!-- Current Password -->
                            <div>
                                <label for="current_password" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">
                                    Current Password *
                                </label>
                                <input type="password" id="current_password" name="current_password" required
                                       class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 font-medium">
                            </div>

                            <!-- New Password -->
                            <div>
                                <label for="new_password" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">
                                    New Password *
                                </label>
                                <input type="password" id="new_password" name="new_password" required
                                       class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 font-medium">
                                <p class="text-[9px] text-slate-400 mt-1">Must be at least 6 characters long and combine letters/numbers.</p>
                            </div>

                            <!-- Confirm New Password -->
                            <div>
                                <label for="confirm_password" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">
                                    Confirm New Password *
                                </label>
                                <input type="password" id="confirm_password" name="confirm_password" required
                                       class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 font-medium">
                            </div>

                            <!-- Action button -->
                            <div class="pt-4 border-t border-slate-100 flex justify-end">
                                <button type="submit"
                                        class="bg-blue-600 text-white px-5 py-3 rounded-xl hover:bg-blue-700 font-bold shadow-md shadow-blue-500/10 flex items-center transition">
                                    <i class="fas fa-save mr-2"></i> Update Password
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <!-- 2FA Management Panel (Optional Toggle for Customer Accounts) -->
                    <?php if ($user['role'] === 'customer'): ?>
                        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                            <div class="flex items-center gap-2 border-b border-slate-100 pb-3 mb-4">
                                <i class="fas fa-shield text-blue-600 text-lg"></i>
                                <h3 class="text-slate-800 font-bold text-sm uppercase tracking-wider">Multi-Factor Authentication (2FA)</h3>
                            </div>

                            <div class="flex items-center justify-between p-4 bg-slate-50 border border-slate-100 rounded-xl mb-4">
                                <div>
                                    <h4 class="font-bold text-slate-800 mb-0.5">Toggle optional 2FA</h4>
                                    <p class="text-[10px] text-slate-400 font-semibold leading-relaxed">Require SMS / Email OTP code at every login session</p>
                                </div>
                                <form method="POST" action="">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="action" value="toggle_2fa">
                                    <button type="submit" 
                                            class="px-4 py-2 rounded-xl text-[10px] font-bold uppercase transition border 
                                                   <?php echo $user['two_factor_enabled'] ? 'bg-red-50 text-red-700 border-red-200 hover:bg-red-100' : 'bg-green-50 text-green-700 border-green-200 hover:bg-green-100'; ?>">
                                        <?php echo $user['two_factor_enabled'] ? 'Disable 2FA' : 'Enable 2FA'; ?>
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

            </div>

        </div>
    </div>

</body>
</html>
