<?php
include('../lib/checkroles.php');
include('../lib/users_lib.php');

protectPathAccess();
$users = new User();
if (isset($_GET['id'])) {
    $userId = intval($_GET['id']);
    if ($users->deleteUser($userId)) {
        echo "<script>alert('deleted successfully!'); window.location.href='index.php';</script>";
    } else {
        echo "<script>alert('Error: Unable to delete !'); window.location.href='index.php';</script>";
    }
} else {
    echo "<script>alert('Invalid request!'); window.location.href='index.php';</script>";
}
?>