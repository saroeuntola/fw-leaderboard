<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
ob_start();
include "../lib/checkroles.php";
include "../lib/users_lib.php";
protectRoute([1, 3]);
include "../lib/brand_lib.php";

$brandObj = new Brand();

// Handle CRUD actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create'])) {
        $name = $_POST['brand_name'];
        $link = $_POST['link'];
        $image = '';

        if (!empty($_FILES['brand_image']['name'])) {
            $uploadDir = "../uploads/brands/";
            if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);
            $filename = time() . "_" . basename($_FILES['brand_image']['name']);
            $target   = $uploadDir . $filename;
            move_uploaded_file($_FILES['brand_image']['tmp_name'], $target);
            $image = "uploads/brands/" . $filename;
        }

        $brandObj->createBrand($name, $image, $link);
    }

    if (isset($_POST['update'])) {
        $id    = $_POST['id'];
        $name  = $_POST['brand_name'];
        $link  = $_POST['link'];
        $image = $_POST['old_image'];

        if (!empty($_FILES['brand_image']['name'])) {
            $uploadDir = "../uploads/brands/";
            if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);
            $filename = time() . "_" . basename($_FILES['brand_image']['name']);
            $target   = $uploadDir . $filename;
            move_uploaded_file($_FILES['brand_image']['tmp_name'], $target);
            $image = "uploads/brands/" . $filename;

            // remove old file
            if (!empty($_POST['old_image']) && file_exists("../" . $_POST['old_image'])) {
                unlink("../" . $_POST['old_image']);
            }
        }

        $brandObj->updateBrand($id, $name, $image, $link);
    }

    if (isset($_POST['delete'])) {
        $id = $_POST['id'];
        $brand = $brandObj->getBrandById($id);
        if ($brand && !empty($brand['brand_image']) && file_exists("../" . $brand['brand_image'])) {
            unlink("../" . $brand['brand_image']);
        }
        $brandObj->deleteBrand($id);
    }

    header("Location: ./");
    exit;
}

$brands = $brandObj->getBrand();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Brand CRUD</title>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@5" rel="stylesheet" type="text/css" />
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>

<body class="flex h-screen bg-gray-900">
    <!-- Sidebar -->
    <?php include "../include/sidebar.php" ?>

    <!-- Main content -->
    <main class="flex-1 ml-64 p-6 transition-all duration-300 text-white" id="main-content">
        <div class="flex justify-between mb-4">
            <h1 class="text-2xl font-bold">Logo Management</h1>
            <button class="btn border-none bg-sky-600 text-white" onclick="document.getElementById('createModal').showModal()">+ Add Brand</button>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="table table-zebra w-full">
                <thead class="text-white bg-sky-600">
                    <tr>
                        <th>#</th>
                        <th>Logo</th>
                        <th>Name</th>
                        <th>Link</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($brands as $i => $b): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td>
                                <?php if ($b['brand_image']): ?>
                                    <img src="../<?= htmlspecialchars($b['brand_image']) ?>" class="h-16 w-32 object-contain rounded border" loading="lazy" />
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($b['brand_name']) ?></td>

                            <td><a href="<?= htmlspecialchars($b['link']) ?>" class="link" target="_blank"><?= htmlspecialchars($b['link']) ?></a></td>
                            <td class="flex gap-2">
                                <!-- Edit -->
                                <button class="btn btn-sm btn-warning"
                                    onclick="openEditModal(<?= $b['id'] ?>, '<?= htmlspecialchars($b['brand_name'], ENT_QUOTES) ?>', '<?= htmlspecialchars($b['link'], ENT_QUOTES) ?>', '<?= $b['brand_image'] ?>')">
                                    Edit
                                </button>
                                <!-- Delete -->
                                <form method="POST" onsubmit="return confirm('Delete this brand?')">
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
            <h3 class="font-bold text-lg mb-4">Add Logo</h3>
            <form method="POST" enctype="multipart/form-data">

                <input type="text" name="brand_name" placeholder="Logo Name*" class="input input-bordered w-full mb-2" required />
                <input type="text" name="link" placeholder="Link (Optional)" class="input input-bordered w-full mb-2" />

                <!-- Preview -->
                <img id="createPreview" src="" class="hidden w-full h-32 object-contain mb-2 rounded border" loading="lazy" />

                <input type="file" name="brand_image" accept="image/*"
                    class="file-input file-input-bordered w-full mb-4"
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
            <h3 class="font-bold text-lg mb-4">Edit Logo</h3>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id" id="editId">
                <input type="hidden" name="old_image" id="editOldImage">
                <div>
                    <label for="">Logo Name*</label>
                    <input type="text" name="brand_name" id="editName" class="input input-bordered w-full mb-2 mt-2" required />
                </div>


                <div>
                    <label for="">Link (Optional)</label>
                    <input type="text" name="link" id="editLink" class="input input-bordered w-full mb-2 mt-2" />
                </div>


                <!-- Preview -->
                <img id="editPreview" src="" class="w-full h-32 object-contain mb-2 rounded border" loading="lazy" />

                <input type="file" name="brand_image" accept="image/*"
                    class="file-input file-input-bordered w-full mb-4"
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

        function openEditModal(id, name, link, image) {
            document.getElementById('editId').value = id;
            document.getElementById('editName').value = name;
            document.getElementById('editLink').value = link;
            document.getElementById('editOldImage').value = image;
            document.getElementById('editPreview').src = "../" + image;
            document.getElementById('editPreview').classList.remove("hidden");
            document.getElementById('editModal').showModal();
        }
    </script>
</body>

</html>