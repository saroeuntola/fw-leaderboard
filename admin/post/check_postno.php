<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);// log errors to PHP log

require "../lib/checkroles.php";
require "../lib/post_lib.php";

protectRoute([1, 3]);
$postNo = (int)($_POST['postNo'] ?? 0);
$id = (int)($_POST['id'] ?? 0);

$postObj = new Post();
$exists = $postObj->postNoExists($postNo, $id);

echo json_encode(['exists' => $exists]);
