<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/User.php';

class AuthController
{
    private $db;
    private $user;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->user = new User($this->db);
    }

    public function login($username, $password)
    {
        $this->user->username = $username;
        $this->user->password = $password;

        if ($this->user->login()) {
            $_SESSION['user_id'] = $this->user->id;
            $_SESSION['username'] = $this->user->username;
            $_SESSION['role'] = $this->user->role;
            $_SESSION['full_name'] = $this->user->full_name;

            return true;
        }
        return false;
    }

    public function register($data)
    {
        try {
            // Check for duplicate username before attempting INSERT
            if ($this->user->usernameExists($data['username'])) {
                return "That username is already taken. Please choose a different one.";
            }

            // Check for duplicate email
            if ($this->user->emailExists($data['email'])) {
                return "An account with that email address already exists. Try logging in instead.";
            }

            $this->user->username  = $data['username'];
            $this->user->email     = $data['email'];
            $this->user->password  = $data['password'];
            $this->user->full_name = $data['full_name'];
            $this->user->phone     = $data['phone'];
            $this->user->address   = $data['address'];
            $this->user->role      = 'customer';

            if ($this->user->register()) {
                return true;
            }
            return "Registration failed. Please try again.";
        } catch (\Throwable $e) {
            error_log('Register error: ' . $e->getMessage());
            return "Registration failed. Please check your connection and try again.";
        }
    }

    /**
     * Initiate a password reset: generates a token and sends (or returns) a reset URL.
     * @param string $email
     * @return array ['success' => bool, 'message' => string, 'reset_url' => string|null]
     */
    public function initPasswordReset($email)
    {
        $user = $this->user->getByEmail($email);
        if (!$user) {
            // Return a generic message so we don't reveal whether the email exists
            return [
                'success' => true,
                'message' => "If that email is registered, you will receive a password reset link shortly.",
                'reset_url' => null
            ];
        }

        // Generate a cryptographically secure token
        $token   = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $this->user->setResetToken($user['id'], $token, $expires);

        $reset_url = BASE_URL . '/views/auth/reset_password.php?token=' . $token;

        // Attempt to send email via PHPMailer
        $email_sent = false;
        if (defined('MAIL_USERNAME') && MAIL_USERNAME !== 'your_email@gmail.com') {
            try {
                require_once __DIR__ . '/../vendor/autoload.php';
                $mail = new PHPMailer\PHPMailer\PHPMailer(true);
                $mail->isSMTP();
                $mail->Host       = MAIL_HOST;
                $mail->SMTPAuth   = true;
                $mail->Username   = MAIL_USERNAME;
                $mail->Password   = MAIL_PASSWORD;
                $mail->SMTPSecure = MAIL_ENCRYPTION;
                $mail->Port       = MAIL_PORT;
                $mail->setFrom(MAIL_FROM_EMAIL, MAIL_FROM_NAME);
                $mail->addAddress($email, $user['full_name']);
                $mail->isHTML(true);
                $mail->Subject = 'Password Reset - ' . SITE_NAME;
                $mail->Body    = '
                    <p>Hello ' . htmlspecialchars($user['full_name']) . ',</p>
                    <p>Click the link below to reset your password. This link expires in 1 hour.</p>
                    <p><a href="' . $reset_url . '" style="background:#0891b2;color:#fff;padding:12px 24px;border-radius:6px;text-decoration:none;">Reset Password</a></p>
                    <p>Or copy this URL: ' . $reset_url . '</p>
                    <p>If you did not request this, ignore this email.</p>';
                $mail->send();
                $email_sent = true;
            } catch (Exception $e) {
                // Email failed — fall through to show link in dev mode
            }
        }

        return [
            'success'   => true,
            'message'   => $email_sent
                ? "A password reset link has been sent to your email address."
                : "Reset link generated. (Email not configured — see link below for testing.)",
            'reset_url' => $email_sent ? null : $reset_url,
            'dev_mode'  => !$email_sent
        ];
    }

    /**
     * Complete a password reset using a valid token.
     * @param string $token
     * @param string $new_password
     * @return array ['success' => bool, 'message' => string]
     */
    public function completePasswordReset($token, $new_password)
    {
        $user = $this->user->getUserByResetToken($token);
        if (!$user) {
            return ['success' => false, 'message' => 'This reset link is invalid or has expired. Please request a new one.'];
        }

        $this->user->resetPasswordById($user['id'], $new_password);
        $this->user->clearResetToken($user['id']);

        return ['success' => true, 'message' => 'Your password has been reset successfully. You can now log in.'];
    }

    /**
     * Enhanced logout with proper session cleanup
     * Destroys all session data and redirects to login page with success message
     */
    public function logout()
    {
        // Unset all session variables
        $_SESSION = array();

        // Delete the session cookie if it exists
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }

        // Destroy the session
        session_destroy();

        // Start a new session for flash message
        session_start();
        $_SESSION['logout_success'] = 'You have been logged out successfully.';

        // Redirect to login page
        header("Location: " . BASE_URL . "/views/auth/login.php");
        exit();
    }

    /**
     * Check if user is currently logged in
     * @return bool
     */
    public function isLoggedIn()
    {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }

    /**
     * Get current user role
     * @return string|null
     */
    public function getUserRole()
    {
        return $_SESSION['role'] ?? null;
    }

    /**
     * Redirect logged-in user to their appropriate dashboard
     * Used on login page to prevent logged-in users from seeing login form
     */
    public function redirectIfLoggedIn()
    {
        if ($this->isLoggedIn()) {
            $role = $this->getUserRole();

            if (in_array($role, ['admin', 'staff'])) {
                header("Location: " . BASE_URL . "/views/staff/dashboard.php");
                exit();
            } else {
                header("Location: " . BASE_URL . "/views/customer/browse_fish.php");
                exit();
            }
        }
    }

    /**
     * Require authentication - redirect to login if not authenticated
     * Used on protected pages
     */
    public function requireAuth()
    {
        if (!$this->isLoggedIn()) {
            // Store the requested page for redirect after login
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];

            header("Location: " . BASE_URL . "/views/auth/login.php?error=auth_required");
            exit();
        }
    }

    /**
     * Require specific role(s) - redirect if user doesn't have required role
     * @param array $allowedRoles Array of allowed role names
     */
    public function requireRole($allowedRoles)
    {
        // First check if user is logged in
        $this->requireAuth();

        // Check if user has one of the allowed roles
        if (!in_array($_SESSION['role'], $allowedRoles)) {
            // Redirect to appropriate page based on user's actual role
            $role = $this->getUserRole();

            if (in_array($role, ['admin', 'staff'])) {
                header("Location: " . BASE_URL . "/views/staff/dashboard.php?error=access_denied");
            } else {
                header("Location: " . BASE_URL . "/views/customer/browse_fish.php?error=access_denied");
            }
            exit();
        }
    }

    /**
     * Handle post-login redirect
     * Redirects to stored URL or default dashboard
     */
    public function postLoginRedirect()
    {
        // Check if there's a stored redirect URL
        if (isset($_SESSION['redirect_after_login'])) {
            $redirect = $_SESSION['redirect_after_login'];
            unset($_SESSION['redirect_after_login']);
            header("Location: " . $redirect);
            exit();
        }

        // Default redirect based on role
        if (in_array($_SESSION['role'], ['admin', 'staff'])) {
            header('Location: ' . BASE_URL . '/views/staff/dashboard.php');
        } else {
            header('Location: ' . BASE_URL . '/views/customer/browse_fish.php');
        }
        exit();
    }
}
