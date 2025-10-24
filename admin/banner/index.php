<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
include "../lib/checkroles.php";
include "../lib/users_lib.php";
include "../lib/banner_lib.php";

protectPathAccess();
$bannerObj = new Banner();
// Handle CRUD actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create'])) {
        $title = $_POST['title'];
        $link  = $_POST['link'];
        $image = '';

        if (!empty($_FILES['image']['name'])) {
            $uploadDir = "../uploads/banners/";
            if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);
            $filename = time() . "_" . basename($_FILES['image']['name']);
            $target   = $uploadDir . $filename;
            move_uploaded_file($_FILES['image']['tmp_name'], $target);
            $image = "uploads/banners/" . $filename;
        }

        $bannerObj->createBanner($title, $image, $link);
    }
    if (isset($_POST['update'])) {
        $id    = $_POST['id'];
        $title = $_POST['title'];
        $link  = $_POST['link'];
        $image = $_POST['old_image'];

        if (!empty($_FILES['image']['name'])) {
            $uploadDir = "../uploads/banners/";
            if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);

            $ext      = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . "_" . time() . "." . $ext;
            $target   = $uploadDir . $filename;

            move_uploaded_file($_FILES['image']['tmp_name'], $target);
            $image = "uploads/banners/" . $filename;

            // 🔥 Delete old image file (optional, to save space)
            if (!empty($_POST['old_image']) && file_exists("../" . $_POST['old_image'])) {
                unlink("../" . $_POST['old_image']);
            }
        }

        $bannerObj->updateBanner($id, $title, $image, $link);
    }

    if (isset($_POST['delete'])) {
        $id = $_POST['id'];
        $bannerObj->deleteBanner($id);
    }
    header("Location: /v2/admin/banner/");
    exit;
}
$banners = $bannerObj->getBanner();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Banner CRUD</title>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@5" rel="stylesheet" type="text/css" />
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="flex h-screen bg-gray-900">
    <!-- Sidebar -->
    <?php include "../include/sidebar.php" ?>
    <!-- Main Content -->
    <main class="flex-1 ml-64 p-6 transition-all duration-300" id="main-content">
        <div class="flex justify-between mb-4">
            <h1 class="text-2xl font-bold">Slideshow Banner Management</h1>
            <button class="btn bg-blue-600 border-none text-white" onclick="document.getElementById('createModal').showModal()">+ Add Banner</button>
        </div>
        <!-- Table -->
        <div class="overflow-x-auto text-white">
            <table class="table w-full">
                <thead class="bg-blue-600 text-white">
                    <tr>
                        <th>#</th>
                        <th>Banner</th>
                        <th>Title</th>
                        <th>Link</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($banners as $i => $b): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td>
                                <?php if ($b['image']): ?>
                                    <img src="../<?= htmlspecialchars($b['image']) ?>" class="h-16 w-32 object-cover rounded" loading="lazy" />
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($b['title']) ?></td>
                            <td><a href="<?= htmlspecialchars($b['link']) ?>" class="link" target="_blank"><?= htmlspecialchars($b['link']) ?></a></td>
                            <td class="flex gap-2">
                                <!-- Edit button -->
                                <button class="btn btn-sm btn-warning"
                                    onclick="openEditModal(<?= $b['id'] ?>, '<?= htmlspecialchars($b['title'], ENT_QUOTES) ?>', '<?= htmlspecialchars($b['link'], ENT_QUOTES) ?>', '<?= $b['image'] ?>')">
                                    Edit
                                </button>
                                <!-- Delete -->
                                <form method="POST" onsubmit="return confirm('Delete this banner?')">
                                    <input type="hidden" name="id" value="<?= $b['id'] ?>">
                                    <button type="submit" name="delete" class="btn btn-sm btn-error">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
    <!-- Create Modal -->
    <dialog id="createModal" class="modal">
        <div class="modal-box">
            <h3 class="font-bold text-lg mb-4">Add Banner</h3>
            <form method="POST" enctype="multipart/form-data">
                <input type="text" name="title" placeholder="Title" class="input input-bordered w-full mb-2" required />
                <input type="text" name="link" placeholder="Link" class="input input-bordered w-full mb-2" required />

                <!-- Preview -->
                <img id="createPreview" src="" class="hidden w-full h-32 object-cover mb-2 rounded border" loading="lazy" />

                <input type="file" name="image" accept="image/*" class="file-input file-input-bordered w-full mb-4"
                    onchange="previewImage(event, 'createPreview')" required />

                <div class="modal-action">
                    <button type="submit" name="create" class="btn btn-primary">Save</button>
                    <button type="button" class="btn" onclick="createModal.close()">Cancel</button>
                </div>
            </form>
        </div>
    </dialog>

    <!-- Edit Modal -->
    <dialog id="editModal" class="modal">
        <div class="modal-box">
            <h3 class="font-bold text-lg mb-4">Edit Banner</h3>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id" id="editId">
                <input type="hidden" name="old_image" id="editOldImage">

                <input type="text" name="title" id="editTitle" class="input input-bordered w-full mb-2" required />
                <input type="text" name="link" id="editLink" class="input input-bordered w-full mb-2" required />

                <!-- Preview -->
                <img id="editPreview" src="" class="w-full h-32 object-cover mb-2 rounded border" loading="lazy" />

                <input type="file" name="image" accept="image/*" class="file-input file-input-bordered w-full mb-4"
                    onchange="previewImage(event, 'editPreview')" />

                <div class="modal-action">
                    <button type="submit" name="update" class="btn btn-primary">Update</button>
                    <button type="button" class="btn" onclick="editModal.close()">Cancel</button>
                </div>
            </form>
        </div>
    </dialog>


    <script>
        function previewImage(event, previewId) {
            const file = event.target.files[0];
            const preview = document.getElementById(previewId);

            if (file) {
                preview.src = URL.createObjectURL(file);
                preview.classList.remove("hidden");
            } else {
                preview.src = "";
                preview.classList.add("hidden");
            }
        }

        function openEditModal(id, title, link, image) {
            document.getElementById('editId').value = id;
            document.getElementById('editTitle').value = title;
            document.getElementById('editLink').value = link;
            document.getElementById('editOldImage').value = image;
            document.getElementById('editPreview').src = "../" + image;
            document.getElementById('editPreview').classList.remove("hidden");
            document.getElementById('editModal').showModal();
        }
    </script>

    <!-- Sidebar toggle script -->
    <script>
        const toggleBtn = document.getElementById('toggleSidebar');
        const sidebar = document.getElementById('sidebar');
        if (toggleBtn) {
            toggleBtn.addEventListener('click', () => {
                sidebar.classList.toggle('-translate-x-full');
            });
        }
    </script>
</body>

</html>