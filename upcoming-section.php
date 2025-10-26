<?php
include './admin/lib/upcoming_event_lib.php';

$eventLib = new UpcomingEvent();
$events = $eventLib->getAll();
?>

<h1 class="text-3xl font-bold mb-8 text-gray-900 dark:text-gray-100 md:text-left text-center">Upcoming Events</h1>

<div class="grid gap-5 md:grid-cols-2 lg:grid-cols-3 w-full max-w-7xl">
    <?php foreach ($events as $index => $event): ?>
        <div class="bg-gray-100 text-gray-900 dark:bg-gray-800 dark:text-gray-100 rounded-2xl shadow-lg p-6 text-center">
            <h2 class="text-xl font-semibold"><?= htmlspecialchars($event['title']) ?></h2>
            <p class="text-sm text-gray-900 dark:text-gray-100 mt-2">Matches: <?= htmlspecialchars($event['matches']) ?></p>
            <p class="text-sm text-gray-900 dark:text-gray-100 mt-2">Event Date: <?= htmlspecialchars($event['event_date']) ?></p>
            <p class="text-sm text-gray-900 dark:text-gray-100 mt-1">Duration: <?= htmlspecialchars($event['duration']) ?> minutes</p>
            <div id="countdown-<?= $index ?>" class="text-2xl font-bold text-green-400 mt-4"></div>
        </div>
    <?php endforeach; ?>
</div>
<?php
$js = file_get_contents('./js/upcoming-event.js');
$encoded = base64_encode($js);
echo '<script src="data:text/javascript;base64,' . $encoded . '" defer></script>';
?>
<script>
    document.addEventListener("DOMContentLoaded", () => {
        <?php foreach ($events as $index => $event): ?>
            startCountdown(
                "countdown-<?= $index ?>",
                "<?= htmlspecialchars($event['event_date']) ?>",
                <?= intval($event['duration'] ?? 120) ?>
            );
        <?php endforeach; ?>
    });
</script>