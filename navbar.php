<?php
// Example PHP navbar (logo can be dynamic)
include "./admin/lib/brand_lib.php";
$brandObj = new Brand();
$logos = $brandObj->getBrandLimit(1);
$logo = $logos[0] ?? null;
?>
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
                <a href="/v2" class="hover:text-red-500 transition-colors duration-300">Home</a>
                <a href="/v2/tournaments" class="hover:text-red-500 transition-colors duration-300">Tournaments</a>
                <a href="/v2/news" class="hover:text-red-500 transition-colors duration-300">News</a>
                <a href="/v2/leaderboard" class="hover:text-red-500 transition-colors duration-300">Leaderboard</a>
                <a href="/v2/leaderboard" class="hover:text-red-500 transition-colors duration-300">Fancybet Guide</a>
            </div>


            <!-- Right section: Mobile theme toggle + hamburger -->
            <div class="flex items-center gap-5">
                <button
                    id="theme-toggle"
                    class="p-2 rounded-full bg-gray-200 dark:bg-gray-800 hover:bg-gray-300 dark:hover:bg-gray-700 transition-colors duration-300"
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
            <a href="/v2" class="flex items-center hover:text-red-500 transition-all duration-500 delay-200">

                <i class="fa-solid fa-house text-lg w-6 text-center"></i>
                <span class="ml-2">Home</span>
            </a>

            <a href="/v2/tournaments" class="flex items-center hover:text-red-500 transition-all duration-500 delay-200">
                <i class="fa-solid fa-trophy text-lg w-6 text-center"></i>
                <span class="ml-2">Tournaments</span>
            </a>

            <a href="/v2/news" class="flex items-center hover:text-red-500 transition-all duration-500 delay-200">
                <i class="fa-solid fa-newspaper text-lg w-6 text-center"></i>
                <span class="ml-2">News</span>
            </a>

            <a href="/v2/leaderboard" class="flex items-center hover:text-red-500 transition-all duration-500 delay-200">
                <i class="fa-solid fa-ranking-star text-lg w-6 text-center"></i>
                <span class="ml-2">Leaderboard</span>
            </a>

            <a href="/v2" class="flex items-center hover:text-red-500 transition-all duration-500 delay-200">
                <i class="fa-solid fa-book-open text-lg w-6 text-center"></i>
                <span class="ml-2">Fancybet Guide</span>
            </a>
        </ul>
    </div>

</nav>
<script src="./js/navbar.js"></script>