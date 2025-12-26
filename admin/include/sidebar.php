<?php
$userLib = new User();
$userId = $_SESSION['user_id'] ?? null;
if (!$userId) {
    header("Location: login");
    exit;
}
$user = $userLib->getUser($userId);

// Get current path relative to /v2/
$currentPath = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
$currentPath = preg_replace('#^v2/#', '', $currentPath);
?>

<aside id="sidebar" class="fixed top-0 left-0 h-full w-64 bg-gray-800 shadow-lg flex flex-col justify-between transition-all duration-300 text-white">
    <div>
        <div class="flex items-center justify-between ml-4 p-4 border-b border-gray-200 dark:border-gray-700">
            <h1 class="text-xl font-bold">FW Dashboard</h1>
            <button id="sidebar-toggle" class="md:hidden p-2 rounded hover:bg-gray-200 dark:hover:bg-gray-700">
                ☰
            </button>
        </div>

        <nav class="mt-6 space-y-2 px-4 overflow-y-auto max-h-[calc(100vh-160px)]">
            <?php
            $sidebarLinks = [
                'admin' => 'Dashboard',
                'admin/login-logs' => 'Login Logs',
                'admin/user' => 'Users',
                'admin/lion_leaderboard' => 'Lion Leaderboard',
                'admin/tiger_leaderboard' => 'Tiger Leaderboard',
                'admin/lion_banners' => 'Lion Banner',
                'admin/tiger_banners' => 'Tiger Banner',
                'admin/logo' => 'Logo',
                'admin/banner' => 'Slide Banner',
                'admin/upcoming_events' => 'Upcoming event',
                'admin/post' => 'Posts Content',
                'admin/post_prev_tourament' => 'Prev Tournament',
                'admin/fwguide_announcement' => 'Fwguide Announcement',
            ];

            foreach ($sidebarLinks as $path => $title):
                $activeClass = ($currentPath === $path) ? 'bg-gray-200 text-gray-800' : '';
            ?>
                <a href="/<?= $path ?>/" class="btn btn-ghost btn-block justify-start hover:text-gray-800 <?= $activeClass ?>">
                    <?= $title ?>
                </a>
            <?php endforeach; ?>
        </nav>
    </div>

    <div class="p-8 border-t flex items-center justify-between">
        <h6 class="text-md font-semibold text-white">
            Welcome, <?= htmlspecialchars($_SESSION['username'] ?? 'User'); ?>
        </h6>
        <a href="/logout" class="text-sm btn bg-red-700 shadow-none border-none text-white hover:bg-red-900">Logout</a>
    </div>
</aside>

<script>
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebar-toggle');
    const mainContent = document.getElementById('main-content');

    sidebarToggle?.addEventListener('click', () => {
        sidebar.classList.toggle('-translate-x-64');
        mainContent.classList.toggle('ml-0');
    });
</script>