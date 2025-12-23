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
function openEditModal(id, title, link, image, status, postNo) {
  document.getElementById("editId").value = id;
  document.getElementById("editTitle").value = title;
  document.getElementById("editLink").value = link;
  document.getElementById("editOldImage").value = image;
  document.getElementById("editPreview").src = image ? "../" + image : "";

  // Status (if you want to show it)
  document.getElementById("statusActive").checked = status == 1;
  document.getElementById("statusInactive").checked = status == 0;

  // ✅ Fill postNo
  document.getElementById("editPostNo").value = postNo;

  // Show modal
  document.getElementById("editModal").showModal();
}



    function toggleStatus(id) {
      fetch("toggle_banner_status", {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
        body: `id=${id}`,
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            location.reload();
          } else {
            alert("Failed to toggle status.");
          }
        })
        .catch((error) => {
          console.error("Error:", error);
          alert("An error occurred.");
        });
    }