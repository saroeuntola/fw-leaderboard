<?php
require_once 'services/ApiService.php';
require_once 'services/apiCache.php';

$cacheDir = $_SERVER['DOCUMENT_ROOT'] . '/crickets/services/cache';
$matchId = $_GET['id'] ?? null;
if (!$matchId) exit('Match ID missing');

// Detect if match is live or ended
$matchInfo = apiCache(
    "$cacheDir/matchInfo_$matchId.json",
    120,
    fn() => ApiService::getMatchInfo($matchId)
);

$isLive = !($matchInfo['data']['matchEnded'] ?? false);

// Cache TTL: 2 min for live, 10 min for ended
$ttl = $isLive ? 120 : 600;

// Fetch scorecard
$response = apiCache(
    "$cacheDir/scorecard_$matchId.json",
    $ttl,
    fn() => ApiService::getMatchInfo($matchId)
);

$data = $response['data'] ?? [];

// Teams info
$teamA = $data['teamInfo'][0] ?? [];
$teamB = $data['teamInfo'][1] ?? [];
?>

<?php foreach ($data['scorecard'] ?? [] as $inning): ?>
    <div class="bg-white dark:bg-[#1f1f1f] p-4 rounded-xl shadow mb-6">
        <h3 class="font-bold text-lg mb-2"><?= htmlspecialchars($inning['inning']) ?></h3>

        <!-- Team Score Summary -->
        <div class="mb-4">
            <p class="font-semibold">
                <?= htmlspecialchars($inning['batting_team'] ?? '') ?>:
                <?= $inning['r'] ?? 0 ?>/<?= $inning['w'] ?? 0 ?> (<?= $inning['o'] ?? 0 ?> ov)
            </p>
        </div>

        <!-- Batting Table -->
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
                    <?php foreach ($inning['batting'] ?? [] as $bat): ?>
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

        <!-- Bowling Table -->
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
                    <?php foreach ($inning['bowling'] ?? [] as $bow): ?>
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
    </div>
<?php endforeach; ?>