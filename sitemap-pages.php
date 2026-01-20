<?php
while (ob_get_level()) ob_end_clean();
ob_start();

header("Content-Type: application/xml; charset=utf-8");

$baseUrl = "https://fancybet-leaderboard.com";
$today = date('Y-m-d');

$pages = [
    ['slug' => '', 'freq' => 'daily', 'priority' => '1.0'],
    ['slug' => '/tournaments', 'freq' => 'daily', 'priority' => '0.9'],
    ['slug' => '/leaderboard', 'freq' => 'daily', 'priority' => '0.8'],
    ['slug' => '/news', 'freq' => 'daily', 'priority' => '0.9'],
];

// Start XML
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

foreach ($pages as $page) {

    $loc = rtrim($baseUrl, '/') . '/' . ltrim($page['slug'], '/');

    echo "  <url>\n";
    echo "    <loc>" . htmlspecialchars($loc, ENT_XML1) . "</loc>\n";
    echo "    <lastmod>{$today}</lastmod>\n";
    echo "    <changefreq>{$page['freq']}</changefreq>\n";
    echo "    <priority>{$page['priority']}</priority>\n";
    echo "  </url>\n";
}

echo "</urlset>";
ob_end_flush();
