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
  const preview = document.getElementById("editPreview");
  preview.src = "../" + image;
  preview.classList.remove("hidden");
  // Set status
  if (parseInt(status) === 1) {
    document.getElementById("statusActive").checked = true;
  } else {
    document.getElementById("statusInactive").checked = true;
  }
 document.getElementById("editPostNo").value = postNo;
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