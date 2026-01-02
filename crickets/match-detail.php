<?php
require_once 'services/ApiService.php';
require_once 'services/apiCache.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/admin/lib/db.php';
$matchId = $_GET['id'] ?? null;
if (!$matchId) die('Match ID missing');

$cacheDir = __DIR__ . '/cache';

/**
 * Quick fetch to detect status
 */
$temp = apiCache(
    "$cacheDir/match_status_$matchId.json",
    60,
    fn() => ApiService::getMatchInfo($matchId)
);

$data = $temp['data'] ?? [];
if (!$data) die('No match data');

$matchStarted = !empty($data['matchStarted']);
$matchEnded   = !empty($data['matchEnded']);

if ($matchStarted && $matchEnded) {
    $ttl = 10 * 60 * 60; // ended
} elseif ($matchStarted && !$matchEnded) {
    $ttl = 120; // live
} else {
    $ttl = 3600; // upcoming
}

$matchResponse = apiCache(
    "$cacheDir/matchInfo_$matchId.json",
    $ttl,
    fn() => ApiService::getMatchInfo($matchId)
);
$data = $matchResponse['data'] ?? [];
$score     = $data['score'] ?? [];
$scorecards = $data['scorecard'] ?? [];
$teamA = $data['teamInfo'][0] ?? [];
$teamB = $data['teamInfo'][1] ?? [];
$seriesId = $data['series_id'] ?? null;
// ================= DATE =================
$dt = new DateTime($data['dateTimeGMT'], new DateTimeZone('UTC'));
$dt->setTimezone(new DateTimeZone('Asia/Dhaka'));
$matchDate = $dt->format('d M Y');
$matchTime = $dt->format('g:i A');

// ================= WIN PROBABILITY =================
function calcWinProb($data)
{
    $score = $data['score'] ?? [];
    $teamA_prob = 50;
    $teamB_prob = 50;
    $teamA = $data['teamInfo'][0]['name'] ?? '';
    $teamB = $data['teamInfo'][1]['name'] ?? '';
    $isEnded = $data['matchEnded'] ?? false;

    if ($isEnded && count($score) >= 2) {
        $scoreA = $score[0];
        $scoreB = $score[1];
        $winner = $data['matchWinner'] ?? '';

        if (($scoreA['r'] ?? 0) > ($scoreB['r'] ?? 0)) {
            $marginRuns = ($scoreA['r'] ?? 0) - ($scoreB['r'] ?? 0);
            $winnerProb = 55 + ($marginRuns / 2.5);
        } else {
            $wicketsLeft = 10 - ($scoreB['w'] ?? 0);
            $winnerProb = 55 + ($wicketsLeft * 2.5);
        }

        $winnerProb = max(55, min(75, round($winnerProb)));
        $loserProb  = 100 - $winnerProb;

        if ($winner === $teamA) {
            $teamA_prob = $winnerProb;
            $teamB_prob = $loserProb;
        } else {
            $teamA_prob = $loserProb;
            $teamB_prob = $winnerProb;
        }
    } elseif (count($score) === 2) {
        $first  = $score[0];
        $second = $score[1];
        $target = ($first['r'] ?? 0) + 1;
        $runs   = $second['r'] ?? 0;
        $wickets = $second['w'] ?? 0;
        $overs  = $second['o'] ?? 0;
        $maxOvers = $second['max_overs'] ?? 20;

        $ballsLeft  = max(1, ($maxOvers * 6) - ($overs * 6));
        $runsNeeded = max(0, $target - $runs);
        $rrr        = ($runsNeeded / $ballsLeft) * 6;

        $pressure = ($rrr / 10) + ($wickets / 10);
        $chasingProb = 100 - ($pressure * 40);
        $chasingProb = max(10, min(90, round($chasingProb)));

        $teamB_prob = $chasingProb;
        $teamA_prob = 100 - $teamB_prob;
    } elseif (count($score) === 1) {
        $runs  = $score[0]['r'] ?? 0;
        $overs = max(0.1, $score[0]['o'] ?? 0.1);
        $rr    = $runs / $overs;

        $teamA_prob = max(45, min(65, round(40 + ($rr * 2.5))));
        $teamB_prob = 100 - $teamA_prob;
    }

    return [$teamA_prob, $teamB_prob];
}

