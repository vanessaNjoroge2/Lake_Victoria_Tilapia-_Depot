<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/User.php';

class UserController
{
    private $db;
    private $user;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->user = new User($this->db);
    }

    public function getCustomerStats()
    {
        $query = "SELECT 
                    COUNT(*) as total_customers,
                    COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as new_customers_30_days
                 FROM users 
                 WHERE role = 'customer'";

        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Get all users
    public function getAllUsers()
    {
        try {
            return $this->user->getAllUsers();
        } catch (Exception $e) {
            error_log("Get all users error: " . $e->getMessage());
            return [];
        }
    }

    // Get all staff users
    public function getAllStaff()
    {
        try {
            return $this->user->getAllStaff();
        } catch (Exception $e) {
            error_log("Get all staff error: " . $e->getMessage());
            return [];
        }
    }

    // Get user by ID
    public function getUserById($id)
    {
        try {
            return $this->user->getUserById($id);
        } catch (Exception $e) {
            error_log("Get user by ID error: " . $e->getMessage());
            return null;
        }
    }

    // Add new staff user
    public function addStaffUser($data)
    {
        try {
            // Validate required fields
            if (empty($data['username']) || empty($data['email']) || empty($data['password']) || empty($data['full_name'])) {
                return ['success' => false, 'message' => 'Please fill all required fields.'];
            }

            // Check if username already exists
            if ($this->user->usernameExists($data['username'])) {
                return ['success' => false, 'message' => 'Username already exists.'];
            }

            // Check if email already exists
            if ($this->user->emailExists($data['email'])) {
                return ['success' => false, 'message' => 'Email already exists.'];
            }

            // Validate email format
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                return ['success' => false, 'message' => 'Invalid email format.'];
            }

            // Add the user
            $result = $this->user->addStaffUser(
                $data['username'],
                $data['email'],
                $data['password'],
                $data['full_name'],
                $data['phone'] ?? null,
                $data['address'] ?? null,
                $data['role'] ?? 'staff'
            );

            if ($result) {
                return ['success' => true, 'message' => 'User added successfully.'];
            } else {
                return ['success' => false, 'message' => 'Failed to add user.'];
            }
        } catch (Exception $e) {
            error_log("Add staff user error: " . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred while adding user.'];
        }
    }

    // Update user profile
    public function updateUser($id, $data)
    {
        try {
            // Validate required fields
            if (empty($data['full_name']) || empty($data['email'])) {
                return ['success' => false, 'message' => 'Please fill all required fields.'];
            }

            // Validate email format
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                return ['success' => false, 'message' => 'Invalid email format.'];
            }

            // Update the user
            $result = $this->user->updateProfileWithParams(
                $id,
                $data['full_name'],
                $data['email'],
                $data['phone'] ?? null,
                $data['address'] ?? null
            );

            if ($result) {
                // Update role if provided and user is admin
                if (isset($data['role']) && isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
                    $this->user->updateRole($id, $data['role']);
                }

                return ['success' => true, 'message' => 'User updated successfully.'];
            } else {
                return ['success' => false, 'message' => 'Failed to update user.'];
            }
        } catch (Exception $e) {
            error_log("Update user error: " . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred while updating user.'];
        }
    }

    // Delete user
    public function deleteUser($id)
    {
        try {
            // Prevent deleting current user
            if ($id == ($_SESSION['user_id'] ?? 0)) {
                return ['success' => false, 'message' => 'Cannot delete your own account.'];
            }

            $result = $this->user->deleteUser($id);

            if ($result) {
                return ['success' => true, 'message' => 'User deleted successfully.'];
            } else {
                return ['success' => false, 'message' => 'Failed to delete user.'];
            }
        } catch (Exception $e) {
            error_log("Delete user error: " . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred while deleting user.'];
        }
    }

    // Change password
    public function changePassword($user_id, $current_password, $new_password, $confirm_password)
    {
        try {
            // Validate inputs
            if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
                return ['success' => false, 'message' => 'Please fill all password fields.'];
            }

            // Check if new passwords match
            if ($new_password !== $confirm_password) {
                return ['success' => false, 'message' => 'New passwords do not match.'];
            }

            // Check password strength (minimum 6 characters)
            if (strlen($new_password) < 6) {
                return ['success' => false, 'message' => 'Password must be at least 6 characters long.'];
            }

            // Change password
            $result = $this->user->changePassword($user_id, $current_password, $new_password);

            if ($result) {
                return ['success' => true, 'message' => 'Password changed successfully.'];
            } else {
                return ['success' => false, 'message' => 'Current password is incorrect.'];
            }
        } catch (Exception $e) {
            error_log("Change password error: " . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred while changing password.'];
        }
    }

    // Search users
    public function searchUsers($search_term)
    {
        try {
            return $this->user->searchUsers($search_term);
        } catch (Exception $e) {
            error_log("Search users error: " . $e->getMessage());
            return [];
        }
    }

    // Get user statistics
    public function getUserStatistics()
    {
        try {
            return $this->user->getUserStatistics();
        } catch (Exception $e) {
            error_log("Get user statistics error: " . $e->getMessage());
            return [
                'total_users' => 0,
                'total_customers' => 0,
                'total_staff' => 0,
                'total_admins' => 0
            ];
        }
    }
}
