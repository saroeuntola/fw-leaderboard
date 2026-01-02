<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/crickets/services/ApiService.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/crickets/services/apiCache.php';

$cacheDir = $_SERVER['DOCUMENT_ROOT'] . '/crickets/cache';
$matchId = $_GET['id'] ?? null;
if (!$matchId) exit;

// ----------------------
// Fetch match info
// ----------------------
$matchInfo = apiCache(
    "$cacheDir/matchInfo_$matchId.json",
    120,
    fn() => ApiService::getMatchInfo($matchId)
);

$data = $matchInfo['data'] ?? [];
if (!$data) exit;

// Determine if live or ended
$isLive = !($data['matchEnded'] ?? false);
$ttl = $isLive ? 120 : 600;

// ----------------------
// Scorecard cache
// ----------------------
$scorecardResp = apiCache(
    "$cacheDir/scorecard_$matchId.json",
    $ttl,
    fn() => ApiService::getMatchInfoWthScoreCard($matchId)
);
$scoreData = $scorecardResp['data'] ?? [];

// ----------------------
// Teams & Scores
// ----------------------
$teamA = $data['teamInfo'][0] ?? [];
$teamB = $data['teamInfo'][1] ?? [];

$inning1 = $scoreData['score'][0] ?? null;
$inning2 = $scoreData['score'][1] ?? null;

$teamA_score = $inning1 ? ($inning1['r'] ?? 0) . "/" . ($inning1['w'] ?? 0) . " (" . ($inning1['o'] ?? 0) . " ov)" : "Yet to bat";
$teamB_score = $inning2 ? ($inning2['r'] ?? 0) . "/" . ($inning2['w'] ?? 0) . " (" . ($inning2['o'] ?? 0) . " ov)" : "Yet to bat";

// ----------------------
// Live win probability
// ----------------------
$teamA_prob = 50;
$teamB_prob = 50;

if (!empty($inning1) && !empty($inning2)) {
    $teamA_runs = $inning1['r'] ?? 0;
    $teamB_runs = $inning2['r'] ?? 0;
    $teamB_wickets = $inning2['w'] ?? 0;
    $teamB_overs = $inning2['o'] ?? 0;
    $total_overs = $inning2['max_overs'] ?? 20;
    $balls_remaining = ($total_overs * 6) - ($teamB_overs * 6);
    $target = $teamA_runs + 1;
    $runs_needed = max($target - $teamB_runs, 0);

    if ($runs_needed <= 0) {
        $teamB_prob = 99.99;
        $teamA_prob = 0.01;
    } else {
        $wicket_factor = (10 - $teamB_wickets) / 10;
        $ball_factor = $balls_remaining / ($total_overs * 6);
        $teamB_prob = max(0.01, min(99.99, $wicket_factor * $ball_factor * 100));
        $teamA_prob = 100 - $teamB_prob;
        $teamA_prob = round($teamA_prob, 2);
        $teamB_prob = round($teamB_prob, 2);
    }
} elseif (!empty($inning1)) {
    $teamA_prob = 60;
    $teamB_prob = 40;
}

// ----------------------
// Output HTML
// ----------------------
?>

<!-- Live Scores Header -->
<div class="flex justify-between mb-4">
    <div class="team-score-container">
        <div class="team-a-score font-semibold text-gray-800 dark:text-gray-200">
            <?= htmlspecialchars($teamA['name'] ?? 'Team A') ?> <?= $teamA_score ?>
        </div>
        <div class="team-b-score font-semibold text-gray-800 dark:text-gray-200">
            <?= htmlspecialchars($teamB['name'] ?? 'Team B') ?> <?= $teamB_score ?>
        </div>
    </div>
</div>

<!-- Probability Bar -->
<div class="live-prob-container mb-6 h-4 rounded-full overflow-hidden flex bg-gray-200 dark:bg-gray-700">
    <div class="bg-red-600 h-4" style="width: <?= $teamA_prob ?>%"></div>
    <div class="bg-blue-600 h-4" style="width: <?= $teamB_prob ?>%"></div>
</div>

<!-- Scorecard Innings -->
<?php foreach ($scoreData['innings'] ?? [] as $inning): ?>
    <div class="bg-white dark:bg-[#1f1f1f] p-4 rounded-xl shadow mb-6">
        <h3 class="font-bold text-lg mb-3"><?= htmlspecialchars($inning['inning']) ?></h3>

        <!-- Batting Table -->
        <?php if (!empty($inning['batting'])): ?>
            <div class="overflow-x-auto mb-4">
                <table class="w-full text-sm border border-gray-200 dark:border-gray-700">
                    <thead class="bg-gray-100 dark:bg-[#2a2a2a]">
                        <tr>
                            <th class="p-2 text-left">Batsman</th>
                            <th class="p-2 text-left">Dismissal</th>
                            <th class="p-2">R</th>
                            <th class="p-2">B</th>
                            <th class="p-2">4s</th>
                            <th class="p-2">6s</th>
                            <th class="p-2">SR</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($inning['batting'] as $bat): ?>
                            <tr class="border-t border-gray-200 dark:border-gray-700">
                                <td class="p-2 font-semibold"><?= htmlspecialchars($bat['batsman']['name'] ?? '-') ?></td>
                                <td class="p-2 text-left text-gray-600 dark:text-gray-400"><?= htmlspecialchars($bat['dismissal-text'] ?? '-') ?></td>
                                <td class="p-2 text-center"><?= $bat['r'] ?? 0 ?></td>
                                <td class="p-2 text-center"><?= $bat['b'] ?? 0 ?></td>
                                <td class="p-2 text-center"><?= $bat['4s'] ?? 0 ?></td>
                                <td class="p-2 text-center"><?= $bat['6s'] ?? 0 ?></td>
                                <td class="p-2 text-center"><?= $bat['sr'] ?? 0 ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <!-- Bowling Table -->
        <?php if (!empty($inning['bowling'])): ?>
            <div class="overflow-x-auto">
                <table class="w-full text-sm border border-gray-200 dark:border-gray-700">
                    <thead class="bg-gray-100 dark:bg-[#2a2a2a]">
                        <tr>
                            <th class="p-2 text-left">Bowler</th>
                            <th class="p-2">O</th>
                            <th class="p-2">M</th>
                            <th class="p-2">R</th>
                            <th class="p-2">W</th>
                            <th class="p-2">ECO</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($inning['bowling'] as $bow): ?>
                            <tr class="border-t border-gray-200 dark:border-gray-700">
                                <td class="p-2"><?= htmlspecialchars($bow['bowler']['name'] ?? '-') ?></td>
                                <td class="p-2 text-center"><?= $bow['o'] ?? 0 ?></td>
                                <td class="p-2 text-center"><?= $bow['m'] ?? 0 ?></td>
                                <td class="p-2 text-center"><?= $bow['r'] ?? 0 ?></td>
                                <td class="p-2 text-center"><?= $bow['w'] ?? 0 ?></td>
                                <td class="p-2 text-center"><?= $bow['eco'] ?? 0 ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
<?php endforeach; ?>