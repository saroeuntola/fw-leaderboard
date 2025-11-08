<?php
header('Content-Type: application/json');

// Allow only specific origins
$allowedOrigins = [
    'http://localhost:5173',
    'http://localhost:5174',
    'http://fw-leaderboard:8080',
    'https://fwguide.online',
    'http://localhost:4173',
    'https://www.fwguide.online'

];

if (isset($_SERVER['HTTP_ORIGIN']) && in_array($_SERVER['HTTP_ORIGIN'], $allowedOrigins)) {
    header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN']);
}

header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-API-KEY");

// Preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

//API Key Authentication
$API_KEY = "ae0f6a0115944739adb9fb8d69853460a415978125ee477fabc98ff67bfc6203";

// Get all request headers safely
$headers = function_exists('getallheaders') ? getallheaders() : [];

$providedKey = $headers['X-API-KEY'] ?? $headers['x-api-key'] ?? null;

if ($providedKey !== $API_KEY) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized: Invalid or missing API key'
    ]);
    exit;
}

include '../lib/db.php';
include '../lib/fwguide_announcement.php';

$fwguide = new FwguideAnnouncement();

try {
    $announcements = $fwguide->getAll();

    // Build full image URLs
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
