<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
ob_start();
include "../lib/checkroles.php";
include '../lib/tiger_tourament_lib.php';
protectPathAccess();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ./');
    exit;
}
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
if ($id <= 0) {
    header('Location: index.php');
    exit;
}
$tournament = new TigerTouramentPost();
$record = $tournament->getTournamentById($id);
$deleted = $tournament->deleteTournament($id);

if ($deleted && $record && !empty($record['image'])) {
    $imageRelative = $record['image'];
    $imagePath = __DIR__ . '/../uploads/' . $imageRelative;

    // Only delete if file exists
    if (file_exists($imagePath)) {
        @unlink($imagePath);
    }
}

header('Location: ./');
exit;