<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

include '../lib/checkroles.php';
include '../lib/post_lib.php';

protectRoute([1, 3]);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid method']);
    exit;
}

if (!isset($_POST['postNo'])) {
    echo json_encode(['success' => false, 'message' => 'postNo missing']);
    exit;
}

$postNo = (int)$_POST['postNo'];

$post = new Post();

$result = $post->replacePostNoForCreate($postNo);

if ($result) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'DB replace failed']);
}
