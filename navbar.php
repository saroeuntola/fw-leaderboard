<?php
// Example PHP navbar (logo can be dynamic)
include "./admin/lib/brand_lib.php";
$brandObj = new Brand();
$logos = $brandObj->getBrandLimit(1);
$logo = $logos[0] ?? null;

$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
?>

<style>
    .nav-link {
        position: relative;
        display: inline-block;
        padding-bottom: 2px;
    }

    .nav-link::after {
        content: '';
        position: absolute;
        left: 0;
        bottom: 0;
        height: 2px;
        width: 100%;
        background-color: #f43f5e;
        /* red-500 */
        transform: scaleX(0);
        transform-origin: left;
        transition: transform 0.3s ease;
    }

    .nav-link:hover::after,
    .nav-link.active::after {
        transform: scaleX(1);
    }
</style>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" crossorigin="anonymous" referrerpolicy="no-referrer" />

<nav class="fixed top-0 left-0 right-0 z-50 shadow-lg bg-white dark:bg-black text-gray-800 dark:text-gray-100 transition-colors duration-300 lg:py-0 py-1">
    <div class="max-w-7xl mx-auto px-4">
        <div class="flex justify-between items-center h-16">

            <!-- Logo -->
            <div class="flex-shrink-0">
                <a href="<?= htmlspecialchars($logo['link'] ?? '/') ?>" class="text-2xl font-bold text-red-600">
                    <img src="/v2/admin/<?= htmlspecialchars($logo['brand_image'] ?? 'default-logo.png') ?>" alt="Logo" class="h-10 object-contain">
                </a>
            </div>

            <!-- Desktop Menu -->
            <div class="hidden md:flex items-center space-x-6 font-medium">
                <?php
                $navItems = [
                    ['label' => 'Home', 'url' => '/v2'],
                    ['label' => 'Tournaments', 'url' => '/v2/tournaments'],
                    ['label' => 'News', 'url' => '/v2/news'],
                    ['label' => 'Leaderboard', 'url' => '/v2/leaderboard'],
                    ['label' => 'Fancybet Guide', 'url' => '/v2/guide'],
                ];

                foreach ($navItems as $item):
                    // Exact match for active link
                    $isActive = rtrim($currentPath, '/') === rtrim($item['url'], '/') ? 'active' : '';
                ?>
                    <a href="<?= $item['url'] ?>" class="nav-link hover:text-red-500 transition-colors duration-300 <?= $isActive ?>">
                        <?= $item['label'] ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <!-- Right section: Mobile theme toggle + hamburger -->
            <div class="flex items-center gap-5">
                <button
                    id="theme-toggle"
                    class="p-2 rounded-full hover:bg-gray-300 dark:hover:bg-gray-700 transition-colors duration-300"
                    aria-label="Toggle Theme">
                    <svg
                        id="theme-icon"
                        class="h-6 w-6 text-black dark:text-white transition-colors duration-300"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        viewBox="0 0 24 24">
                    </svg>
                </button>

                <!-- Mobile Hamburger -->
                <button id="mobile-menu-button" class="md:hidden">
                    <svg id="mobile-menu-icon" class="w-8 h-8 text-gray-800 dark:text-gray-100" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path id="mobile-menu-path" stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile Menu -->
    <div
        id="mobile-menu"
        class="max-h-0 overflow-hidden opacity-0 md:hidden bg-white dark:bg-black px-4 transition-all duration-500 ease-in-out">
        <div class="w-full h-[2px] bg-black dark:bg-white mt-2"></div>

        <ul class="py-6 flex flex-col gap-6">
            <?php foreach ($navItems as $item):
                $isActiveMobile = rtrim($currentPath, '/') === rtrim($item['url'], '/') ? 'active' : '';
            ?>
                <a href="<?= $item['url'] ?>" class="flex items-center hover:text-red-500 transition-all duration-500 delay-200 nav-link <?= $isActiveMobile ?>">
                    <?php
                    // Add simple icons for mobile menu
                    $icons = [
                        'Home' => 'fa-house',
                        'Tournaments' => 'fa-trophy',
                        'News' => 'fa-newspaper',
                        'Leaderboard' => 'fa-ranking-star',
                        'Fancybet Guide' => 'fa-book-open',
                    ];
                    ?>
                    <i class="fa-solid <?= $icons[$item['label']] ?? 'fa-circle' ?> text-lg w-6 text-center"></i>
                    <span class="ml-2"><?= $item['label'] ?></span>
                </a>
            <?php endforeach; ?>
        </ul>
    </div>
</nav>

<script src="/v2/navbar.js"></script>


