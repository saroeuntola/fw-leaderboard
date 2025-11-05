<?php
include_once './admin/lib/post_lib.php';
$listPost = new Post();

$posts = $listPost->getPostByCategory(2, 'en', 4, 1);
?>

<h1 class="lg:text-3xl text-xl font-bold mb-8 mt-10 dark:text-white text-gray-900">Tournaments</h1>

<div class="grid gap-3 sm:grid-cols-1 md:grid-cols-2 lg:grid-cols-4 text-white cursor-pointer mb-10">
    <?php if (!empty($posts)): ?>
        <?php foreach ($posts as $post): ?>
            <a href="views?slug=<?= urlencode($post['slug']); ?>"
                class="dark:bg-gray-800 bg-white rounded-xl shadow-md overflow-hidden hover:shadow-xl transition-shadow">
                <!-- Image -->
                <div class="overflow-hidden rounded-t-xl">
                    <img src="./admin/post/<?= htmlspecialchars($post['image']) ?>"
                        alt="<?= htmlspecialchars($post['name']) ?>" loading="lazy"
                        class="w-full h-52 transition-transform duration-500 hover:scale-105">
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