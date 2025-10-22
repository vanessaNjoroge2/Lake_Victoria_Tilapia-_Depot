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

    public function logout()
    {
        session_destroy();
        header("Location: " . BASE_URL . "/views/auth/login.php");
        exit();
    }

    public function isLoggedIn()
    {
        return isset($_SESSION['user_id']);
    }

    public function requireAuth()
    {
        if (!$this->isLoggedIn()) {
            header("Location: " . BASE_URL . "/views/auth/login.php");
            exit();
        }
    }

    public function requireRole($allowedRoles)
    {
        $this->requireAuth();
        if (!in_array($_SESSION['role'], $allowedRoles)) {
            header("Location: " . BASE_URL . "/views/customer/browse_fish.php");
            exit();
        }
    }
}
