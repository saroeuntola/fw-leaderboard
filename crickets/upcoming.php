<?php
require_once 'services/ApiService.php';

// =====================
// CACHE CONFIG
// =====================
$cacheFile = __DIR__ . '/cache/upcoming_matches.json';
$cacheTTL  = 3600; // 10 minutes

if (!is_dir(__DIR__ . '/cache')) {
    mkdir(__DIR__ . '/cache', 0755, true);
}

// =====================
// FETCH DATA WITH CACHE
// =====================
if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $cacheTTL) {
    $cachedData = file_get_contents($cacheFile);
    $response = json_decode($cachedData, true);
} else {
    $response = ApiService::getUpComingMatch();
    if (!empty($response['data'])) {
        file_put_contents($cacheFile, json_encode($response));
    }
}

$matches = $response['data'] ?? [];
if (!is_array($matches)) $matches = [];

// =====================
// FILTER MATCHES (TODAY + 7 DAYS) & SERIES LIST
// =====================
$filteredMatches = [];
$seriesList = [];
$now = new DateTime('now', new DateTimeZone('Asia/Dhaka'));
$maxDate = (clone $now)->modify('+7 days');

foreach ($matches as $m) {
    if (empty($m['dateTimeGMT'])) continue;

    $dt = new DateTime($m['dateTimeGMT'], new DateTimeZone('UTC'));
    $dt->setTimezone(new DateTimeZone('Asia/Phnom_Penh'));

    if ($dt >= $now && $dt <= $maxDate) {
        $filteredMatches[] = $m;

        if (!empty($m['series'])) {
            $seriesList[$m['series']] = true;
        }
    }
}

$seriesList = array_keys($seriesList);
sort($seriesList);
?>

<!DOCTYPE html>
<html lang="en" class="dark">

<head>
    <meta charset="UTF-8">
    <title>Upcoming Cricket Matches</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Hide scrollbar */
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }

        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>
</head>

<body class="bg-gray-100 dark:bg-[#121212] p-5">

    <!-- Navigation -->
    <div class="flex gap-6 mb-4 text-sm font-semibold text-gray-800 dark:text-gray-200">
        <a href="./finished-matches.php">Recently</a>
        <a href="./upcoming.php" class="text-red-600">Upcoming</a>
        <a href="./live-match.php">Live</a>
    </div>

    <!-- SERIES TABS -->
    <div class="flex gap-2 overflow-x-auto pb-3 mb-4 text-sm font-semibold">
        <button class="tab-btn px-4 py-2 bg-red-600 text-white rounded-full whitespace-nowrap" data-series="all">
            All
        </button>
        <?php foreach ($seriesList as $series): ?>
            <button class="tab-btn px-4 py-2 bg-white dark:bg-[#252525] text-gray-700 dark:text-gray-200 rounded-full shadow whitespace-nowrap"
                data-series="<?= htmlspecialchars($series) ?>">
                <?= htmlspecialchars($series) ?>
            </button>
        <?php endforeach; ?>
    </div>

    <!-- Horizontal Scroll with Arrows -->
    <div class="relative">
        <!-- Left Arrow (Desktop Only) -->
        <button onclick="scrollLeft()" class="hidden md:flex absolute left-0 top-1/2 -translate-y-1/2 z-10 px-3 py-2 rounded-lg bg-white dark:bg-[#252525] shadow hover:bg-gray-100 dark:hover:bg-[#333]">
            ◀
        </button>

        <!-- Cards Container -->
        <div id="matchContainer" class="flex gap-4 overflow-x-auto pb-4 scroll-smooth no-scrollbar">
            <?php foreach ($filteredMatches as $m): ?>
                <?php
                $matchId   = $m['id'] ?? '';
                $series    = htmlspecialchars($m['series'] ?? 'Unknown');
                $team1Name = htmlspecialchars($m['t1'] ?? 'Team 1');
                $team2Name = htmlspecialchars($m['t2'] ?? 'Team 2');
                $team1Img  = $m['t1img'] ?? '';
                $team2Img  = $m['t2img'] ?? '';

                $dt = new DateTime($m['dateTimeGMT'], new DateTimeZone('UTC'));
                $dt->setTimezone(new DateTimeZone('Asia/Phnom_Penh'));

                $matchDate = $dt->format('d M Y');
                $matchTime = $dt->format('g:i A');

                $diff = $now->diff($dt);
                if ($diff->invert == 1) {
                    $timeLabel = 'LIVE';
                } else {
                    $timeLabel = $diff->days === 0
                        ? ($diff->h > 0 ? 'Starts in ' . $diff->h . 'h ' . $diff->i . 'm' : 'Starts in ' . $diff->i . 'm')
                        : 'Starts ' . $dt->format('d M');
                }
                ?>
                <a href="match.php?id=<?= $matchId ?>" class="match-card min-w-[320px] max-w-[320px] bg-white dark:bg-[#252525] rounded-xl shadow hover:shadow-lg transition p-4"
                    data-series="<?= $series ?>">

                    <!-- Date & Time -->
                    <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-3 flex flex-wrap gap-1">
                        <span><?= $matchDate ?></span>
                        <span>•</span>
                        <span><?= $timeLabel ?></span>
                        <span>•</span>
                        <span><?= $matchTime ?></span>
                    </div>

                    <!-- Teams -->
                    <div class="space-y-3">
                        <div class="flex items-center gap-2">
                            <img src="<?= $team1Img ?>" class="w-8 h-8 rounded-full">
                            <span class="font-semibold text-sm text-gray-800 dark:text-gray-200"><?= $team1Name ?></span>
                        </div>
                        <div class="flex items-center gap-2">
                            <img src="<?= $team2Img ?>" class="w-8 h-8 rounded-full">
                            <span class="font-semibold text-sm text-gray-800 dark:text-gray-200"><?= $team2Name ?></span>
                        </div>
                    </div>

                    <!-- Series -->
                    <div class="text-sm text-gray-700 dark:text-gray-300 mt-3 line-clamp-2"><?= $series ?></div>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- Right Arrow (Desktop Only) -->
        <button onclick="scrollRight()" class="hidden md:flex absolute right-0 top-1/2 -translate-y-1/2 z-10 px-3 py-2 rounded-lg bg-white dark:bg-[#252525] shadow hover:bg-gray-100 dark:hover:bg-[#333]">
            ▶
        </button>
    </div>

    <!-- SCROLL SCRIPT -->
    <script>
        const container = document.getElementById('matchContainer');

        function scrollLeft() {
            container.scrollBy({
                left: -340,
                behavior: 'smooth'
            });
        }

        function scrollRight() {
            container.scrollBy({
                left: 340,
                behavior: 'smooth'
            });
        }

        // SERIES FILTER
        const tabs = document.querySelectorAll('.tab-btn');
        const cards = document.querySelectorAll('.match-card');
        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                const series = tab.dataset.series;
                tabs.forEach(t => {
                    t.classList.remove('bg-red-600', 'text-white');
                    t.classList.add('bg-white', 'dark:bg-[#252525]', 'text-gray-700', 'dark:text-gray-200');
                });
                tab.classList.add('bg-red-600', 'text-white');

                cards.forEach(card => {
                    card.style.display = (series === 'all' || card.dataset.series === series) ? 'block' : 'none';
                });
            });
        });
    </script>

</body>

</html>