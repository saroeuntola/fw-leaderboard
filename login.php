<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) session_start();
include('./admin/lib/auth.php');
$auth = new Auth();
$error_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $remember = isset($_POST['remember']);
    $loginResult = $auth->login($username, $password, $remember);
    if ($loginResult === true) {
        $result = dbSelect(
            'users',
            'role_id, status',
            'username = :username',
            [':username' => $username]
        );

        if ($result && count($result) > 0) {
            $user = $result[0];

            // Check if blocked
            if ((int)$user['status'] === 0) {
                $error_message = 'Your account is blocked. Please contact admin.';
                $auth->logout();
            } else {
                // Redirect based on role
                if ($user['role_id'] == 1) {
                    header('Location: /v2/admin');
                } elseif ($user['role_id'] == 2) {
                    header('Location: /v2');
                }
                exit();
            }
        }
    } elseif ($loginResult === "inactive") {
        $error_message = "Your account is inactive. Please contact support.";
    } else {
        $error_message = "Invalid username or password!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title> 
    <link rel="stylesheet" href="./src/output.css">
    <link href="https://cdn.jsdelivr.net/npm/daisyui@5" rel="stylesheet" type="text/css"/>
</head>

<body class="bg-gray-900 flex items-center justify-center min-h-screen">
    <div class="w-full max-w-md p-6 bg-gray-800 bg-opacity-70 rounded-2xl shadow-lg">
        <div class="text-center mb-6">
            <h1 class="text-3xl font-bold text-red-700 mb-2">Login</h1>
        </div>

        <?php if (!empty($error_message)): ?>
            <div class="alert alert-error mb-4">
                <span><?= htmlspecialchars($error_message) ?></span>
            </div>
        <?php endif; ?>

        <form action="login" method="POST" class="space-y-4">
            <div class="form-control">
                <input type="text" id="username" name="username" placeholder="Username"
                    class="input input-bordered w-full bg-gray-700 text-white p-2" required>
            </div>

            <div class="form-control">
                <input type="password" id="password" name="password" placeholder="Password"
                    class="input input-bordered w-full bg-gray-700 text-white p-2" required>
            </div>

            <div class="flex items-center justify-between">
                <label class="cursor-pointer label">
                    <input type="checkbox" id="remember" name="remember" class="checkbox checkbox-sm bg-white">
                    <span class="label-text text-gray-200 text-sm">Remember me</span>
                </label>
            </div>

            <button type="submit" class="btn bg-red-700 w-full text-white">Login</button>
        </form>
    </div>

</body>

</html>