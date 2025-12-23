<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
ob_start();

include "../lib/checkroles.php";
include "../lib/users_lib.php";
include "../lib/banner_lib.php";

$currentUser = $_SESSION['username'] ?? 'user';
protectRoute([1, 3]);

$bannerObj = new Banner();

// Handle CRUD actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    /* CREATE BANNER */
    if (isset($_POST['create'])) {
        $title = $_POST['title'];
        $link  = $_POST['link'];
        $post_by = $currentUser;
        $status = $_POST['status'];
        $postNo = (int)$_POST['postNo'];
        $replace = isset($_POST['replacePostNo']) ? (int)$_POST['replacePostNo'] : 0;

        $image = '';
        if (!empty($_FILES['image']['name'])) {
            $uploadDir = "../uploads/banners/";
            if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);
            $filename = time() . "_" . basename($_FILES['image']['name']);
            $target   = $uploadDir . $filename;
            move_uploaded_file($_FILES['image']['tmp_name'], $target);
            $image = "uploads/banners/" . $filename;
        }

        if ($replace === 1) {
            // Swap existing postNo with new one
            $stmt = $bannerObj->db->prepare("SELECT id FROM banner WHERE postNo = ?");
            $stmt->execute([$postNo]);
            $conflict = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($conflict) {
                $tempNo = (int)$bannerObj->db->query("SELECT IFNULL(MAX(postNo),0)+1 AS tempNo FROM banner")->fetch(PDO::FETCH_ASSOC)['tempNo'];
                $stmt = $bannerObj->db->prepare("UPDATE banner SET postNo = ? WHERE id = ?");
                $stmt->execute([$tempNo, $conflict['id']]);
            }
        }

        $bannerObj->createBanner($title, $image, $link, $post_by, $status, $postNo);
    }

    /* UPDATE BANNER */
    if (isset($_POST['update'])) {
        $id    = $_POST['id'];
        $title = $_POST['title'];
        $link  = $_POST['link'];
        $post_by = $currentUser;
        $status = $_POST['status'];
        $postNo = (int)$_POST['postNo'];
        $image = $_POST['old_image'];

        if (!empty($_FILES['image']['name'])) {
            $uploadDir = "../uploads/banners/";
            if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);

            $ext      = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . "_" . time() . "." . $ext;
            $target   = $uploadDir . $filename;

            move_uploaded_file($_FILES['image']['tmp_name'], $target);
            $image = "uploads/banners/" . $filename;

            // Delete old image
            if (!empty($_POST['old_image']) && file_exists("../" . $_POST['old_image'])) {
                unlink("../" . $_POST['old_image']);
            }
        }

        $bannerObj->updateBanner($id, $title, $image, $link, $post_by, $status, $postNo);
    }

    /* DELETE BANNER */
    if (isset($_POST['delete'])) {
        $id = $_POST['id'];
        $bannerObj->deleteBanner($id);
    }

    header("Location: /admin/banner/");
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
            <h1 class="text-2xl font-bold text-white">Slideshow Banner Management</h1>
            <button class="btn bg-blue-600 border-none text-white" onclick="document.getElementById('createModal').showModal()">+ Add Banner</button>
        </div>
        <!-- Table -->
        <div class="overflow-x-auto text-white">
            <table class="table w-full">
                <thead class="bg-blue-600 text-white">
                    <tr>
                        <th>#</th>
                        <th>Index</th>
                        <th>Banner</th>
                        <th>Title</th>
                        <th>Link</th>
                        <th>Post by</th>
                        <th>Create At</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($banners as $i => $b): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td><?= htmlspecialchars($b['postNo']) ?></td>
                            <td>
                                <?php if ($b['image']): ?>
                                    <img src="../<?= htmlspecialchars($b['image']) ?>" class="h-16 w-32 object-cover rounded" loading="lazy" />
                                <?php endif; ?>
                            </td>

                            <td><?= htmlspecialchars($b['title']) ?></td>
                            <td><a href="<?= htmlspecialchars($b['link']) ?>" class="link" target="_blank"><?= htmlspecialchars($b['link']) ?></a></td>
                            <td><?= htmlspecialchars($b['post_by']) ?></td>
                            <td><?= htmlspecialchars($b['created_at']) ?></td>
                            <td>
                                <button
                                    onclick="toggleStatus(<?= $b['id'] ?>)"
                                    class="px-3 py-1 rounded text-white text-sm transition-all duration-300 cursor-pointer hover:opacity-70
                                    <?= $b['status'] == 1 ? 'bg-green-600' : 'bg-red-800' ?>">
                                    <?= $b['status'] == 1 ? 'Active' : 'Inactive' ?>
                                </button>

                            </td>
                            <td class="flex gap-2">
                                <button
                                    class="btn btn-warning"
                                    onclick="openPostNoModal(<?= $b['id'] ?>, <?= $b['postNo'] ?>)">
                                    Edit Index
                                </button>

                                <!-- Edit button -->
                                <!-- Edit button -->
                                <button class="btn btn-sm btn-warning"
                                    onclick='openEditModal(
        <?= $b["id"] ?>,
        <?= json_encode($b["title"]) ?>,
        <?= json_encode($b["link"]) ?>,
        <?= json_encode($b["image"] ?? "") ?>,
        <?= $b["status"] ?>,
        <?= $b["postNo"] ?>
    )'>
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
                <input type="text" name="title" placeholder="Title*" class="input input-bordered w-full mb-2" required />
                <input type="text" name="link" placeholder="Link*" class="input input-bordered w-full mb-2" required />

                <!-- Preview -->
                <img id="createPreview" src="" class="hidden w-full h-32 object-cover mb-2 rounded border" loading="lazy" />

                <input type="file" name="image" accept="image/*" class="file-input file-input-bordered w-full mb-4"
                    onchange="previewImage(event, 'createPreview')" required />

                <div class="mb-2">
                    <label class="font-semibold mr-4">Status:</label><br>
                    <input type="radio" name="status" value="1" checked class="">
                    <label for="">Active</label>
                    <input type="radio" name="status" value="0" class="ml-2">
                    <label for="">Inactive</label>
                </div>

                <input type="number" class="input input-bordered w-full mb-2" placeholder="Banner index*" name="postNo" id="createPostNo" oninput="checkCreatePostNo()" required>
                <p id="createPostNoError" class="text-red-500 text-sm hidden">Number already exists.</p>
                <button type="button" id="createReplaceBtn" onclick="enableCreateReplace()" class="hidden bg-orange-600 text-white px-4 py-2 rounded cursor-pointer">
                    Replace
                </button>
                <input type="hidden" name="replacePostNo" id="createReplaceFlag" value="0">

                <div class="modal-action">
                    <button type="submit" name="create" class="btn btn-primary" id="createSaveBtn">Save</button>
                    <button type="button" class="btn" onclick="createModal.close()">Cancel</button>
                </div>
            </form>
        </div>
    </dialog>
    <!-- Edit Modal Post -->
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


                <div class="mb-4 hidden">
                    <label class="font-semibold mr-2">Status:</label>
                    <input type="radio" name="status" id="statusActive" value="1">
                    <label for="statusActive">Active</label>

                    <input type="radio" name="status" id="statusInactive" value="0" class="ml-2">
                    <label for="statusInactive">Inactive</label>
                </div>

                <input hidden type="number" name="postNo" id="editPostNo" class="input input-bordered w-full mb-2" required>

                <div class=" modal-action">
                    <button type="submit" name="update" class="btn btn-primary">Update</button>
                    <button type="button" class="btn" onclick="editModal.close()">Cancel</button>
                </div>
            </form>
        </div>
    </dialog>


    <!-- Edit Modal Index -->
    <dialog id="postNoModal" class="modal">
        <div class="modal-box bg-sky-900 text-white">
            <h3 class="font-bold text-lg mb-4">Update Post No.</h3>

            <form id="postNoForm" onsubmit="return false;">
                <!-- REQUIRED -->
                <input type="hidden" id="modalPostId">

                <input type="number"
                    id="modalPostNo"
                    class="input input-bordered w-full text-black"
                    oninput="checkModalPostNo()">

                <small id="modalPostNoError" class="text-red-500 hidden">
                    Number already exists
                </small>

                <button type="button"
                    id="modalReplaceBtn"
                    class="btn btn-warning hidden mt-2"
                    onclick="enableModalReplace()">
                    Replace
                </button>

                <!-- FLAG -->
                <input type="hidden" id="modalReplaceFlag" value="0">

                <div class="modal-action justify-start">
                    <button type="button"
                        class="btn btn-primary"
                        onclick="saveModalPostNo()">
                        Save
                    </button>

                    <button type="button"
                        class="btn"
                        onclick="postNoModal.close()">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </dialog>
    <script src="banner.js"></script>
</body>

</html>