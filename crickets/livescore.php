<?php
require_once 'services/ApiService.php';
require_once 'services/apiCache.php';

$cacheDir = __DIR__ . '/cache';
if (!is_dir($cacheDir)) mkdir($cacheDir, 0755, true);

// ================= FETCH ALL MATCHES (CACHED) =================
$response = apiCache(
    "$cacheDir/cricScore_all.json",
    180, // 2 minutes cache
    fn() => ApiService::getLivescore()
);

$matches = $response['data'] ?? [];

// ================= FILTER BY MATCH STATUS =================
$liveMatches     = [];
$upcomingMatches = [];
$resultMatches   = [];

foreach ($matches as $m) {
    $status = strtolower($m['ms'] ?? '');
    if ($status === 'live') {
        $liveMatches[] = $m;
    } elseif ($status === 'fixture') {
        $upcomingMatches[] = $m;
    } elseif ($status === 'result') {
        $resultMatches[] = $m;
    }
}
// ================= SORT UPCOMING (START FROM TODAY) =================
$now = new DateTime('now', new DateTimeZone('UTC'));

// Remove past fixtures
$upcomingMatches = array_filter($upcomingMatches, function ($m) use ($now) {
    if (empty($m['dateTimeGMT'])) return false;
    return new DateTime($m['dateTimeGMT'], new DateTimeZone('UTC')) >= $now;
});

// Sort by date ASC
usort($upcomingMatches, function ($a, $b) {
    return strtotime($a['dateTimeGMT'] ?? '') <=> strtotime($b['dateTimeGMT'] ?? '');
});

function matchDayLabel($dateTimeGMT)
{
    $matchDate = new DateTime($dateTimeGMT, new DateTimeZone('UTC'));
    $matchDate->setTimezone(new DateTimeZone('Asia/Dhaka'));

    $today = new DateTime('today', new DateTimeZone('Asia/Dhaka'));
    $tomorrow = (clone $today)->modify('+1 day');

    if ($matchDate->format('Y-m-d') === $today->format('Y-m-d')) {
        return 'Today';
    } elseif ($matchDate->format('Y-m-d') === $tomorrow->format('Y-m-d')) {
        return 'Tomorrow';
    }

    return $matchDate->format('d M Y');
}

function gmtToDhaka($dateTimeGMT, $format = 'g:i A')
{
    if (empty($dateTimeGMT)) return '';

    $dt = new DateTime($dateTimeGMT, new DateTimeZone('UTC'));
    $dt->setTimezone(new DateTimeZone('Asia/Dhaka'));

    return $dt->format($format);
}

// ================= MATCH CARD COMPONENT =================

