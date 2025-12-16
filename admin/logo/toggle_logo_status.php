<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
include "../lib/checkroles.php";
include "../lib/users_lib.php";
include "../lib/brand_lib.php";
protectRoute([1, 3]);
$bannerModel = new Brand();

$id = (int)$_POST['id'];

if ($bannerModel->toggleBannerStatus($id)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}
