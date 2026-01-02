<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/crickets/services/ApiService.php';
require_once  $_SERVER['DOCUMENT_ROOT'] . '/crickets/services/apiCache.php';

date_default_timezone_set('Asia/Dhaka');
$now = new DateTime('now', new DateTimeZone('Asia/Dhaka'));

$cacheDir = $_SERVER['DOCUMENT_ROOT'] . '/crickets/services/cache';
// =====================
// FETCH MATCHES
// =====================
$upcomingMatches = apiCache(
    "$cacheDir/upcoming.json",
    3600, // 1 hour
    function () {
        $resp = ApiService::getUpComingMatch();
        return $resp['data'] ?? [];
    }
);


$upcomingMatches = array_filter($upcomingMatches, function ($m) use ($now) {
    $dt = new DateTime($m['dateTimeGMT'], new DateTimeZone('UTC'));
    $dt->setTimezone(new DateTimeZone('Asia/Dhaka'));
    return $dt >= $now;
});

// Sort upcoming matches by datetime ASC
usort($upcomingMatches, fn($a, $b) => strtotime($a['dateTimeGMT']) <=> strtotime($b['dateTimeGMT']));


?>

<script src="https://cdn.tailwindcss.com"></script>
<style>
    .no-scrollbar::-webkit-scrollbar {
        display: none;
    }

    .no-scrollbar {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }

    .scroll-arrow {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        background: rgba(0, 0, 0, 0.3);
        padding: 0.5rem;
        border-radius: 50%;
        cursor: pointer;
        display: none;
        z-index: 10;
    }

    .scroll-arrow svg {
        width: 20px;
        height: 20px;
        stroke: white;
    }

    .group:hover .scroll-arrow {
        display: block;
    }

    .scroll-arrow.left {
        left: 0.5rem;
    }

    .scroll-arrow.right {
        right: 0.5rem;
    }
</style>
<div class="w-full mb-4">
    <h1 class="inline-block bg-yellow-600 text-white px-3 py-1 
           lg:text-xl text-lg font-bold">
        Live Crikket Scores
    </h1>
    <div class="h-[2px] bg-yellow-600"></div>
</div>
<!-- Tabs -->
<div class="flex gap-2 mb-5">
    <button class="tab-btn px-4 py-2 rounded-full bg-red-600 text-white font-semibold" data-tab="upcoming">Upcoming</button>
    <button class="tab-btn px-4 py-2 rounded-full bg-white dark:bg-[#252525] text-gray-700 dark:text-gray-200 font-semibold" data-tab="live">Live</button>
</div>

<?php
function renderMatchCard($m, $isUpcoming = true)
{
    $matchId   = $m['id'];
    $title     = htmlspecialchars($m['series'] ?? $m['name'] ?? 'Unknown Series');
    $venue     = htmlspecialchars($m['venue'] ?? '');
    $dtUTC     = new DateTime($m['dateTimeGMT'], new DateTimeZone('UTC'));
    $dtUTC->setTimezone(new DateTimeZone('Asia/Dhaka'));
    $matchDate = $dtUTC->format('d M Y');
    $matchTime = $dtUTC->format('g:i A');

    $isLive = !empty($m['matchStarted']) && empty($m['matchEnded']);
    $timeLabel = $isLive ? 'LIVE' : (!empty($m['matchEnded']) ? 'FINAL' : 'Starts ' . $matchDate);

    if ($isUpcoming) {
        $team1Img = $m['t1img'] ?? '';
        $team2Img = $m['t2img'] ?? '';
        $team1Name = htmlspecialchars($m['t1'] ?? '');
        $team2Name = htmlspecialchars($m['t2'] ?? '');
    } else {
        $team1 = $m['teamInfo'][0] ?? [];
        $team2 = $m['teamInfo'][1] ?? [];
        $team1Img = $team1['img'] ?? '';
        $team2Img = $team2['img'] ?? '';
        $team1Name = htmlspecialchars($team1['name'] ?? '');
        $team2Name = htmlspecialchars($team2['name'] ?? '');
    }

    $score1 = $m['score'][0] ?? null;
    $score2 = $m['score'][1] ?? null;
    $status = htmlspecialchars($m['status'] ?? '');
?>
    <a href="/crickets/match-detail?id=<?= urlencode($matchId) ?>" class="snap-start flex-shrink-0 min-w-[320px] max-w-[320px] bg-white dark:bg-[#252525] rounded-xl shadow hover:shadow-lg transition p-4">
        <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-3 flex flex-wrap gap-2">
            <span><?= $matchDate ?></span> •
            <?php if ($isLive): ?>
                <span class="flex items-center gap-1 text-red-600 font-bold">
                    <span class="w-2 h-2 bg-red-600 rounded-full animate-pulse"></span> LIVE
                </span>
            <?php else: ?>
                <span><?= $timeLabel ?></span>
            <?php endif; ?>
            • <span><?= $matchTime ?> Local</span>
        </div>

        <div class="text-xs text-gray-400 mb-3"><?= $venue ?></div>

        <div class="space-y-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <img src="<?= $team1Img ?>" class="w-8 h-8 rounded-full">
                    <span class="font-semibold text-sm text-gray-800 dark:text-gray-200"><?= $team1Name ?></span>
                </div>
                <?php if ($score1): ?><span class="text-sm font-bold"><?= $score1['r'] ?>/<?= $score1['w'] ?></span><?php endif; ?>
            </div>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <img src="<?= $team2Img ?>" class="w-8 h-8 rounded-full">
                    <span class="font-semibold text-sm text-gray-800 dark:text-gray-200"><?= $team2Name ?></span>
                </div>
                <?php if ($score2): ?><span class="text-sm font-bold"><?= $score2['r'] ?>/<?= $score2['w'] ?></span><?php endif; ?>
            </div>
        </div>

        <div class="text-sm text-gray-700 dark:text-gray-300 mt-3 line-clamp-2"><?= $title ?></div>
        <?php if (!$isLive && !empty($status)): ?>
            <div class="mt-2 text-xs font-semibold text-red-600"><?= $status ?></div>
        <?php endif; ?>
    </a>
<?php } ?>

