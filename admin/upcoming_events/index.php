<?php
ob_start();
include "../lib/checkroles.php";
include '../lib/upcoming_event_lib.php';
include '../lib/users_lib.php';
protectPathAccess();
$eventObj = new UpcomingEvent();
$events = $eventObj->getAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id         = $_POST['id'] ?? null; // <-- fix here
    $title      = $_POST['title'];
    $matches    = $_POST['matches'];
    $event_date = $_POST['event_date'];
    $duration   = $_POST['duration']; // new

    if ($id) {
        $eventObj->update($id, $title, $matches, $event_date, $duration);
    } else {
        $eventObj->create($title, $matches, $event_date, $duration);
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
</head>

<body class="bg-gray-900 text-white min-h-screen">
    <?php include "../include/sidebar.php" ?>
    <main class="flex-1 ml-64 p-6 transition-all duration-300" id="main-content">
        <h1 class="text-3xl font-bold mb-6">Upcoming Events</h1>
        <!-- Add Button -->
        <button onclick="openModal('create')" class="bg-green-500 px-4 py-2 rounded hover:bg-green-600 mb-4">+ Add Event</button>
        <!-- Events Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full text-left border border-gray-700">
                <thead class="bg-gray-800">
                    <tr>
                        <th class="px-4 py-2">#</th>
                        <th class="px-4 py-2">Title</th>
                        <th class="px-4 py-2">Matches</th>
                        <th class="px-4 py-2">Duration (minutes)</th>
                        <th class="px-4 py-2">Event Date</th>
                        <th class="px-4 py-2">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($events as $index => $ev): ?>
                        <tr class="border-b border-gray-700">
                            <td class="px-4 py-2"><?= $index + 1 ?></td>
                            <td class="px-4 py-2"><?= htmlspecialchars($ev['title']) ?></td>
                            <td class="px-4 py-2"><?= $ev['matches'] ?></td>
                            <td class="px-4 py-2"><?= htmlspecialchars($ev['duration']) ?></td>
                            <td class="px-4 py-2"><?= $ev['event_date'] ?></td>
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
                <div>
                    <label class="block text-sm mb-1">Title</label>
                    <input type="text" id="title" name="title" class="w-full p-2 rounded bg-gray-700 text-white" required>
                </div>
                <div>
                    <label class="block text-sm mb-1">Matches</label>
                    <input type="number" id="matches" name="matches" class="w-full p-2 rounded bg-gray-700 text-white" required>
                </div>
                <div>
                    <label class="block text-sm mb-1">Duration (minutes)</label>
                    <input type="number" id="duration" name="duration" class="w-full p-2 rounded bg-gray-700 text-white" value="120" required>
                </div>

                <div>
                    <label class="block text-sm mb-1">Event Date</label>
                    <input type="datetime-local" id="event_date" name="event_date" class="w-full p-2 rounded bg-gray-700 text-white" required>
                </div>
                <div class="flex justify-end space-x-2">
                    <button type="button" onclick="closeModal()" class="bg-gray-600 px-3 py-1 rounded hover:bg-gray-700">Cancel</button>
                    <button type="submit" class="bg-green-500 px-3 py-1 rounded hover:bg-green-600">Save</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const events = <?= json_encode($events) ?>;

        function openModal(mode, id = null) {
            document.getElementById('eventModal').classList.remove('hidden');

            if (mode === 'create') {
                document.getElementById('modalTitle').innerText = 'Add Event';
                document.getElementById('eventForm').reset();
                document.getElementById('eventId').value = '';
                document.getElementById('duration').value = 120;
            } else if (mode === 'edit') {
                document.getElementById('modalTitle').innerText = 'Edit Event';
                const ev = events.find(e => e.id == id);
                if (!ev) return;

                document.getElementById('eventId').value = ev.id;
                document.getElementById('title').value = ev.title;
                document.getElementById('matches').value = ev.matches;
                document.getElementById('event_date').value = ev.event_date.replace(' ', 'T');
                document.getElementById('duration').value = ev.duration;
            }
        }

        function closeModal() {
            document.getElementById('eventModal').classList.add('hidden');
        }
    </script>


</body>

</html>