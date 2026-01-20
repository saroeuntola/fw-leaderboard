   <?php
        require_once $_SERVER['DOCUMENT_ROOT'] . '/crickets/services/ApiService.php';
        require_once $_SERVER['DOCUMENT_ROOT'] . '/crickets/services/apiCache.php';
        require_once $_SERVER['DOCUMENT_ROOT'] . '/crickets/services/cors.php';
        validateRequest();
        $matchId = $_GET['id'] ?? null;
        if (!$matchId) die('No match info');

        $cacheDir = $_SERVER['DOCUMENT_ROOT'] . '/crickets/cache';
         $TEN_HOURS = 10 * 60 * 60;
        $matchSquadResponse = apiCache(
            "$cacheDir/matchSq_$matchId.json",
            $TEN_HOURS, //12h
            fn() => ApiService::getMatchSquad($matchId)
        );

        $squads = $matchSquadResponse['data'] ?? [];
   
   ?>
   
   <div class="bg-white dark:bg-[#1f1f1f] rounded-xl shadow p-4 space-y-6">
       <?php foreach ($squads as $team): ?>
           <div>
               <h2 class="text-lg font-semibold mb-2"><?= htmlspecialchars($team['teamName']) ?></h2>
               <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                   <?php foreach ($team['players'] as $player): ?>
                       <div class="bg-gray-100 dark:bg-[#2a2a2a] rounded-lg p-3 flex items-center gap-3 hover:shadow-lg transition">
                           <img
                               src="<?= ($player['playerImg'] && $player['playerImg'] !== 'https://h.cricapi.com/img/icon512.png') ? $player['playerImg'] : '/crickets/img/no-profile-picture.webp' ?>"
                               alt="<?= htmlspecialchars($player['name']) ?>"
                               class="w-12 h-12 rounded-full object-cover">
                           <div class="text-sm">
                               <div class="font-semibold"><?= $player['name'] ?></div>
                               <div class="text-gray-400"><?= $player['role'] ?? '' ?></div>
                               <div class="text-gray-400"><?= $player['battingStyle'] ?? '' ?> <?= $player['bowlingStyle'] ?? '' ?></div>
                               <div class="text-gray-400"><?= $player['country'] ?? '' ?></div>
                           </div>
                       </div>
                   <?php endforeach; ?>
               </div>
           </div>
       <?php endforeach; ?>
   </div>