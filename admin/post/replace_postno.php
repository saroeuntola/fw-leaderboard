<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

require_once '../lib/checkroles.php';
require_once '../lib/post_lib.php';
require_once '../lib/users_lib.php';

// Allow only admin / editor
protectRoute([1, 3]);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
    exit;
}

$id     = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$postNo = isset($_POST['postNo']) ? (int)$_POST['postNo'] : null;

if ($id <= 0 || $postNo === null || $postNo < 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid input'
    ]);
    exit;
}

$post = new Post();

// Try replace logic (transaction inside lib)
$success = $post->replacePostNo($id, $postNo);

if ($success) {
    echo json_encode([
        'success' => true,
        'message' => 'Post order replaced successfully'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to replace post order'
    ]);
}
