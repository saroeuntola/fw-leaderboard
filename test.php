<?php
include "./admin/lib/db.php";
include "./admin/lib/brand_lib.php";

$brandObj = new Brand();
$logos = $brandObj->getBrandLimit(1);
$logo = $logos[0] ?? null;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dark/Light Theme</title>

    <!-- Tailwind CSS -->
    <link rel="stylesheet" href="./src/output.css">

    <!-- Theme toggle JS -->
    <script src="/js/theme.js" defer></script>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous" />
</head>

<body class="bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 min-h-screen transition-colors duration-300">

    <!-- Navbar -->
    <nav class="fixed top-0 left-0 right-0 z-50 shadow-lg transition-colors duration-300 bg-white dark:bg-gray-900 text-gray-900 dark:text-white">
        <div class="max-w-7xl mx-auto flex justify-between items-center h-16 px-4">

            <!-- Logo -->
            <div class="lg:w-[200px] w-[120px]">
                <a href="<?= htmlspecialchars($logo['link'] ?? '/') ?>">
                    <img src="/admin/<?= htmlspecialchars($logo['brand_image'] ?? 'default-logo.png') ?>" class="h-10 object-contain" alt="Logo">
                </a>
            </div>

            <!-- Desktop Menu -->
            <div class="hidden md:flex space-x-6 items-center">
                <a href="/" class="hover:text-cyan-400 transition">Home</a>
                <a href="/v2/tournaments" class="hover:text-cyan-400 transition">Tournaments</a>
                <a href="/v2/news" class="hover:text-cyan-400 transition">News</a>
                <a href="/v2/leaderboard" class="hover:text-cyan-400 transition">Leaderboard</a>

                <!-- Desktop Theme Toggle -->
                <button id="theme-toggle" class="ml-4 p-2 rounded hover:bg-gray-300 dark:hover:bg-gray-700 transition" aria-label="Toggle Theme">
                    🌓
                </button>
            </div>

            <!-- Mobile Menu + Toggle -->
            <div class="md:hidden flex items-center space-x-2">
                <button id="mobile-menu-button" class="text-gray-900 dark:text-gray-100 focus:outline-none">
                    <i class="fas fa-bars"></i>
                </button>
                <button id="mobile-theme-toggle" class="p-2 rounded hover:bg-gray-300 dark:hover:bg-gray-700 transition" aria-label="Toggle Theme">
                    🌓
                </button>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div id="mobile-menu" class="md:hidden hidden bg-white dark:bg-gray-900 px-4 pt-2 pb-4 space-y-1 transition-colors duration-300">
            <a href="/" class="block hover:text-cyan-400 transition">Home</a>
            <a href="/v2/tournaments.php" class="block hover:text-cyan-400 transition">Tournaments</a>
            <a href="/v2/news.php" class="block hover:text-cyan-400 transition">News</a>
            <a href="/v2/leaderboard" class="block hover:text-cyan-400 transition">Leaderboard</a>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="pt-[100px] px-4 max-w-7xl mx-auto space-y-12">
        <?php include 'slideshow.php'; ?>
        <?php include 'upcoming-section.php'; ?>
        <?php include 'last-news-section.php'; ?>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-100 dark:bg-gray-800 text-gray-900 dark:text-gray-100 p-6 mt-12">
        <?php include 'footer.php'; ?>
    </footer>

    <script>
        // Mobile Menu Toggle
        const menuButton = document.getElementById('mobile-menu-button');
        const mobileMenu = document.getElementById('mobile-menu');
        menuButton.addEventListener('click', () => mobileMenu.classList.toggle('hidden'));

        // Desktop + Mobile Theme Toggle
        document.getElementById('theme-toggle').addEventListener('click', () => toggleTheme());
        document.getElementById('mobile-theme-toggle').addEventListener('click', () => toggleTheme());
    </script>
</body>

</html>