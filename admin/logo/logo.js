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

  function openEditModal(id, name, link, image, status) {
    document.getElementById("editId").value = id;
    document.getElementById("editName").value = name;
    document.getElementById("editLink").value = link;
    document.getElementById("editOldImage").value = image;
    document.getElementById("editPreview").src = "../" + image;
    document.getElementById("editPreview").classList.remove("hidden");

  if (parseInt(status) === 1) {
    document.getElementById("statusActive").checked = true;
  } else {
    document.getElementById("statusInactive").checked = true;
  }

    document.getElementById("editModal").showModal();
  }

function toggleStatus(id) {
  fetch("toggle_logo_status", {
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
