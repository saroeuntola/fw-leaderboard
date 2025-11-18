<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
include('../lib/checkroles.php');
protectRoute([1]);
include('../lib/users_lib.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $userId = intval($_POST['user_id']);
    $newPassword = trim($_POST['new_password']);
    $confirmPassword = trim($_POST['confirm_password']);

    if ($newPassword !== $confirmPassword) {
        die("Passwords do not match.");
    }

    if (strlen($newPassword) < 6) {
        die("Password must be at least 6 characters.");
    }

    $user = new User();
    $user->changePassword($userId, $newPassword); // <--- pass raw password

    header("Location: ./");
    exit;
}
