<?php
session_start();
include("auth.php");

function protectRoute($allowedRoles = [])
{
    $auth = new Auth();

    // Not logged in
    if (!$auth->is_logged_in()) {
        header("Location: /v2/unauthorized");
        exit;
    }

    // User role undefined or not allowed
    if (!isset($_SESSION['role_id']) || !in_array($_SESSION['role_id'], $allowedRoles)) {
        header("Location: /v2/unauthorized");
        exit;
    }
}
