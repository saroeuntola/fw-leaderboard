<?php
include_once './admin/lib/db.php';
include_once './admin/lib/post_lib.php';

$listPost = new Post();

// Pagination setup
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$limit = 12; // items per page

$totalPosts = (int)$listPost->getPostCountByCategory(3);
$totalPages = $limit > 0 ? (int)ceil($totalPosts / $limit) : 1;
if ($totalPages < 1) $totalPages = 1;

$posts = $listPost->getPostByCategory(2, 'en', $limit, $page);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Tournaments</title>
    <link rel="stylesheet" href="./src/output.css">
    <link rel="icon" type="image/x-icon" href="/v2/images/favicon.png">
</head>

<body class="dark:bg-gray-900 bg-gray-200">
    <?php
         include "./loading.php";
    ?>
    <?php include "./navbar.php" ?>
    <main class="max-w-7xl m-auto px-4 pt-32 pb-32">
        <h1 class="text-3xl font-bold mb-8 dark:text-white text-gray-900">All Tournaments</h1>
        <div class="grid gap-5 sm:grid-cols-1 md:grid-cols-2 lg:grid-cols-3 text-white cursor-pointer">
            <?php foreach ($posts as $post): ?>
                <a href="views?slug=<?= urlencode($post['slug']); ?>" class="dark:bg-gray-800 bg-white rounded-xl shadow-md overflow-hidden hover:shadow-xl transition-shadow">
                    <!-- Image with hover zoom -->
                    <div class="overflow-hidden rounded-t-xl">
                        <img src="./admin/post/<?= htmlspecialchars($post['image']) ?>"
                            alt="<?= htmlspecialchars($post['name']) ?>"
                            class="w-full h-60 object-cover transition-transform duration-500 hover:scale-105">
                    </div>
                    <div class="p-4">
                        <h2 class="text-lg font-semibold mb-2 truncate text-gray-900 dark:text-white"><?= htmlspecialchars($post['name']) ?></h2>
                        <p class="text-gray-400 text-xs mt-2"><?= date('F-j-Y', strtotime($post['created_at'])) ?></p>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <nav class="mt-6 flex items-center justify-center" aria-label="Pagination">
                <ul class="inline-flex items-center -space-x-px">
                    <!-- Previous -->
                    <li>
                        <?php if ($page > 1): ?>
                            <a href="?page=<?= $page - 1 ?>" class="px-3 py-1.5 rounded-l-md bg-gray-800 text-white hover:bg-gray-700">Prev</a>
                        <?php else: ?>
                            <span class="px-3 py-1.5 rounded-l-md bg-gray-700 text-gray-400">Prev</span>
                        <?php endif; ?>
                    </li>

                    <!-- Page numbers (show a limited window) -->
                    <?php
                    $window = 5; // how many page links to show
                    $start = max(1, $page - floor($window / 2));
                    $end = min($totalPages, $start + $window - 1);
                    if ($end - $start + 1 < $window) {
                        $start = max(1, $end - $window + 1);
                    }
                    for ($i = $start; $i <= $end; $i++):
                    ?>
                        <li>
                            <?php if ($i == $page): ?>
                                <span class="px-3 py-1.5 bg-indigo-600 text-white font-medium"><?= $i ?></span>
                            <?php else: ?>
                                <a href="?page=<?= $i ?>" class="px-3 py-1.5 bg-gray-800 text-white hover:bg-gray-700"><?= $i ?></a>
                            <?php endif; ?>
                        </li>
                    <?php endfor; ?>

                    <!-- Next -->
                    <li>
                        <?php if ($page < $totalPages): ?>
                            <a href="?page=<?= $page + 1 ?>" class="px-3 py-1.5 rounded-r-md bg-gray-800 text-white hover:bg-gray-700">Next</a>
                        <?php else: ?>
                            <span class="px-3 py-1.5 rounded-r-md bg-gray-700 text-gray-400">Next</span>
                        <?php endif; ?>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    </main>

    <?php include "./footer.php" ?>
</body>

</html>