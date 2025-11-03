<?php
session_start();
include("auth.php");

function protectPathAccess() {
    $auth = new Auth();
    
    if ($auth->is_logged_in()) {
        if ($_SESSION['role_id'] != 1) {
            header("Location: /v2/permission-denied");
            exit;
        }
    } else {
        header("Location: /v2/permission-denied");
        exit;
    }
}
?>
