<?php
require_once 'services/ApiService.php';
require_once 'services/apiCache.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/admin/lib/db.php';

$matchId = $_GET['id'] ?? null;
if (!$matchId) die('Match ID missing');

$cacheDir = __DIR__ . '/cache';
if (!is_dir($cacheDir)) mkdir($cacheDir, 0755, true);

/**
 * Fetch upcoming match info
 */
$response = apiCache(
    "$cacheDir/upcoming_$matchId.json",
    3600,
    fn() => ApiService::getUpcomingInfo($matchId)
);

$data = $response['data'] ?? [];
if (!$data) die('No data');

/**
 * Date convert UTC → Dhaka
 */
$dt = new DateTime($data['dateTimeGMT'], new DateTimeZone('UTC'));
$dt->setTimezone(new DateTimeZone('Asia/Dhaka'));

$matchDate = $dt->format('d M Y');
$matchTime = $dt->format('g:i A');
$matchTimestamp = $dt->getTimestamp() * 1000;

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
        <!-- MATCH INFO CARD -->
        <div class="bg-white dark:bg-[#1f1f1f] rounded-xl shadow p-5 mb-6">
            <h1 class="text-xl font-bold mb-2"><?= htmlspecialchars($data['name']) ?></h1>

            <div class="text-sm text-gray-500 mb-2">
                <?= htmlspecialchars($data['venue']) ?>
            </div>

            <div class="text-sm text-sky-500 mb-3">
                <?= $matchDate ?> • <?= $matchTime ?> (Dhaka)
            </div>

            <!-- COUNTDOWN -->
            <div class="text-center mt-4">
                <p class="text-xs text-gray-400 mb-1">Match starts in</p>
                <div id="countdown"
                    class="inline-flex gap-3 px-4 py-2 bg-red-50 text-red-600 rounded-lg font-semibold text-sm">
                    --
                </div>
            </div>
        </div>

        <!-- TABS -->
        <div class="flex border-b border-gray-300 dark:border-gray-700 mb-4 overflow-x-auto">
            <button class="tab-btn px-4 py-2 font-semibold border-b-2"
                data-tab="overview">
                Overview
            </button>

            <button class="tab-btn px-4 py-2 font-semibold border-b-2 border-transparent"
                data-tab="standings">
                Standings
            </button>

            <button class="tab-btn px-4 py-2 font-semibold border-b-2 border-transparent"
                data-tab="squad">
                Squad
            </button>
        </div>

        <!-- TAB CONTENT -->
        <div id="tab-content" class="min-h-[40vh]">

            <!-- OVERVIEW -->
            <div id="overview" class="tab-panel">
                <div class="bg-white dark:bg-[#1f1f1f] rounded-xl shadow p-4">
                    <p><strong>Match Type:</strong> <?= htmlspecialchars($data['matchType']) ?></p>
                    <p><strong>Toss:</strong> <?= htmlspecialchars($data['tossWinner'] ?? 'N/A') ?></p>
                    <p><strong>Status:</strong> <?= htmlspecialchars($data['status'] ?? 'Upcoming') ?></p>
                </div>
            </div>

            <!-- LAZY PANELS -->
            <div id="standings" class="tab-panel hidden" data-loaded="false"></div>
            <div id="squad" class="tab-panel hidden" data-loaded="false"></div>

        </div>
    </div>


    <?php include $_SERVER['DOCUMENT_ROOT'] . '/footer.php' ?>
    <!-- COUNTDOWN SCRIPT -->
    <script>
        const matchTime = <?= json_encode($matchTimestamp) ?>;
        const countdownEl = document.getElementById('countdown');

        function updateCountdown() {
            const now = new Date().getTime();
            const diff = matchTime - now;

            if (diff <= 0) {
                countdownEl.innerHTML = '<span class="text-green-600">Match Started</span>';
                clearInterval(timer);
                return;
            }

            const d = Math.floor(diff / (1000 * 60 * 60 * 24));
            const h = Math.floor((diff / (1000 * 60 * 60)) % 24);
            const m = Math.floor((diff / (1000 * 60)) % 60);
            const s = Math.floor((diff / 1000) % 60);

            countdownEl.innerHTML = `<span>${d}d</span><span>${h}h</span><span>${m}m</span><span>${s}s</span>`;
        }

        updateCountdown();
        const timer = setInterval(updateCountdown, 1000);
    </script>

    <!-- TAB FETCH SCRIPT -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {

            const tabs = document.querySelectorAll('.tab-btn');
            const panels = document.querySelectorAll('.tab-panel');
            const seriesId = <?= json_encode($seriesId) ?>;
            const matchId = <?= json_encode($matchId) ?>;

            function showTab(tabName) {
                console.log('Tab clicked:', tabName);

                tabs.forEach(btn => btn.classList.remove('border-red-600'));
                document.querySelector(`[data-tab="${tabName}"]`)
                    .classList.add('border-red-600');

                panels.forEach(p => p.classList.add('hidden'));
                const panel = document.getElementById(tabName);
                panel.classList.remove('hidden');

                if (tabName !== 'overview' && panel.dataset.loaded !== 'true') {
                    panel.innerHTML = `
                <div class="flex justify-center py-10">
                    <div class="w-8 h-8 border-4 border-red-600 border-t-transparent rounded-full animate-spin"></div>
                </div>
            `;

                    let url = '';
                    if (tabName === 'standings') {
                        url = `/crickets/pages/match-standings?series_id=${seriesId}`;
                    }
                    if (tabName === 'squad') {
                        url = `/crickets/pages/match-squad?id=${matchId}`;
                    }

                    console.log('Fetching:', url);

                    fetch(url)
                        .then(res => res.text())
                        .then(html => {
                            panel.innerHTML = html;
                            panel.dataset.loaded = 'true';
                        })
                        .catch(err => {
                            console.error(err);
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