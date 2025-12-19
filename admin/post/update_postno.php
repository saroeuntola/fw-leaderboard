<?php
ini_set('display_errors', 0);   // hide notices in JSON response
ini_set('log_errors', 1);       // log errors to PHP log

require "../lib/checkroles.php";
require "../lib/post_lib.php";

protectRoute([1, 3]);

$post = new Post();

$id = (int)$_POST['id'];
$postNo = (int)$_POST['postNo'];

$exists = $post->findPostByPostNo($postNo, $id);

if ($exists) {
    echo json_encode([
        'exists' => true
    ]);
    exit;
}

// Normal update
$post->replacePostNo($id, $postNo);

echo json_encode(['success' => true]);
