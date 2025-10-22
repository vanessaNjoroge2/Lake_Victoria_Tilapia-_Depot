<?php
class User
{
    private $conn;
    private $table = 'users';

    // Property declarations
    public $id;
    public $username;
    public $email;
    public $password;
    public $full_name;
    public $phone;
    public $address;
    public $role;
    public $created_at;
    public $updated_at;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // CREATE - Register new user
    public function register()
    {
        $query = "INSERT INTO " . $this->table . " 
                 SET username=:username, email=:email, password=:password, 
                     full_name=:full_name, phone=:phone, address=:address, role=:role";

        $stmt = $this->conn->prepare($query);

        // Hash password
        $this->password = password_hash($this->password, PASSWORD_DEFAULT);

        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":password", $this->password);
        $stmt->bindParam(":full_name", $this->full_name);
        $stmt->bindParam(":phone", $this->phone);
        $stmt->bindParam(":address", $this->address);
        $stmt->bindParam(":role", $this->role);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // READ - Login user
    public function login()
    {
        $query = "SELECT * FROM " . $this->table . " WHERE username = :username LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":username", $this->username);
        $stmt->execute();

        if ($stmt->rowCount() == 1) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (password_verify($this->password, $row['password'])) {
                $this->id = $row['id'];
                $this->username = $row['username'];
                $this->email = $row['email'];
                $this->full_name = $row['full_name'];
                $this->role = $row['role'];
                $this->phone = $row['phone'];
                $this->address = $row['address'];
                return true;
            }
        }
        return false;
    }

    // READ - Get user by ID
    public function getUserById($id)
    {
        $query = "SELECT id, username, email, full_name, phone, address, role, created_at 
                  FROM " . $this->table . " 
                  WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // READ - Check if username exists
    public function usernameExists($username)
    {
        $query = "SELECT id FROM " . $this->table . " WHERE username = :username";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":username", $username);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    // READ - Check if email exists
    public function emailExists($email)
    {
        $query = "SELECT id FROM " . $this->table . " WHERE email = :email";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $email);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    // UPDATE - Update user profile (method 1 - using object properties)
    public function updateProfile()
    {
        $query = "UPDATE " . $this->table . " 
                 SET full_name=:full_name, email=:email, phone=:phone, address=:address 
                 WHERE id=:id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":full_name", $this->full_name);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":phone", $this->phone);
        $stmt->bindParam(":address", $this->address);
        $stmt->bindParam(":id", $this->id);

        return $stmt->execute();
    }

    // UPDATE - Update user profile (method 2 - using parameters)
    public function updateProfileWithParams($user_id, $full_name, $email, $phone = null, $address = null)
    {
        $query = "UPDATE " . $this->table . " 
                 SET full_name = :full_name, email = :email, phone = :phone, address = :address 
                 WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":full_name", $full_name);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":phone", $phone);
        $stmt->bindParam(":address", $address);
        $stmt->bindParam(":id", $user_id);

        return $stmt->execute();
    }

    // UPDATE - Change user password
    public function changePassword($user_id, $current_password, $new_password)
    {
        // First verify current password
        $query = "SELECT password FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $user_id);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($current_password, $user['password'])) {
            // Current password is correct, update to new password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

            $updateQuery = "UPDATE " . $this->table . " SET password = :password WHERE id = :id";
            $updateStmt = $this->conn->prepare($updateQuery);
            $updateStmt->bindParam(":password", $hashed_password);
            $updateStmt->bindParam(":id", $user_id);

            return $updateStmt->execute();
        }

        return false;
    }

    // UPDATE - Update user role
    public function updateRole($user_id, $role)
    {
        $query = "UPDATE " . $this->table . " SET role = :role WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":role", $role);
        $stmt->bindParam(":id", $user_id);
        return $stmt->execute();
    }

    // READ - Get all users
    public function getAllUsers()
    {
        $query = "SELECT id, username, email, full_name, phone, address, role, created_at 
                  FROM " . $this->table . " 
                  ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // READ - Get all staff users
    public function getAllStaff()
    {
        $query = "SELECT id, username, email, full_name, phone, address, role, created_at 
                  FROM " . $this->table . " 
                  WHERE role IN ('admin', 'staff') 
                  ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // READ - Get all customers
    public function getAllCustomers()
    {
        $query = "SELECT id, username, email, full_name, phone, address, role, created_at 
                  FROM " . $this->table . " 
                  WHERE role = 'customer' 
                  ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // READ - Get user count by role
    public function getUserCountByRole($role)
    {
        $query = "SELECT COUNT(*) as count FROM " . $this->table . " WHERE role = :role";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":role", $role);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'];
    }

    // CREATE - Add new staff user
    public function addStaffUser($username, $email, $password, $full_name, $phone = null, $address = null, $role = 'staff')
    {
        $query = "INSERT INTO " . $this->table . " 
                 SET username=:username, email=:email, password=:password, 
                     full_name=:full_name, phone=:phone, address=:address, role=:role";

        $stmt = $this->conn->prepare($query);

        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $stmt->bindParam(":username", $username);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":password", $hashed_password);
        $stmt->bindParam(":full_name", $full_name);
        $stmt->bindParam(":phone", $phone);
        $stmt->bindParam(":address", $address);
        $stmt->bindParam(":role", $role);

        return $stmt->execute();
    }

    // DELETE - Delete user
    public function deleteUser($id)
    {
        // Prevent deleting your own account
        if ($id == $_SESSION['user_id'] ?? 0) {
            return false;
        }

        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }

    // READ - Search users
    public function searchUsers($search_term)
    {
        $query = "SELECT id, username, email, full_name, phone, address, role, created_at 
                  FROM " . $this->table . " 
                  WHERE username LIKE :search OR email LIKE :search OR full_name LIKE :search 
                  ORDER BY created_at DESC";

        $stmt = $this->conn->prepare($query);
        $search_term = "%$search_term%";
        $stmt->bindParam(":search", $search_term);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // UPDATE - Reset user password (admin function)
    public function resetPassword($user_id, $new_password)
    {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        $query = "UPDATE " . $this->table . " SET password = :password WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":password", $hashed_password);
        $stmt->bindParam(":id", $user_id);

        return $stmt->execute();
    }

    // READ - Get user statistics
    public function getUserStatistics()
    {
        $query = "SELECT 
                    COUNT(*) as total_users,
                    SUM(role = 'customer') as total_customers,
                    SUM(role = 'staff') as total_staff,
                    SUM(role = 'admin') as total_admins,
                    MAX(created_at) as latest_signup
                  FROM " . $this->table;

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
