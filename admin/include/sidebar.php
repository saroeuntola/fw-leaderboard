  <!-- Sidebar -->
  <?php
    $userLib = new User();
    $userId = $_SESSION['user_id'] ?? null;
    if (!$userId) {
        header("Location: login");
        exit;
    }
    $user = $userLib->getUser($userId);
    ?>
  <aside id="sidebar" class="fixed top-0 left-0 h-full w-64 bg-white dark:bg-gray-800 shadow-lg flex flex-col justify-between transition-all duration-300 text-white">
      <div>
          <div class="flex items-center justify-between ml-4 p-4 border-b border-gray-200 dark:border-gray-700">
              <h1 class="text-xl font-bold">FW Dashboard</h1>
              <button id="sidebar-toggle" class="md:hidden p-2 rounded hover:bg-gray-200 dark:hover:bg-gray-700">
                  ☰
              </button>
          </div>
          <nav class="mt-6 space-y-2 px-4">
              <a href="/v2/admin" class="btn btn-ghost btn-block justify-start hover:text-gray-900">
                  Dashboard
              </a>
              <a href="/v2/admin/user/" class="btn btn-ghost btn-block justify-start hover:text-gray-900">Users</a>
              <a href="/v2/admin/lion_leaderboard/" class="btn btn-ghost btn-block justify-start hover:text-gray-900">Lion Leaderboard</a>
              <a href="/v2/admin/tiger_leaderboard/" class="btn btn-ghost btn-block justify-start hover:text-gray-900">Tiger Leaderboard</a>
              <a href="/v2/admin/lion_banners/" class="btn btn-ghost btn-block justify-start hover:text-gray-900">Lion Banner</a>
              <a href="/v2/admin/tiger_banners/" class="btn btn-ghost btn-block justify-start hover:text-gray-900">Tiger Banner</a>
              <a href="/v2/admin/logo/" class="btn btn-ghost btn-block justify-start hover:text-gray-900">logo</a>
              <a href="/v2/admin/banner/" class="btn btn-ghost btn-block justify-start hover:text-gray-900">Slide Banner</a>
              <a href="/v2/admin/upcoming_events/" class="btn btn-ghost btn-block justify-start hover:text-gray-900">Upcoming event</a>
              <a href="/v2/admin/post/" class="btn btn-ghost btn-block justify-start hover:text-gray-900">Posts Content</a>
              <a href="/v2/admin/post_prev_tourament_lion/" class="btn btn-ghost btn-block justify-start hover:text-gray-900">Post Prev Tournament Lion</a>
              <a href="/v2/admin/post_prev_tourament_tiger/" class="btn btn-ghost btn-block justify-start hover:text-gray-900">Post Prev Tournament Tiger</a>
          </nav>
      </div>

      <div class="p-8 border-t border-gray-200 dark:border-gray-700 flex items-center justify-between">
          <h6 class="text-md font-semibold text-white">
              Welcome, <?= htmlspecialchars($_SESSION['username'] ?? 'User'); ?>
          </h6>
          <a href="../logout" class="text-sm btn bg-red-700 shadow-none border-none text-white hover:bg-red-900">Logout</a>
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