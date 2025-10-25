<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
ob_start();

include "../lib/checkroles.php";
include '../lib/fwguide_announcement.php';

protectPathAccess();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ./');
    exit;
}
// Sanitize ID
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
if ($id <= 0) {
    header('Location: index.php');
    exit;
}
$fwguide = new FwguideAnnouncement();
// Fetch record before deletion
$record = $fwguide->getById($id);

if ($record) {
    // Delete DB record
    $deleted = $fwguide->delete($id);

    // Delete PC image if exists
    if (!empty($record['image_pc'])) {
        $pcPath = __DIR__ . '/../uploads/' . $record['image_pc'];
        if (file_exists($pcPath)) @unlink($pcPath);
    }

    // Delete Mobile image if exists
    if (!empty($record['image_mb'])) {
        $mbPath = __DIR__ . '/../uploads/' . $record['image_mb'];
        if (file_exists($mbPath)) @unlink($mbPath);
    }
}

// Redirect back to announcement list
header('Location: ./');
exit;
