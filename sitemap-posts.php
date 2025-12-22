<?php
while (ob_get_level()) ob_end_clean();
ob_start();
header("Content-Type: application/xml; charset=utf-8");

require_once __DIR__ . '/admin/lib/db.php';
require_once __DIR__ . '/admin/lib/post_lib.php';

$baseUrl = "https://fancybet-leaderboard.com";
$today = date('Y-m-d');

// Fetch all posts
$postLib = new Post();
try {
    $posts = $postLib->getPostAll();
} catch (Exception $e) {
    $posts = [];
}
ob_clean();
// Start XML
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

foreach ($posts as $post) {
    if (empty($post['slug']) || empty($post['category_name'])) continue;

    // Determine path based on category
    switch (strtolower($post['category_name'])) {
        case 'news':
            $path = '/views-news';
            break;
        case 'tournament':
            $path = '/views';
            break;
        default:
            $path = '/views'; // fallback
    }

    $loc = rtrim($baseUrl, '/') . $path . '?slug=' . urlencode($post['slug']);

    echo "  <url>\n";
    echo "    <loc>" . htmlspecialchars($loc, ENT_XML1) . "</loc>\n";

    // Use updated_at if available
    $lastmod = !empty($post['updated_at']) ? date('Y-m-d', strtotime($post['updated_at'])) : $today;
    echo "    <lastmod>{$lastmod}</lastmod>\n";

    echo "    <changefreq>daily</changefreq>\n";
    echo "    <priority>1.0</priority>\n";
    echo "  </url>\n";
}

echo "</urlset>";
ob_end_flush();
