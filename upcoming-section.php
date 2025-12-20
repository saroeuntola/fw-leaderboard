<?php
include './admin/lib/upcoming_event_lib.php';

$eventLib = new UpcomingEvent();
$events = $eventLib->getAll();
$timezone = new DateTimeZone('Asia/Dhaka');

foreach ($events as &$e) {
    $start = new DateTime($e['start_date'], $timezone);
    $end   = new DateTime($e['end_date'], $timezone);
    $e['start_ms'] = $start->getTimestamp() * 1000;
    $e['end_ms']   = $end->getTimestamp() * 1000;
}
unset($e);
?>
<style>
    .live-badge {
        display: inline-block;
        background: #16a34a;
        color: #fff;
        font-weight: bold;
        padding: 2px 6px;
        border-radius: 4px;
        font-size: .8rem;
        margin-left: 5px;
        animation: pulse 1.5s infinite;
    }

    @keyframes pulse {
        0% {
            transform: scale(1);
            opacity: 1;
        }

        50% {
            transform: scale(1.1);
            opacity: 0.7;
        }

        100% {
            transform: scale(1);
            opacity: 1;
        }
    }

    .progress-container {
        background: #e5e7eb;
        border-radius: 4px;
        height: 8px;
        margin-top: 8px;
        overflow: hidden;
    }

    .progress-bar {
        background: #16a34a;
        height: 100%;
        width: 0%;
        transition: width 0.5s linear;
    }

    .event-card a {
        color: unset;
    }
</style>
<!-- RUNNING EVENTS -->
<div id="runningSection" class="w-full mb-6 hidden">
    <h1 class="inline-block bg-green-600 text-white px-3 py-1 lg:text-xl text-lg font-bold">🔴 Ongoing Events</h1>
    <div class="h-[2px] bg-green-600"></div>
    <div id="runningContainer" class="grid gap-5 md:grid-cols-2 lg:grid-cols-3 w-full max-w-7xl mt-4"></div>
</div>
<!-- UPCOMING EVENTS -->
<div id="upcomingSection" class="w-full mb-4">
    <h1 class="inline-block bg-red-800 text-white px-3 py-1 lg:text-xl text-lg font-bold">⏳ Upcoming Events</h1>
    <div class="h-[2px] bg-red-800"></div>
    <div id="upcomingContainer" class="grid gap-5 md:grid-cols-2 lg:grid-cols-3 w-full max-w-7xl mt-4">
        <?php foreach ($events as $event): ?>
            <div class="event-card bg-white text-gray-900 dark:bg-[#252525] dark:text-gray-100 rounded-md p-6 text-center shadow"
                data-id="<?= $event['id'] ?>"
                data-type="<?= $event['type'] ?>"
                data-start="<?= $event['start_ms'] ?>"
                data-end="<?= $event['end_ms'] ?>"
                data-status="<?= $event['status'] ?>">
                <h2 class="text-xl font-semibold flex items-center justify-center">
                    <span class="event-title">
                        <?= html_entity_decode($event['title']) ?>
                    </span>
                </h2>

                <p class="text-sm mt-2">Matches: <?= $event['matches'] ?></p>
                <p class="text-sm mt-2">Start Date: <?= $event['start_date'] ?></p>
                <p class="text-sm mt-1">End Date: <?= $event['end_date'] ?></p>
                <p class="time-label text-sm mt-1"></p>
                <p class="text-sm mt-3 font-semibold"><span class="countdown"></span></p>

            </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        function startCountdown(card) {
            const countdown = card.querySelector('.countdown');
            const progressContainer = card.querySelector('.progress-container');
            const eventStart = parseInt(card.dataset.start);
            const eventEnd = parseInt(card.dataset.end);

            const timer = setInterval(function() {
                const now = Date.now(); 
                if (now < eventStart) { 
                    const diff = eventStart - now;
                    const d = Math.floor(diff / (1000 * 60 * 60 * 24));
                    const h = Math.floor((diff / (1000 * 60 * 60)) % 24);
                    const m = Math.floor((diff / (1000 * 60)) % 60);
                    const s = Math.floor((diff / 1000) % 60);

                    countdown.innerHTML = `
                    <div class="text-cyan-400 text-sm mb-1 animate-pulse">Starts in:</div>
                    <span class="text-yellow-400 text-lg">${d}d</span> :
                    <span class="text-cyan-400 text-lg">${h}h</span> :
                    <span class="text-green-400 text-lg">${m}m</span> :
                    <span class="text-red-500 text-lg">${s}s</span>
                `;
                    progressContainer?.classList.add('hidden');
                } else if (now >= eventStart && now < eventEnd) { 
                    if (!card.closest('#runningContainer')) {
                        const type = card.dataset.type;
                        const link = document.createElement('a');
                        link.href = type === 'lion' ?
                            './view-lion-leaderboard' :
                            './view-tiger-leaderboard';
                        link.className = 'block'; 
                        link.style.textDecoration = 'none';
                        card.parentNode.insertBefore(link, card);
                        link.appendChild(card);
                        document.getElementById('runningContainer').appendChild(link);
                    }
                    const diff = eventEnd - now;
                    const h = Math.floor((diff / (1000 * 60 * 60)) % 24);
                    const m = Math.floor((diff / (1000 * 60)) % 60);
                    const s = Math.floor((diff / 1000) % 60);
                    countdown.innerHTML = `
        <div class="text-yellow-400 text-sm mb-1 animate-pulse">Time left:</div>
        <span class="text-yellow-400 text-lg">${h}h</span> :
        <span class="text-green-400 text-lg">${m}m</span> :
        <span class="text-pink-400 text-lg">${s}s</span>
    `;
                } else { // ENDED
                    countdown.innerHTML = "<span class='text-red-500 font-semibold'>Event Ended</span>";
                    clearInterval(timer);
                    card.remove();
                }
            }, 1000);
        }
        document.querySelectorAll('.event-card').forEach(card => startCountdown(card));
        setInterval(function() {
            const rSec = document.getElementById('runningSection');
            if (rSec) rSec.classList.toggle('hidden', document.getElementById('runningContainer').children.length === 0);
            const uSec = document.getElementById('upcomingSection');
            if (uSec) uSec.classList.toggle('hidden', document.getElementById('upcomingContainer').children.length === 0);
        }, 1000);
    });
</script>