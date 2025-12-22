<?php
include_once './admin/lib/prev_tournament_lib.php';
$lionTournament = new TournamentPost();
$latestTournament = $lionTournament->getLatest(10);

?>
<section class="mt-10">

    <div class="w-full mb-4 mt-4">
        <h1 class="inline-block bg-red-800 text-white px-3 py-1 
           lg:text-xl text-lg font-bold">
            Previous Tournaments
        </h1>
        <div class="h-[2px] bg-red-800"></div>
    </div>
    <?php if (!empty($latestTournament)): ?>
        <?php foreach ($latestTournament as $item): ?>
            <?php
            // Determine the correct link based on type
            $link = $item['type'] === 'tiger'
                ? "/views-tiger-result?id=" . urlencode($item['id'])
                : "/views-lion-result?id=" . urlencode($item['id']);
            ?>
            <div class="bg-white dark:bg-[#252525]
            shadow-[0_0_5px_0_rgba(0,0,0,0.2)] rounded-md flex flex-col md:flex-row md:items-center justify-between gap-4 overflow-hidden mb-4">

                <!-- Left: Image -->
                <img src="./admin/uploads/<?= htmlspecialchars($item['image']) ?>"
                    alt="<?= htmlspecialchars($item['title']) ?>" loading="lazy"
                    class="w-full md:w-32 h-50 md:h-24 rounded-lg flex-shrink-0">

                <!-- Center: Title & Date -->
                <div class="flex-1 px-4">
                    <h2 class="dark:text-white text-gray-900 text-lg font-semibold "><?= htmlspecialchars($item['title']) ?></h2>
                    <div class="flex items-center mt-1 gap-2">
                        <i class="fa-solid fa-earth-americas text-gray-400"></i>
                        <p class="text-gray-400 text-sm">
                            <?= htmlspecialchars(date('F-j-Y', strtotime($item['created_at']))) ?>
                        </p>
                    </div>
                </div>

                <!-- Right: Button -->
                <div class="px-2 lg:w-auto w-full lg:mb-0 mb-4">
                    <a href="<?= $link ?>"
                        class="block border border-red-500 text-red-500 hover:bg-red-500 hover:text-white px-4 py-2 rounded-lg text-sm transition-colors w-full md:w-auto text-center">
                        See Result
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p class="text-gray-400">No Tournament Available</p>
    <?php endif; ?>
</section>