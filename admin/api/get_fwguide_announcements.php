<?php

header('Content-Type: application/json');
$allowedOrigins = [
    'http://localhost:5174',
    'https://fwguide.online'
];
if (isset($_SERVER['HTTP_ORIGIN']) && in_array($_SERVER['HTTP_ORIGIN'], $allowedOrigins)) {
    header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN']);
}
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

include '../lib/db.php';
include '../lib/fwguide_announcement.php';

$fwguide = new FwguideAnnouncement();

try {
    $announcements = $fwguide->getAll();

    // Optional: prepend full URL for images
    $baseUrl = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://{$_SERVER['HTTP_HOST']}/v2/admin/uploads/";

    foreach ($announcements as &$item) {
        $item['image_pc'] = !empty($item['image_pc']) ? $baseUrl . $item['image_pc'] : null;
        $item['image_mb'] = !empty($item['image_mb']) ? $baseUrl . $item['image_mb'] : null;
    }

    echo json_encode([
        'success' => true,
        'data' => $announcements
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