list($teamA_prob, $teamB_prob) = calcWinProb($data);

$isLive = !empty($data['matchStarted']) && empty($data['matchEnded']);
?>

<!DOCTYPE html>
<html lang="en" class="dark">

<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($data['name']) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="/src/output.css">
</head>

<body class="bg-gray-100 dark:bg-[#121212] text-white">
    <?php
    include $_SERVER['DOCUMENT_ROOT'] . '/navbar.php';
    ?>
    <main class="px-4 pb-10">
        <!-- ================= MATCH HEADER ================= -->
        <div class="relative max-w-6xl mx-auto px-4 bg-white dark:bg-[#1f1f1f] rounded-xl shadow py-5 mt-[90px] mb-6 ">
            <button class="mb-4 px-4 py-1 bg-amber-700 rounded-md " onclick="history.back()">
                Back
            </button>
            <!-- LIVE Label -->
            <?php if ($isLive): ?>
                <div class="absolute top-3 right-3 bg-red-600 text-white text-[11px] font-bold px-2 py-0.5 rounded-md flex items-center gap-1 animate-pulse">
                    <span class="w-1.5 h-1.5 bg-white rounded-full"></span>
                    LIVE
                </div>
            <?php endif; ?>


            <!-- Match Info -->
            <div class="text-sm text-sky-300 mb-3">
                <?= $matchDate ?> • <?= $matchTime ?> •
                <span class="text-white bg-red-500 p-1 rounded-md"><?= htmlspecialchars($data['matchType'] ?? '') ?></span>
            </div>
            <div class="text-sm text-green-300 mb-3"><?= htmlspecialchars($data['venue']) ?></div>

            <!-- Teams -->
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div class="text-center p-4 bg-gray-100 dark:bg-[#2a2a2a] rounded-xl">
                    <img src="<?= $teamA['img'] ?>" class="w-12 h-12 mx-auto mb-1">
                    <div class="font-semibold <?= $teamA_prob > $teamB_prob ? 'text-red-500' : '' ?>"><?= $teamA['name'] ?></div>
                    <div class="text-lg font-bold"><?= isset($score[0]) ? "{$score[0]['r']}/{$score[0]['w']} ({$score[0]['o']} ov)" : 'Yet to bat' ?></div>
                </div>
                <div class="text-center p-4 bg-gray-100 dark:bg-[#2a2a2a] rounded-xl">
                    <img src="<?= $teamB['img'] ?>" class="w-12 h-12 mx-auto mb-1">
                    <div class="font-semibold <?= $teamB_prob > $teamA_prob ? 'text-red-500' : '' ?>"><?= $teamB['name'] ?></div>
                    <div class="text-lg font-bold"><?= isset($score[1]) ? "{$score[1]['r']}/{$score[1]['w']} ({$score[1]['o']} ov)" : 'Yet to bat' ?></div>
                </div>
            </div>

            <!-- Win Probability -->
            <div class="mt-4">
                <div class="text-sm text-red-600 mb-3 text-center"><?= htmlspecialchars($data['status']) ?></div>
                <h2 class="text-center mb-2 text-yellow-500">LIVE WIN PROBABILITY</h2>
                <div class="flex h-2 rounded-full overflow-hidden">
                    <div class="bg-green-600" style="width:<?= $teamA_prob ?>%"></div>
                    <div class="bg-red-600" style="width:<?= $teamB_prob ?>%"></div>
                </div>
                <div class="flex justify-between text-xs mt-1">
                    <span><?= $teamA['shortname'] ?> <?= $teamA_prob ?>%</span>
                    <span><?= $teamB['shortname'] ?> <?= $teamB_prob ?>%</span>
                </div>
            </div>
        </div>

        <!-- ================= TABS ================= -->
        <div class="max-w-6xl mx-auto">
            <div class="flex border-b border-gray-300 dark:border-gray-700 mb-4 overflow-x-auto">
                <button class="tab-btn px-4 py-2 font-semibold border-b-2 border-transparent flex-shrink-0" data-tab="live">Scorecard</button>
                <button class="tab-btn px-4 py-2 font-semibold border-b-2 border-transparent flex-shrink-0" data-tab="standings">Standings</button>
                <button class="tab-btn px-4 py-2 font-semibold border-b-2 border-transparent flex-shrink-0" data-tab="squad">Squad</button>
                <!-- <button class="tab-btn px-4 py-2 font-semibold border-b-2 border-transparent flex-shrink-0" data-tab="match-points">Match Points</button> -->

            </div>

            <div id="tab-content">
                <!-- SCORECARD -->
                <div id="live" class="tab-panel">
                    <?php foreach ($scorecards as $inning): ?>
                        <div class="bg-white dark:bg-[#1f1f1f] rounded-xl shadow mb-6" data-inning="<?= htmlspecialchars($inning['inning']) ?>">
                            <div class="px-4 py-3 border-b text-fuchsia-400 font-semibold text-lg"><?= $inning['inning'] ?></div>

                            <!-- Batting Table -->
                            <table class="w-full text-sm overflow-x-auto">
                                <thead class="bg-gray-100 dark:bg-[#2a2a2a]">
                                    <tr>
                                        <th class="text-left p-2">Batter</th>
                                        <th>Dismissal</th>
                                        <th>R</th>
                                        <th>B</th>
                                        <th>4s</th>
                                        <th>6s</th>
                                        <th>SR</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($inning['batting'] ?? [] as $bat): ?>
                                        <tr class="border-t">
                                            <td class="p-2 font-medium"><?= $bat['batsman']['name'] ?></td>
                                            <td class="text-center"><?= $bat['dismissal-text'] ?? 'not out' ?></td>
                                            <td class="text-center"><?= $bat['r'] ?></td>
                                            <td class="text-center"><?= $bat['b'] ?></td>
                                            <td class="text-center"><?= $bat['4s'] ?></td>
                                            <td class="text-center"><?= $bat['6s'] ?></td>
                                            <td class="text-center"><?= $bat['sr'] ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>

                            <!-- Extras / Total -->
                            <div class="mt-2 px-2 text-sm">
                                <?php if (isset($inning['extras'])): ?>
                                    <div>Extras: <?= $inning['extras']['total'] ?? 0 ?> (b <?= $inning['extras']['b'] ?? 0 ?>, lb <?= $inning['extras']['lb'] ?? 0 ?>, w <?= $inning['extras']['w'] ?? 0 ?>, nb <?= $inning['extras']['nb'] ?? 0 ?>)</div>
                                <?php endif; ?>
                                <?php if (isset($inning['total'])): ?>
                                    <div class="font-semibold">Total: <?= $inning['total']['r'] ?? 0 ?>/<?= $inning['total']['w'] ?? 0 ?> (<?= $inning['total']['o'] ?? 0 ?> ov)</div>
                                <?php endif; ?>
                            </div>

                            <!-- Fall of Wickets -->
                            <?php if (!empty($inning['fow'])): ?>
                                <div class="mt-2 px-2 text-sm">
                                    <strong>Fall of Wickets:</strong>
                                    <?= implode(', ', array_map(fn($f) => "{$f['batsman']} {$f['score']}({$f['over']})", $inning['fow'])) ?>
                                </div>
                            <?php endif; ?>

                            <!-- Bowling Table -->
                            <table class="w-full text-sm mt-4">
                                <thead class="bg-gray-100 dark:bg-[#2a2a2a]">
                                    <tr>
                                        <th class="text-left p-2">Bowler</th>
                                        <th>O</th>
                                        <th>M</th>
                                        <th>R</th>
                                        <th>W</th>
                                        <th>NB</th>
                                        <th>WD</th>
                                        <th>ECO</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($inning['bowling'] ?? [] as $bowl): ?>
                                        <tr class="border-t">
                                            <td class="p-2"><?= $bowl['bowler']['name'] ?></td>
                                            <td class="text-center"><?= $bowl['o'] ?></td>
                                            <td class="text-center"><?= $bowl['m'] ?></td>
                                            <td class="text-center"><?= $bowl['r'] ?></td>
                                            <td class="text-center"><?= $bowl['w'] ?></td>
                                            <td class="text-center"><?= $bowl['nb'] ?></td>
                                            <td class="text-center"><?= $bowl['wd'] ?></td>
                                            <td class="text-center"><?= $bowl['eco'] ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endforeach; ?>

                    <!-- match info -->

                    <div class="max-w-6xl mx-auto bg-white dark:bg-[#1f1f1f] rounded-xl shadow p-5 mb-6">
                        <h1 class="text-2xl font-bold mb-4 border-b-2 border-gray-500">Match Detail</h1>
                        <h2 class="text-xl font-bold mb-2"><?= htmlspecialchars($data['name']) ?></h2>
                        <div class="text-sm text-sky-300 mb-3">Match Date: <?= $matchDate ?> • <?= $matchTime ?></div>
                        <div class="text-white mb-3">Match Type: <?= htmlspecialchars($data['matchType'] ?? '') ?></div>
                        <div class="text-sm text-green-300 mb-3">Venue: <?= htmlspecialchars($data['venue']) ?></div>
                        <div class="text-sm text-pink-300 mb-3">Toss: <?= htmlspecialchars($data['tossWinner'] ?? 'N/A') ?></div>
                        <div class="text-sm text-red-600 mb-3">Match Won: <?= htmlspecialchars($data['matchWinner'] ?? 'N/A') ?></div>

                    </div>

                </div>

                <!-- EMPTY PANELS FOR LAZY LOAD -->
                <div id="standings" class="tab-panel hidden" data-loaded="false"></div>
                <div id="match-points" class="tab-panel hidden" data-loaded="false"></div>
                <div id="squad" class="tab-panel hidden" data-loaded="false"></div>
            </div>
        </div>
    </main>

    <?php
    include $_SERVER['DOCUMENT_ROOT'] . '/footer.php';
    ?>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/scroll-to-top.php';?>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            console.log("Tab script loaded");

            const tabs = document.querySelectorAll('.tab-btn');
            const panels = document.querySelectorAll('.tab-panel');
            const matchId = <?= json_encode($matchId ?? '') ?>;
            const seriesId = <?= json_encode($seriesId ?? '') ?>;

            function showTab(tabName) {
                console.log("showTab called for:", tabName);

                // Highlight active tab
                tabs.forEach(b => b.classList.remove('border-red-600'));
                const activeBtn = document.querySelector(`.tab-btn[data-tab="${tabName}"]`);
                if (activeBtn) activeBtn.classList.add('border-red-600');

                // Hide all panels
                panels.forEach(p => p.classList.add('hidden'));

                // Show the selected panel
                const panel = document.getElementById(tabName);
                if (!panel) return;
                panel.classList.remove('hidden');

                // Lazy load other tabs
                if (tabName !== 'live' && (!panel.dataset.loaded || panel.dataset.loaded === "false")) {
                    panel.innerHTML = `
                <div class="flex justify-center items-center py-10">
                    <div class="w-8 h-8 border-4 border-t-red-600 border-gray-300 rounded-full animate-spin"></div>
                </div>
            `;

                    let url;
                    if (tabName === 'standings') {
                        url = `/crickets/pages/match-standings?series_id=${seriesId}`;
                    } else if (tabName === 'match-points') {
                        url = `/crickets/pages/match-match-points?id=${matchId}`;
                    } else if (tabName === 'squad') {
                        url = `/crickets/pages/match-squad?id=${matchId}`;
                    }

                    console.log("Fetching URL:", url);

                    fetch(url)
                        .then(res => res.text())
                        .then(html => {
                            panel.innerHTML = html;
                            panel.dataset.loaded = "true";
                        })
                        .catch(err => {
                            console.error("Fetch error:", err);
                            panel.innerHTML = `<div class="text-red-500 text-center py-10">
                        Failed to load. <button class="underline" onclick="showTab('${tabName}')">Retry</button>
                    </div>`;
                        });
                }

                // Save last active tab in localStorage
                localStorage.setItem('activeTab', tabName);
            }

            // **Default to 'live' tab (Scorecard) only on first load**
            const lastTab = localStorage.getItem('activeTab');
            if (lastTab) {
                showTab(lastTab); // restore previous tab
            } else {
                showTab('live'); // first visit -> Scorecard
            }

            // Click events
            tabs.forEach(btn => {
                btn.addEventListener('click', () => {
                    console.log("Clicked tab:", btn.dataset.tab);
                    showTab(btn.dataset.tab);
                });
            });
        });
    </script>
</body>

</html>