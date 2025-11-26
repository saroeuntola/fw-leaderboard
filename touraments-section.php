<?php
include_once './admin/lib/post_lib.php';
$listPost = new Post();

$posts = $listPost->getPostByCategory(2, 'en', 4, 1);
?>


<div class="w-full mb-4 mt-4">
    <h1 class="inline-block bg-red-800 text-white px-3 py-1 
           lg:text-xl text-lg font-bold">
        Tournaments
    </h1>
    <div class="h-[2px] bg-red-800"></div>
</div>
<div class="grid gap-3 sm:grid-cols-1 md:grid-cols-2 lg:grid-cols-4 text-white cursor-pointer mb-10">
    <?php if (!empty($posts)): ?>
        <?php foreach ($posts as $post): ?>
            <a href="views?slug=<?= urlencode($post['slug']); ?>"
                class="bg-white dark:bg-[#252525]
            shadow-[0_0_5px_0_rgba(0,0,0,0.2)] rounded-md overflow-hidden">
                <!-- Image -->
                <div class="overflow-hidden rounded-t-md">
                    <img src="./admin/post/<?= htmlspecialchars($post['image']) ?>"
                        alt="<?= htmlspecialchars($post['name']) ?>" loading="lazy"
                        class="w-full transition-transform duration-500 hover:scale-105 image-card object-cover">
                </div>

                <div class="p-4">
                    <h2 class="text-lg font-semibold mb-2 dark:text-white text-gray-900 truncate"><?= htmlspecialchars($post['name']) ?></h2>
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-earth-americas text-gray-400"></i>
                        <p class="text-gray-400 text-xs">
                            <?= htmlspecialchars(date('F j, Y', strtotime($post['created_at']))) ?>
                        </p>
                    </div>

                </div>
            </a>
        <?php endforeach; ?>
    <?php else: ?>
        <p class="text-gray-400 text-center col-span-4">No tournaments found.</p>
    <?php endif; ?>
</div>