let currentPostId = null;

function openPostNoModal(id, postNo) {
  currentPostId = id;
  modalPostId.value = id;
  modalPostNo.value = postNo;
  modalPostNoError.classList.add("hidden");
  replaceBtn.classList.add("hidden");
  postNoModal.showModal();
}

function checkModalPostNo() {
  const value = modalPostNo.value;

  fetch("check_postno", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: `postNo=${value}&id=${currentPostId}`,
  })
    .then((res) => res.json())
    .then((data) => {
      if (data.exists) {
        modalPostNoError.classList.remove("hidden");
        replaceBtn.classList.remove("hidden");
        modalSaveBtn.disabled = true;
      } else {
        modalPostNoError.classList.add("hidden");
        replaceBtn.classList.add("hidden");
        modalSaveBtn.disabled = false;
      }
    });
}

function replacePostNo() {
  const postNo = modalPostNo.value;

  fetch("replace_postno", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: `id=${currentPostId}&postNo=${postNo}`,
  })
    .then((res) => res.json())
    .then((data) => {
      if (data.success) {
        location.reload();
      } else {
        alert("Replace failed");
      }
    });
}

function saveModalPostNo() {
  const postNo = document.getElementById("modalPostNo").value;

  fetch("update_postno", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: `id=${currentPostId}&postNo=${postNo}`,
  })
    .then((res) => res.json())
    .then((data) => {
      if (data.success) {
        // Update table
        const rowInput = document.querySelector(`#postNoText${currentPostId}`);
        if (rowInput) rowInput.innerText = postNo;
        document.getElementById("postNoModal").close();
        location.reload();
      } else {
        alert(data.message || "Failed to update");
      }
    });
}

function toggleStatus(id) {
  fetch("toggle_posts_status", {
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
