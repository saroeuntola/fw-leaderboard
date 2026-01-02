<?php
require_once '../services/ApiService.php';

date_default_timezone_set('Asia/Dhaka');

$matchId = $_GET['id'] ?? null;
if (!$matchId) {
    echo json_encode(['error' => 'Match ID missing']);
    exit;
}

/* =====================
   SIMPLE FILE CACHE
===================== */
$cacheDir = __DIR__ . '/../cache';
if (!is_dir($cacheDir)) mkdir($cacheDir, 0755, true);

$cacheFile = "$cacheDir/live_$matchId.json";
$ttl = 55; // seconds (SAFE)

if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $ttl) {
    echo file_get_contents($cacheFile);
    exit;
}

/* =====================
   API CALL (ONLY HERE)
===================== */
$response = ApiService::getMatchInfo($matchId);
$data = $response['data'] ?? null;
if (!$data) {
    echo json_encode(['error' => 'No data']);
    exit;
}

/* =====================
   SCORE + PROBABILITY
===================== */
$inning1 = $data['scorecard'][0] ?? null;
$inning2 = $data['scorecard'][1] ?? null;

$teamA_prob = 50;
$teamB_prob = 50;

if ($inning1 && $inning2) {
    $runsA = $inning1['r'] ?? 0;
    $runsB = $inning2['r'] ?? 0;
    $wktsB = $inning2['w'] ?? 0;
    $oversB = $inning2['o'] ?? 0;

    $totalOvers = $inning2['max_overs'] ?? 50;
    $ballsLeft = ($totalOvers * 6) - ($oversB * 6);
    $target = $runsA + 1;
    $need = max($target - $runsB, 0);

    if ($need <= 0) {
        $teamB_prob = 99;
        $teamA_prob = 1;
    } else {
        $wicketFactor = (10 - $wktsB) / 10;
        $ballFactor = max($ballsLeft / ($totalOvers * 6), 0.1);
        $teamB_prob = max(5, min(95, round($wicketFactor * $ballFactor * 100)));
        $teamA_prob = 100 - $teamB_prob;
    }
}

$output = [
    'status' => $data['status'] ?? '',
    'score'  => $data['score'] ?? [],
    'prob'   => [
        'a' => $teamA_prob,
        'b' => $teamB_prob
    ],
    'live' => (!empty($data['matchStarted']) && empty($data['matchEnded']))
];

file_put_contents($cacheFile, json_encode($output));
echo json_encode($output);
