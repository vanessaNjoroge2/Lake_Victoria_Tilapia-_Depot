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
        $this->user->username = $data['username'];
        $this->user->email = $data['email'];
        $this->user->password = $data['password'];
        $this->user->full_name = $data['full_name'];
        $this->user->phone = $data['phone'];
        $this->user->address = $data['address'];
        $this->user->role = 'customer';

        if ($this->user->register()) {
            return true;
        }
        return "Registration failed";
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
