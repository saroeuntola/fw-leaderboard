<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
include "../lib/checkroles.php";
include "../lib/users_lib.php";
include "../lib/post_lib.php";
protectRoute([1, 3]);
$postModel = new Post();

$id = (int)$_POST['id'];

if ($postModel->togglePostStatus($id)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}
