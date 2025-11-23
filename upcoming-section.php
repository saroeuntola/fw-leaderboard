<?php
include './admin/lib/upcoming_event_lib.php';

$eventLib = new UpcomingEvent();
$events = $eventLib->getAll();
?>

<div class="w-full mb-4">
    <h1 class="inline-block bg-red-800 text-white px-3 py-1 
           lg:text-xl text-lg font-bold">
        Upcoming Events
    </h1>
<div class="h-[2px] bg-red-800"></div>
</div>


<div class="grid gap-5 md:grid-cols-2 lg:grid-cols-3 w-full max-w-7xl">
    <?php foreach ($events as $index => $event): ?>
        <div class="bg-white text-gray-900 dark:bg-[#252525] dark:text-gray-100 rounded-md p-6 text-center shadow-[0_0_5px_0_rgba(0,0,0,0.2)] ">
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