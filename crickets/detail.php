<?php
require_once 'services/ApiService.php';
require_once 'services/apiCache.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/admin/lib/db.php';

$matchId = $_GET['id'] ?? null;
if (!$matchId) die('Match ID missing');

$cacheDir = __DIR__ . '/cache';
if (!is_dir($cacheDir)) mkdir($cacheDir, 0755, true);

$hours = 8 * 60 * 60;

/**
 * Fetch match detail
 */
$response = apiCache(
    "$cacheDir/Details_$matchId.json",
    $hours,
    fn() => ApiService::getUpcomingInfo($matchId)
);

$data = $response['data'] ?? [];
if (!$data) die('No match data');

/**
 * Time convert UTC → Dhaka
 */
$dt = new DateTime($data['dateTimeGMT'], new DateTimeZone('UTC'));
$dt->setTimezone(new DateTimeZone('Asia/Dhaka'));
$matchDate = $dt->format('d M Y');
$matchTime = $dt->format('g:i A');

/**
 * Helpers
 */
$teamsInfo  = $data['teamInfo'] ?? [];
$scores = $data['score'] ?? [];
$seriesId = $data['series_id'] ?? '';
?>
<!DOCTYPE html>
<html lang="en" class="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($data['name']) ?></title>
    <link rel="stylesheet" href="/src/output.css?v=<?= time() ?>">
</head>

<body class="bg-gray-100 dark:bg-[#121212] text-gray-900 dark:text-gray-100">

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/navbar.php'; ?>

    <div class="max-w-6xl mx-auto px-4 py-6 mt-20">

        <!-- MATCH HEADER -->
        <div class="bg-white dark:bg-[#1f1f1f] rounded-xl shadow p-5 mb-6">

            <h1 class="text-xl font-bold mb-3">
                <?= htmlspecialchars($data['name']) ?>
            </h1>

            <div class="flex justify-between text-sm text-gray-400 mb-4">
                <span><?= htmlspecialchars($data['venue']) ?></span>
                <span><?= $matchDate ?> • <?= $matchTime ?></span>
            </div>

            <!-- TEAMS & SCORE -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                <?php foreach ($teamsInfo as $i => $team): ?>
                    <?php $score = $scores[$i] ?? null; ?>
                    <div class="flex items-center justify-between bg-gray-50 dark:bg-[#2a2a2a] p-4 rounded-lg">
                        <div class="flex items-center gap-3">
                            <img src="<?= htmlspecialchars($team['img'] ?? '') ?>" alt="<?= htmlspecialchars($team['name']) ?>" class="w-10 h-10 rounded-full">
                            <div>
                                <p class="font-semibold"><?= htmlspecialchars($team['name']) ?></p>
                                <?php if (($data['matchWinner'] ?? '') === $team['name']): ?>
                                    <span class="text-xs text-green-500 font-semibold">Winner</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="text-right">
                            <?php if ($score): ?>
                                <p class="font-bold text-lg">
                                    <?= $score['r'] ?? 0 ?>/<?= $score['w'] ?? 0 ?>
                                </p>
                                <p class="text-xs text-gray-400">
                                    <?= $score['o'] ?? 0 ?> overs
                                </p>
                            <?php else: ?>
                                <p class="text-sm text-gray-400">Yet to bat</p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>

            </div>

            <!-- STATUS -->
            <div class="mt-4 text-center text-sm font-semibold text-red-500">
                <?= htmlspecialchars($data['status'] ?? '') ?>
            </div>
        </div>

        <!-- TABS -->
        <div class="flex border-b border-gray-300 dark:border-gray-700 mb-4 overflow-x-auto">
            <button class="tab-btn px-4 py-2 font-semibold border-b-2 border-red-600" data-tab="overview">
                Overview
            </button>
            <button class="tab-btn px-4 py-2 font-semibold border-b-2 border-transparent" data-tab="standings">
                Standings
            </button>
            <button class="tab-btn px-4 py-2 font-semibold border-b-2 border-transparent" data-tab="squad">
                Squad
            </button>
        </div>

        <!-- TAB CONTENT -->
        <div id="tab-content" class="min-h-[40vh]">

            <!-- OVERVIEW -->
            <div id="overview" class="tab-panel">
                <div class="bg-white dark:bg-[#1f1f1f] rounded-xl shadow p-4 space-y-1">
                    <p><strong>Title:</strong> <?= htmlspecialchars($data['name']) ?></p>
                    <p><strong>Match Type:</strong> <?= htmlspecialchars($data['matchType']) ?></p>
                    <p><strong>Toss:</strong> <?= htmlspecialchars($data['tossWinner'] ?? 'N/A') ?>
                        <?= isset($data['tossChoice']) ? '(' . $data['tossChoice'] . ')' : '' ?>
                    </p>
                    <p><strong>Status:</strong> <?= htmlspecialchars($data['status'] ?? '') ?></p>
                    <p><strong>Venue:</strong> <?= htmlspecialchars($data['venue'] ?? '') ?></p>
                    <p><strong>Date:</strong> <?= $matchDate ?></p>
                    <p><strong>Time:</strong> <?= $matchTime ?></p>
                </div>
            </div>

            <!-- LAZY LOAD -->
            <div id="standings" class="tab-panel hidden" data-loaded="false"></div>
            <div id="squad" class="tab-panel hidden" data-loaded="false"></div>

        </div>
    </div>

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/footer.php'; ?>

    <!-- TAB SCRIPT -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {

            const tabs = document.querySelectorAll('.tab-btn');
            const panels = document.querySelectorAll('.tab-panel');
            const seriesId = <?= json_encode($seriesId) ?>;
            const matchId = <?= json_encode($matchId) ?>;

            function showTab(tab) {

                tabs.forEach(b => b.classList.remove('border-red-600'));
                document.querySelector(`[data-tab="${tab}"]`)
                    .classList.add('border-red-600');

                panels.forEach(p => p.classList.add('hidden'));
                const panel = document.getElementById(tab);
                panel.classList.remove('hidden');

                if (tab !== 'overview' && panel.dataset.loaded !== 'true') {

                    panel.innerHTML = `
                <div class="flex justify-center py-10">
                    <div class="w-8 h-8 border-4 border-red-600 border-t-transparent rounded-full animate-spin"></div>
                </div>
            `;

                    let url = '';
                    if (tab === 'standings') {
                        url = `/crickets/pages/match-standings?series_id=${seriesId}`;
                    }
                    if (tab === 'squad') {
                        url = `/crickets/pages/match-squad?id=${matchId}`;
                    }

                    fetch(url)
                        .then(r => r.text())
                        .then(html => {
                            panel.innerHTML = html;
                            panel.dataset.loaded = 'true';
                        })
                        .catch(() => {
                            panel.innerHTML = `<p class="text-center text-red-500">Failed to load</p>`;
                        });
                }
            }

            tabs.forEach(btn => {
                btn.addEventListener('click', () => showTab(btn.dataset.tab));
            });
        });
    </script>

</body>

</html>