function matchCard($m)
{

    $matchId = $m['id'] ?? null;
    $isLive  = (!empty($m['matchStarted']) && empty($m['matchEnded'])) || (($m['ms'] ?? '') === 'live');
    $label   = matchDayLabel($m['dateTimeGMT'] ?? '');
    $ms      = $m['ms'] ?? '';
    // Decide URL by match status
    if ($matchId) {
        if ($ms === 'fixture') {
            $url = '/crickets/upcoming-detail?id=' . urlencode($matchId);
        } else {
            $url = '/crickets/match-detail?id=' . urlencode($matchId);
        }
    } else {
        $url = null;
    }

    // Wrapper
    $wrapperStart = $url
        ? '<a href="' . htmlspecialchars($url) . '" class="block">'
        : '<div>';

    $wrapperEnd = $url ? '</a>' : '</div>';
?>
    <?= $wrapperStart ?>

    <div class="relative lg:min-w-[370px] min-w-[320px] bg-white dark:bg-[#1f1f1f] rounded-xl shadow p-4 snap-start">

        <!-- LIVE badge -->
        <?php if ($isLive): ?>
            <span class="absolute top-3 right-3 bg-red-600 text-white text-xs font-bold px-2 py-1 rounded animate-pulse">
                LIVE
            </span>
        <?php endif; ?>

        <!-- Date -->
        <div class="text-xs text-gray-400 mb-2">
            <?= htmlspecialchars($label) ?>
            <?php if (!empty($m['dateTimeGMT'])): ?>
                • <?= gmtToDhaka($m['dateTimeGMT'], 'g:i A') ?>
            <?php endif; ?>
        </div>
        <!-- Teams -->
        <div class="space-y-3">
            <div class="flex items-center gap-2">
                <img src="<?= htmlspecialchars($m['t1img'] ?? '') ?>" class="w-6 h-6 rounded-full">
                <span class="text-sm font-semibold"><?= htmlspecialchars($m['t1'] ?? '') ?></span>
                <?php if (!empty($m['t1s'])): ?>
                    <span class="ml-auto text-sm font-bold"><?= htmlspecialchars($m['t1s']) ?></span>
                <?php endif; ?>
            </div>

            <div class="flex items-center gap-2">
                <img src="<?= htmlspecialchars($m['t2img'] ?? '') ?>" class="w-6 h-6 rounded-full">
                <span class="text-sm font-semibold"><?= htmlspecialchars($m['t2'] ?? '') ?></span>
                <?php if (!empty($m['t2s'])): ?>
                    <span class="ml-auto text-sm font-bold"><?= htmlspecialchars($m['t2s']) ?></span>
                <?php endif; ?>
            </div>
        </div>

        <!-- Status -->
        <div class="text-xs text-red-500 mt-3 line-clamp-2">
            <?= htmlspecialchars($m['status'] ?? '') ?>
        </div>

        <!-- Series -->
        <div class="text-[11px] text-yellow-500 mt-1">
            <?= htmlspecialchars($m['series'] ?? '') ?>
        </div>

    </div>

    <?= $wrapperEnd ?>
<?php } ?>
<style>
    .no-scrollbar::-webkit-scrollbar {
        display: none;
    }

    .no-scrollbar {
        scrollbar-width: none;
        -ms-overflow-style: none;
    }

    .tab-btn {
        padding-bottom: 10px;
        font-weight: 600;
        color: #9ca3af;
        white-space: nowrap;
    }

    .tab-active {
        color: #fff;
        border-bottom: 2px solid #ef4444;
    }

    .scroll-arrow {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        background-color: #9ca3af;
        /* semi-transparent overlay */
        border-radius: 9999px;
        padding: 12px;
        cursor: pointer;
        z-index: 20;
        transition: background 0.2s;
        font-size: 18px;
        color: white;
    }

    .scroll-arrow:hover {
        background: rgba(239, 68, 68, 0.8);
    }

    .scroll-arrow.left {
        left: -20px;
        /* half outside */
    }

    .scroll-arrow.right {
        right: -20px;
        /* half outside */
    }

    /* optional: hide arrows on mobile */
    @media (max-width: 1024px) {
        .scroll-arrow {
            display: none;
        }
    }
