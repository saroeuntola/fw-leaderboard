<?php
class User
{
    public $db;
    public function __construct()
    {
        $this->db = dbConn();
    }
    public function changePassword($userId, $newPassword)
    {
        if (empty($newPassword)) {
            return false;
        }

        // Hash the new password
        $hashed = password_hash($newPassword, PASSWORD_DEFAULT);

        try {
            $stmt = $this->db->prepare("
            UPDATE users 
            SET password = :password 
            WHERE id = :id
        ");

            return $stmt->execute([
                ':password' => $hashed,
                ':id'       => $userId
            ]);
        } catch (PDOException $e) {
            die("Error updating password: " . $e->getMessage());
        }
    }
    public function getUsers()
    {
        $query = "SELECT 
                u.id, 
                u.username, 
                u.email, 
                u.phone, 
                u.status, 
                u.created_at, 
                r.name AS role_name 
              FROM users u
              JOIN roles r ON u.role_id = r.id
              ORDER BY u.created_at DESC";

        try {
            $stmt = $this->db->query($query);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Error fetching users: " . $e->getMessage());
        }
    }
    public function toggleStatus($id)
    {
        $stmt = $this->db->prepare("UPDATE users SET status = 1 - status WHERE id = :id");
        $stmt->execute([':id' => $id]);

        // If user is now inactive, remove remember tokens
        $stmt = $this->db->prepare("DELETE FROM user_tokens WHERE user_id = :id");
        $stmt->execute([':id' => $id]);
    }


    // CREATE a new user
    public function createUser($data)
    {
        // Check for duplicate email
        $quotedEmail = $this->db->quote($data['email']);
        $existing = dbSelect('users', 'id', "email=$quotedEmail");

        if ($existing && count($existing) > 0) {
            return false; 
        }

        // Hash password before insert
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);

        return dbInsert('users', $data);
    }

   public function getRoles()
    {
        return dbSelect('roles', '*');
    }
    public function getUser($id)
    {
        $quotedId = $this->db->quote($id);
        $result = dbSelect('users', '*', "id=$quotedId");
        return ($result && count($result) > 0) ? $result[0] : null;
    }
 public function updateProfile($userId, $data) {
        // Assuming you have a database connection in your `db.php`
        global $db;

        $query = "UPDATE users SET username = ?, sex = ?, profile = ? WHERE id = ?";
        $stmt = $db->prepare($query);
        $stmt->bind_param("sssi", $data['username'], $data['sex'], $data['profile'], $userId);

        return $stmt->execute(); // Returns true if update is successful
    }
    // UPDATE a user (password optional)
    public function updateUser($id, $data)
    {
        $user = $this->getUser($id);
        if (!$user) {
            return false; // User not found
        }

        // If password is set, hash it
        if (isset($data['password']) && !empty($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        } else {
            unset($data['password']); // Don't update password if not provided
        }

        return dbUpdate('users', $data, "id=" . $this->db->quote($id));
    }
    public function getUserById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }



    // DELETE a user
    public function deleteUser($id)
    {
        $user = $this->getUser($id);
        if (!$user) {
            return false;
        }

        return dbDelete('users', "id=" . $this->db->quote($id));
    }
}
?>
