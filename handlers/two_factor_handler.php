<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/sanitize.php';
require_once __DIR__ . '/../controllers/TwoFactorController.php';
require_once __DIR__ . '/../controllers/AuditController.php';

// Verify CSRF
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/views/auth/login.php');
    exit;
}

if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
    $_SESSION['flash'] = ['type' => 'error', 'message' => 'Security token mismatch. Please try again.'];
    header('Location: ' . BASE_URL . '/views/auth/verify_otp.php');
    exit;
}

// Check if there is a pending 2FA user
if (empty($_SESSION['temp_2fa_user_id'])) {
    $_SESSION['flash'] = ['type' => 'error', 'message' => 'Session expired. Please log in again.'];
    header('Location: ' . BASE_URL . '/views/auth/login.php');
    exit;
}

$userId = (int) $_SESSION['temp_2fa_user_id'];
$code = trim($_POST['code'] ?? '');

if (empty($code) || strlen($code) !== 6 || !ctype_digit($code)) {
    $_SESSION['flash'] = ['type' => 'error', 'message' => 'Please enter a valid 6-digit verification code.'];
    header('Location: ' . BASE_URL . '/views/auth/verify_otp.php');
    exit;
}

$twoFactorController = new TwoFactorController();
$result = $twoFactorController->verifyOTP($userId, $code);

if ($result['success']) {
    // Authenticate the user fully!
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        $query = "SELECT id, username, role, full_name FROM users WHERE id = :id LIMIT 1";
        $stmt = $db->prepare($query);
        $stmt->execute([':id' => $userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Regenerate session ID (session fixation prevention)
            session_regenerate_id(true);

            // Establish session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['full_name'] = $user['full_name'];
            
            // Session security bindings
            $_SESSION['last_activity'] = time();
            $_SESSION['user_ip'] = $_SERVER['REMOTE_ADDR'];

            // Log successful login
            AuditController::logActivity($user['id'], 'login_2fa_success', 'users', $user['id']);

            // Clear temporary 2FA states
            unset($_SESSION['temp_2fa_user_id']);
            unset($_SESSION['otp_attempts']);

            // Direct to stored redirection page or appropriate dashboard
            if (isset($_SESSION['redirect_after_login'])) {
                $redirect = $_SESSION['redirect_after_login'];
                unset($_SESSION['redirect_after_login']);
                header('Location: ' . $redirect);
            } else {
                if (in_array($user['role'], ['admin', 'staff'])) {
                    header('Location: ' . BASE_URL . '/views/staff/dashboard.php');
                } else {
                    header('Location: ' . BASE_URL . '/views/customer/browse_fish.php');
                }
            }
            exit;
        } else {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Authentication failed. User not found.'];
            header('Location: ' . BASE_URL . '/views/auth/login.php');
            exit;
        }

    } catch (Exception $e) {
        error_log("Database error in 2FA handler: " . $e->getMessage());
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'A system error occurred. Please try again.'];
        header('Location: ' . BASE_URL . '/views/auth/verify_otp.php');
        exit;
    }
} else {
    if (!empty($result['locked'])) {
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'Too many failed attempts. You have been locked out. Please log in again.'];
        header('Location: ' . BASE_URL . '/views/auth/login.php?reason=locked');
    } else {
        $_SESSION['flash'] = ['type' => 'error', 'message' => $result['message']];
        header('Location: ' . BASE_URL . '/views/auth/verify_otp.php');
    }
    exit;
}
