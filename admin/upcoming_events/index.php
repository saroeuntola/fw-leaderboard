<?php
ob_start();
include "../lib/checkroles.php";
include '../lib/upcoming_event_lib.php';
include '../lib/users_lib.php';
protectRoute([1, 3]);
                        date_default_timezone_set('Asia/Dhaka');

                        $eventObj = new UpcomingEvent();
$events = $eventObj->getUpcomingEvents();
$currentUser = $_SESSION['username'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id         = $_POST['id'] ?? null;
    $title      = $_POST['title'];
    $matches    = $_POST['matches'];
    $type       = $_POST['type'];
    $start_date = $_POST['start_date'];
    $end_date   = $_POST['end_date'];
    $post_by    = $currentUser;

    if ($id) {
        $eventObj->update($id, $title, $matches, $type, $start_date, $end_date, $post_by);
    } else {
        $eventObj->create($title, $matches, $type, $start_date, $end_date, $post_by);
    }

    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $eventObj->delete($id);
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Upcoming Events CRUD</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@5" rel="stylesheet" type="text/css" />
    <script src="/v2/js/tinymce/tinymce.min.js"></script>
</head>

<body class="bg-gray-900 text-white min-h-screen">
    <?php include "../include/sidebar.php" ?>
    <main class="flex-1 ml-64 p-6 transition-all duration-300" id="main-content">
        <h1 class="text-3xl font-bold mb-6">Upcoming Events</h1>
        <button onclick="openModal('create')" class="bg-green-500 px-4 py-2 rounded hover:bg-green-600 mb-4">+ Add Event</button>

        <div class="overflow-x-auto">
            <table class="min-w-full text-left border border-gray-700">
                <thead class="bg-gray-800">
                    <tr>
                        <th class="px-4 py-2">#</th>
                        <th class="px-4 py-2">Title</th>
                        <th class="px-4 py-2">Matches</th>
                        <th class="px-4 py-2">Type</th>
                        <th class="px-4 py-2">Start Date</th>
                        <th class="px-4 py-2">End Date</th>
                        <th class="px-4 py-2">Event Status</th>
                        <th class="px-4 py-2">Post by</th>
                        <th class="px-4 py-2">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($events as $index => $ev): ?>
                        <tr class="border-b border-gray-700">
                            <td class="px-4 py-2"><?= $index + 1 ?></td>
                            <td class="px-4 py-2"> <?= html_entity_decode($ev['title']) ?></td>
                            <td class="px-4 py-2"><?= $ev['matches'] ?></td>
                            <td class="px-4 py-2"><?= htmlspecialchars($ev['type']) ?></td>
                            <td class="px-4 py-2"><?= $ev['start_date'] ?></td>
                            <td class="px-4 py-2"><?= $ev['end_date'] ?></td>
                            <td class="px-4 py-2"><?= $ev['status'] ?></td>
                            <td class="px-4 py-2"><?= htmlspecialchars($ev['post_by']) ?></td>
                            <td class="px-4 py-2 space-x-2">
                                <button onclick="openModal('edit', <?= $ev['id'] ?>)" class="bg-blue-500 px-2 py-1 rounded hover:bg-blue-600">Edit</button>
                                <a href="?delete=<?= $ev['id'] ?>" onclick="return confirm('Are you sure to delete?')" class="bg-red-500 px-2 py-1 rounded hover:bg-red-600">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>

    <!-- Modal -->
    <div id="eventModal" class="fixed inset-0 bg-black/50 flex items-center justify-center hidden">
        <div class="bg-gray-800 p-6 rounded-lg w-full max-w-md">
            <h2 id="modalTitle" class="text-xl font-bold mb-4"></h2>
            <form id="eventForm" method="POST" class="space-y-4">
                <input type="hidden" name="id" id="eventId">

                <label class="block text-sm mb-1">Title*</label>
                <textarea
                    id="title"
                    name="title"
                    class="w-full p-2 rounded bg-gray-700 text-white"
                    rows="3">
</textarea>

                <div>
                    <label class="block text-sm mb-1">Matches*</label>
                    <input type="number" id="matches" name="matches" class="w-full p-2 rounded bg-gray-700 text-white" required>
                </div>

                <div>
                    <label class="block text-sm mb-1">Type*</label>
                    <select id="type" name="type" class="w-full p-2 rounded bg-gray-700 text-white" required>
                        <option value="lion">Lion</option>
                        <option value="tiger">Tiger</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm mb-1">Start Date*</label>
                    <input type="datetime-local" id="start_date" name="start_date" class="w-full p-2 rounded bg-gray-700 text-white" required>
                </div>

                <div>
                    <label class="block text-sm mb-1">End Date*</label>
                    <input type="datetime-local" id="end_date" name="end_date" class="w-full p-2 rounded bg-gray-700 text-white" required>
                </div>

                <div class="flex justify-end space-x-2">
                    <button type="button" onclick="closeModal()" class="bg-gray-600 px-3 py-1 rounded hover:bg-gray-700">Cancel</button>
                    <button type="submit" class="bg-green-500 px-3 py-1 rounded hover:bg-green-600">Save</button>
                </div>
            </form>
        </div>
    </div>
    <script>
        tinymce.init({
            selector: '#title',
            height: 180,
            menubar: false,
            branding: false,
            license_key: 'gpl',

            plugins: 'textcolor',
            toolbar: 'bold forecolor',

            toolbar_mode: 'sliding',
            content_style: `
        body {
            color: white;
            background: #808080;
            font-family: inherit;
        }
    `
        });
    </script>


    <script>
        const events = <?= json_encode($events) ?>;

        function openModal(mode, id = null) {
            document.getElementById('eventModal').classList.remove('hidden');
            if (mode === 'create') {
                document.getElementById('modalTitle').innerText = 'Add Event';
                document.getElementById('eventForm').reset();
                document.getElementById('eventId').value = '';
            } else if (mode === 'edit') {
                document.getElementById('modalTitle').innerText = 'Edit Event';
                const ev = events.find(e => e.id == id);
                if (!ev) return;

                document.getElementById('eventId').value = ev.id;
                document.getElementById('title').value = ev.title;
                document.getElementById('matches').value = ev.matches;
                document.getElementById('type').value = ev.type;
                document.getElementById('start_date').value = ev.start_date.replace(' ', 'T');
                document.getElementById('end_date').value = ev.end_date.replace(' ', 'T');
            }
        }

        function closeModal() {
            document.getElementById('eventModal').classList.add('hidden');
        }
    </script>
</body>

</html>