</style>
<div class="max-w-7xl mx-auto">
    <!-- ================= PAGE TITLE ================= -->

    <div class="w-full mb-4">
        <h1 class="inline-block bg-sky-600 text-white px-3 py-1 
           lg:text-xl text-lg font-bold">
            Cricket Live Scores
        </h1>
        <div class="h-[2px] bg-sky-600"></div>
    </div>


    <!-- ================= TABS ================= -->
    <div class="flex gap-8 border-b border-gray-700 overflow-x-auto no-scrollbar mb-4">

        <button class="tab-btn" data-tab="upcoming">Upcoming</button>
        <button class="tab-btn tab-active" data-tab="live">Live</button>
        <button class="tab-btn" data-tab="results">Results</button>
    </div>

    <!-- ================= TAB CONTENT ================= -->
    <!-- UPCOMING -->
    <div id="upcoming" class="tab-panel hidden">
        <?php if (!empty($upcomingMatches)): ?>
            <div class="relative">

                <!-- LEFT ARROW -->
                <button class="scroll-arrow left"
                    onclick="scrollContainer('upcoming-scroll', -1)">
                    &#10094; <!-- ← -->
                </button>

                <!-- RIGHT ARROW -->
                <button class="scroll-arrow right"
                    onclick="scrollContainer('upcoming-scroll', 1)">
                    &#10095; <!-- → -->
                </button>

                <div id="upcoming-scroll"
                    class="flex gap-4 overflow-x-auto pb-3 snap-x snap-mandatory no-scrollbar scroll-smooth">
                    <?php foreach ($upcomingMatches as $m): ?>
                        <?php matchCard($m); ?>
                    <?php endforeach; ?>
                </div>

            </div>


        <?php else: ?>
            <p class="text-gray-400">No upcoming matches.</p>
        <?php endif; ?>
    </div>
    <!-- LIVE -->
    <div id="live" class="tab-panel">
        <?php if (!empty($liveMatches)): ?>
            <div class="relative">

                <!-- LEFT ARROW -->
                <button class="scroll-arrow left"
                    onclick="scrollContainer('livescore-scroll', -1)">
                    &#10094; <!-- ← -->
                </button>

                <!-- RIGHT ARROW -->
                <button class="scroll-arrow right"
                    onclick="scrollContainer('livescore-scroll', 1)">
                    &#10095; <!-- → -->
                </button>

                <div id="livescore-scroll"
                    class="flex gap-4 overflow-x-auto pb-3 snap-x snap-mandatory no-scrollbar scroll-smooth">
                    <?php foreach ($liveMatches as $m): ?>
                        <?php matchCard($m); ?>
                    <?php endforeach; ?>
                </div>

            </div>

        <?php else: ?>
            <p class="text-gray-400">No live matches right now.</p>
        <?php endif; ?>
    </div>



    <!-- RESULTS -->
    <div id="results" class="tab-panel hidden">
        <?php if (!empty($resultMatches)): ?>
            <div class="relative">

                <!-- LEFT ARROW -->
                <button class="scroll-arrow left"
                    onclick="scrollContainer('result-scroll', -1)">
                    &#10094; <!-- ← -->
                </button>

                <!-- RIGHT ARROW -->
                <button class="scroll-arrow right"
                    onclick="scrollContainer('result-scroll', 1)">
                    &#10095; <!-- → -->
                </button>

                <div id="result-scroll"
                    class="flex gap-4 overflow-x-auto pb-3 snap-x snap-mandatory no-scrollbar scroll-smooth">
                    <?php foreach ($resultMatches as $m): ?>
                        <?php matchCard($m); ?>
                    <?php endforeach; ?>
                </div>

            </div>
        <?php else: ?>
            <p class="text-gray-400">No results available.</p>
        <?php endif; ?>
    </div>

</div>

<!-- ================= TAB SCRIPT ================= -->
<script>
    const tabs = document.querySelectorAll('.tab-btn');
    const panels = document.querySelectorAll('.tab-panel');

    function showTab(tab) {
        tabs.forEach(b => b.classList.remove('tab-active'));
        panels.forEach(p => p.classList.add('hidden'));

        const btn = document.querySelector(`[data-tab="${tab}"]`);
        const pnl = document.getElementById(tab);

        if (btn && pnl) {
            btn.classList.add('tab-active');
            pnl.classList.remove('hidden');
            localStorage.setItem('cric_tab', tab);
        }
    }

    tabs.forEach(btn => {
        btn.addEventListener('click', () => showTab(btn.dataset.tab));
    });

    // Restore last active tab
    showTab(localStorage.getItem('cric_tab') || 'live');
</script>
<script>
    const liveContainerId = 'livescore-scroll';

    function updateLiveArrows() {
        const container = document.getElementById(liveContainerId);
        if (!container) return;

        const wrapper = container.parentElement;
        const leftArrow = wrapper.querySelector('.scroll-arrow.left');
        const rightArrow = wrapper.querySelector('.scroll-arrow.right');

        if (container.scrollWidth <= container.clientWidth) {
            // Not scrollable → hide arrows
            leftArrow.style.display = 'none';
            rightArrow.style.display = 'none';
        } else {
            // Scrollable → show arrows
            leftArrow.style.display = 'block';
            rightArrow.style.display = 'block';

            // Optional: disable arrows at edges
            leftArrow.disabled = container.scrollLeft === 0;
            rightArrow.disabled = container.scrollLeft + container.clientWidth >= container.scrollWidth;
        }
    }

    // Scroll function
    function scrollContainer(id, direction) {
        const el = document.getElementById(id);
        if (!el) return;
        const scrollAmount = el.offsetWidth * 0.8;
        el.scrollBy({
            left: direction * scrollAmount,
            behavior: 'smooth'
        });

        // Small timeout to update disabled state after scroll
        setTimeout(updateLiveArrows, 200);
    }

    // Initial check
    updateLiveArrows();

    // Update on resize
    window.addEventListener('resize', updateLiveArrows);
</script>