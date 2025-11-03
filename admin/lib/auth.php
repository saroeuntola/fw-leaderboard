<?php
include('db.php');
class Auth
{
    public $db;

    public function __construct()
    {
        $this->db = dbConn();
    }


    public function exists($field, $value)
    {
        $allowed = ['username', 'email', 'phone'];
        if (!in_array($field, $allowed)) return false;

        $stmt = $this->db->prepare("SELECT id FROM users WHERE $field = :value LIMIT 1");
        $stmt->execute(['value' => $value]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? true : false;
    }
    // Register

    public function createAccUser($username, $email, $phone, $password, $role_id)
    {
        // Check if exists
        if (
            $this->exists('username', $username) ||
            $this->exists('email', $email) ||
            $this->exists('phone', $phone)
        ) {
            throw new PDOException("Email, username, or phone already exists", 23000);
        }

        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        $stmt = $this->db->prepare("
        INSERT INTO users (username, email, phone, password, role_id) 
        VALUES (:username, :email, :phone, :password, :role_id)
    ");

        return $stmt->execute([
            'username' => $username,
            'email'    => $email,
            'phone'    => $phone,
            'password' => $hashed_password,
            'role_id'  => $role_id
        ]);
    }

    public function register($username, $email, $phone, $password, $role_id = 2)
    {
        // Check if exists
        if (
            $this->exists('username', $username) ||
            $this->exists('email', $email) ||
            $this->exists('phone', $phone)
        ) {
            throw new PDOException("Email, username, or phone already exists", 23000);
        }

        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        $stmt = $this->db->prepare("INSERT INTO users (username,email,phone,password,role_id) VALUES (:username,:email,:phone,:password,:role_id)");
        return $stmt->execute([
            'username' => $username,
            'email' => $email,
            'phone' => $phone,
            'password' => $hashed_password,
            'role_id' => $role_id
        ]);
    }

    // Login
    public function login($username, $password, $remember = false)
    {
        // Fetch user safely using a prepared query
        $results = dbSelect(
            'users',
            'id, username, password, role_id, status',
            'username = :username LIMIT 1',
            [':username' => $username]
        );

        if ($results && count($results) > 0) {
            $user = $results[0];

            // Check if user is inactive
            if ((int)$user['status'] === 0) {
                return "inactive"; // account disabled
            }

            // Verify password hash
            if (password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role_id'] = $user['role_id'];

                // Handle "Remember me" functionality
                if ($remember) {
                    $token = bin2hex(random_bytes(32));
                    $expiry = time() + (86400 * 30); // 30 days

                    dbInsert('user_tokens', [
                        'user_id' => $user['id'],
                        'token' => hash('sha256', $token),
                        'expires_at' => date('Y-m-d H:i:s', $expiry)
                    ]);

                    // Set secure, HttpOnly cookie
                    setcookie(
                        "remember_token",
                        $token,
                        [
                            'expires' => $expiry,
                            'path' => '/',
                            'secure' => isset($_SERVER['HTTPS']),
                            'httponly' => true,
                            'samesite' => 'Lax'
                        ]
                    );
                }

                return true;
            }
        }


        return false;
    }

    // Check if user is logged in
    public function is_logged_in()
    {
        if (isset($_SESSION['user_id'])) {
            return true;
        } elseif (isset($_COOKIE['remember_token'])) {
            $token = hash('sha256', $_COOKIE['remember_token']);
            $results = dbSelect('user_tokens', 'user_id', "token = " . $this->db->quote($token) . " AND expires_at > NOW() LIMIT 1");

            if ($results && count($results) > 0) {
                $user = $results[0];
                $_SESSION['user_id'] = $user['user_id'];

                // Fetch role_id
                $roleResults = dbSelect('users', 'role_id', "id = " . $this->db->quote($user['user_id']));
                if ($roleResults && count($roleResults) > 0) {
                    $_SESSION['role_id'] = $roleResults[0]['role_id'];
                }

                return true;
            }
        }
        return false;
    }
    // Logout 
    public function logout()
    {
        session_destroy();
        if (isset($_COOKIE['remember_token'])) {
            $token = hash('sha256', $_COOKIE['remember_token']);
            dbDelete('user_tokens', "token = " . $this->db->quote($token));
            setcookie("remember_token", "", time() - 3600, "/", "", true, true);
        }
    }

    // Check if the user has a specific role
    public function has_role($role_id)
    {
        return isset($_SESSION['role_id']) && $_SESSION['role_id'] == $role_id;
    }

    // Check if user has any of the roles
    public function has_any_role($role_ids = [])
    {
        return isset($_SESSION['role_id']) && in_array($_SESSION['role_id'], $role_ids);
    }
}