<!-- Upcoming -->
<div class="tab-content flex gap-4 overflow-x-auto scroll-smooth snap-x snap-mandatory pb-4 no-scrollbar relative group" data-tab="upcoming">
    <div class="scroll-arrow left" data-scroll-left>
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
    </div>
    <div class="scroll-arrow right" data-scroll-right>
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
    </div>
    <?php foreach ($upcomingMatches as $m) renderMatchCard($m, true); ?>
</div>

<!-- Live -->
<div class="tab-content flex gap-4 overflow-x-auto scroll-smooth snap-x snap-mandatory pb-4 no-scrollbar relative group hidden" data-tab="live">
    <div class="scroll-arrow left" data-scroll-left>
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
    </div>
    <div class="scroll-arrow right" data-scroll-right>
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
    </div>
</div>

<script>
    const tabBtns = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');
    const TAB_KEY = "active_tab";

    // Activate a tab
    function activateTab(tab) {
        tabContents.forEach(c => c.classList.toggle('hidden', c.dataset.tab !== tab));
        tabBtns.forEach(b => {
            b.classList.remove('bg-red-600', 'text-white');
            b.classList.add('bg-white', 'dark:bg-[#252525]', 'text-gray-700', 'dark:text-gray-200');
        });
        const activeBtn = document.querySelector(`.tab-btn[data-tab="${tab}"]`);
        if (activeBtn) activeBtn.classList.add('bg-red-600', 'text-white');

        localStorage.setItem(TAB_KEY, tab);
    }

    // Add scroll arrow functionality
    function initScrollArrows(container) {
        container.querySelectorAll('[data-scroll-left]').forEach(btn => {
            btn.addEventListener('click', () => {
                container.querySelector('div.flex').scrollBy({
                    left: -300,
                    behavior: 'smooth'
                });
            });
        });
        container.querySelectorAll('[data-scroll-right]').forEach(btn => {
            btn.addEventListener('click', () => {
                container.querySelector('div.flex').scrollBy({
                    left: 300,
                    behavior: 'smooth'
                });
            });
        });
    }

    // Fetch Live matches dynamically (background update)
    async function fetchLiveMatches(background = false) {
        const liveContainer = document.querySelector('.tab-content[data-tab="live"]');
        if (!liveContainer) return;

        if (!background) {
            // Only show loading on manual click
            liveContainer.innerHTML = `<div class="text-center py-6 text-gray-500 dark:text-gray-400">Loading...</div>`;
        }

        try {
            const res = await fetch('/crickets/pages/live-match');
            const html = await res.text();

            if (background) {
                // Preserve scroll position and prevent flash
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = html;
                const flexContainer = liveContainer.querySelector('div.flex');
                const newFlexContainer = tempDiv.querySelector('div.flex');
                if (flexContainer && newFlexContainer) {
                    flexContainer.innerHTML = newFlexContainer.innerHTML;
                }
            } else {
                liveContainer.innerHTML = html;
            }

            initScrollArrows(liveContainer);
        } catch (err) {
            if (!background) {
                liveContainer.innerHTML = `<div class="text-center py-6 text-red-500">Failed to load matches.</div>`;
            }
            console.error(err);
        }
    }

    tabBtns.forEach(btn => {
        btn.addEventListener('click', async () => {
            const tab = btn.dataset.tab;
            activateTab(tab);
            if (tab === "live") {
                await fetchLiveMatches(false); 
            }
        });
    });

    const lastTab = localStorage.getItem(TAB_KEY) || 'upcoming';
    activateTab(lastTab);
    if (lastTab === 'live') fetchLiveMatches(false);

    // Auto-refresh live tab every 60s in background
    setInterval(() => {
        if (localStorage.getItem(TAB_KEY) === 'live') {
            fetchLiveMatches(true);
        }
    }, 30000);
</script>