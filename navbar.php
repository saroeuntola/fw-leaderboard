<?php
// Example PHP navbar (logo can be dynamic)
include "./admin/lib/brand_lib.php";
$brandObj = new Brand();
$logos = $brandObj->getBrandByStatus();

$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
?>

<link rel="stylesheet" href="./css/navbar.css">
<link rel="preconnect" href="https://cdnjs.cloudflare.com">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" crossorigin="anonymous" referrerpolicy="no-referrer" />

<nav class="fixed top-0 left-0 right-0 z-50 shadow-lg bg-[#990f02] dark:bg-[#252525] text-gray-100 transition-colors duration-300 lg:py-0 py-1">
    <div class="max-w-7xl mx-auto px-4">
        <div class="flex justify-between items-center h-16">
            <!-- Logo -->
            <div class="flex space-x-4">
                <?php foreach ($logos as $b): ?>
                    <a href="<?= htmlspecialchars($b['link'] ?? '/') ?>" class="flex-shrink-0">
                        <img src="/admin/<?= htmlspecialchars($b['brand_image'] ?? 'default-logo.png') ?>"
                            alt="<?= htmlspecialchars($b['brand_name'] ?? 'logo') ?>"
                            class="h-10 object-contain">
                    </a>
                <?php endforeach; ?>


            </div>

            <!-- Desktop Menu -->
            <div class="hidden md:flex items-center space-x-6 font-medium">
                <?php
                $navItems = [
                    ['label' => 'Home', 'url' => '/'],
                    ['label' => 'Tournaments', 'url' => '/tournaments'],
                    ['label' => 'News', 'url' => '/news'],
                    ['label' => 'Leaderboard', 'url' => '/leaderboard'],
                    ['label' => 'Fancybet Guide', 'url' => 'https://fancybet.info', 'target' => '_blank'],
                ];

                foreach ($navItems as $item):
                    $isActive = rtrim($currentPath, '/') === rtrim($item['url'], '/') ? 'active' : '';
                    $target = $item['target'] ?? '_self';
                ?>
                    <a href="<?= htmlspecialchars($item['url']) ?>"
                        target="<?= htmlspecialchars($target) ?>"
                        class="nav-link hover:text-red-500 transition-colors duration-300 <?= $isActive ?>">
                        <?= htmlspecialchars($item['label']) ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <!-- Right section: Mobile theme toggle + hamburger -->
            <div class="flex items-center gap-5">
                <button id="theme-toggle" class="toggle-btn" aria-label="Toggle Theme">
                    <span class="toggle-circle">
                        <!-- Sun Icon -->
                        <svg id="icon-sun" class="icon absolute" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="5"></circle>
                            <line x1="12" y1="1" x2="12" y2="3"></line>
                            <line x1="12" y1="21" x2="12" y2="23"></line>
                            <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
                            <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
                            <line x1="1" y1="12" x2="3" y2="12"></line>
                            <line x1="21" y1="12" x2="23" y2="12"></line>
                            <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line>
                            <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
                        </svg>

                        <!-- Moon Icon -->
                        <svg id="icon-moon" class="icon absolute" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 12.79A9 9 0 1111.21 3 
                     7 7 0 0021 12.79z"></path>
                        </svg>
                    </span>
                </button>


                <!-- Mobile Hamburger -->
                <button id="mobile-menu-button" class="md:hidden">
                    <svg id="mobile-menu-icon" class="w-8 h-8 text-gray-100" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path id="mobile-menu-path" stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile Menu -->
    <div
        id="mobile-menu"
        class="max-h-0 overflow-hidden opacity-0 md:hidden bg-[#990b02] dark:bg-[#252525] px-4 transition-all duration-500 ease-in-out">
        <div class="w-full h-[2px] bg-[#990b02] dark:bg-[#252525] mt-2"></div>

        <ul class="py-6 flex flex-col gap-6">
            <?php
            foreach ($navItems as $item):
                $isActiveMobile = rtrim($currentPath, '/') === rtrim($item['url'], '/') ? 'active' : '';
                $target = $item['target'] ?? '_self'; // Open Fancybet Guide in new tab only
            ?>
                <a href="<?= htmlspecialchars($item['url']) ?>"
                    target="<?= htmlspecialchars($target) ?>"
                    class="flex items-center hover:text-red-500 transition-all duration-500 delay-200 <?= $isActiveMobile ?>">
                    <?php

                    $icons = [
                        'Home' => 'fa-house',
                        'Tournaments' => 'fa-trophy',
                        'News' => 'fa-newspaper',
                        'Leaderboard' => 'fa-ranking-star',
                        'Fancybet Guide' => 'fa-book-open',
                    ];
                    ?>
                    <i class="fa-solid <?= $icons[$item['label']] ?? 'fa-circle' ?> text-lg w-6 text-center"></i>
                    <span class="ml-2"><?= htmlspecialchars($item['label']) ?></span>
                </a>
            <?php endforeach; ?>
        </ul>
    </div>

</nav>
<script src="./js/navbar.js" defer></script>