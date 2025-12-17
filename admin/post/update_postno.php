<?php
ini_set('display_errors', 0);   // hide notices in JSON response
ini_set('log_errors', 1);       // log errors to PHP log

require "../lib/checkroles.php";
require "../lib/post_lib.php";

protectRoute([1, 3]);

$id = (int)($_POST['id'] ?? 0);
$postNo = (int)($_POST['postNo'] ?? 0);

$postObj = new Post();

if ($postObj->postNoExists($postNo, $id)) {
    echo json_encode([
        'success' => false,
        'message' => 'Post number already exists'
    ]);
    exit;
}

$postObj->updatePostNo($id, $postNo);

echo json_encode(['success' => true]);
