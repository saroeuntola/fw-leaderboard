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


     const modalPostId = document.getElementById("modalPostId");
     const modalPostNo = document.getElementById("modalPostNo");
     const modalPostNoError = document.getElementById("modalPostNoError");
     const modalReplaceBtn = document.getElementById("modalReplaceBtn");
     const modalReplaceFlag = document.getElementById("modalReplaceFlag");

     /* OPEN MODAL */
     function openPostNoModal(id, postNo) {
       modalPostId.value = id;
       modalPostNo.value = postNo;

       modalReplaceFlag.value = 0;
       modalPostNoError.classList.add("hidden");
       modalReplaceBtn.classList.add("hidden");

       document.getElementById("postNoModal").showModal();
       console.log(modalPostId.value, modalPostNo.value);
     }

     /* CHECK CONFLICT */
     function checkModalPostNo() {
       const postNo = modalPostNo.value;
       const id = modalPostId.value;

       if (!postNo) {
         modalPostNoError.classList.add("hidden");
         modalReplaceBtn.classList.add("hidden");
         modalReplaceFlag.value = 0;
         return;
       }

       fetch("check_postno", {
         method: "POST",
         headers: {
           "Content-Type": "application/x-www-form-urlencoded",
         },
         body: `postNo=${postNo}&id=${id}`,
       })
         .then((r) => r.json())
         .then((d) => {
           if (d.exists) {
             modalPostNoError.classList.remove("hidden");
             modalReplaceBtn.classList.remove("hidden");
             modalReplaceFlag.value = 0;
           } else {
             modalPostNoError.classList.add("hidden");
             modalReplaceBtn.classList.add("hidden");
             modalReplaceFlag.value = 0;
           }
         });
     }

     /* UI ONLY */
     function enableModalReplace() {
       modalReplaceFlag.value = 1;
       modalPostNoError.classList.add("hidden");
       modalReplaceBtn.classList.add("hidden");
     }

     /* SAVE */
     function saveModalPostNo() {
       const id = modalPostId.value;
       const postNo = modalPostNo.value;
       const replace = modalReplaceFlag.value;

       console.log(
         "SAVE DEBUG: id=",
         id,
         "postNo=",
         postNo,
         "replace=",
         replace
       );

       // Check for invalid input (allow 0 as valid postNo)
       if (!id || postNo === "" || postNo === null) {
         alert("Invalid input");
         console.log("Invalid input detected:", {
           id,
           postNo,
           replace,
         });
         return;
       }

       fetch("update_postno", {
         method: "POST",
         headers: {
           "Content-Type": "application/x-www-form-urlencoded",
         },
         body: `id=${encodeURIComponent(id)}&postNo=${encodeURIComponent(
           postNo
         )}&replace=${encodeURIComponent(replace)}`,
       })
         .then((r) => r.json())
         .then((d) => {
           console.log("UPDATE RESPONSE:", d);
           if (d.success) {
             location.reload();
           } else {
             alert(d.message || "Update failed");
           }
         })
         .catch((err) => {
           console.error("Request error:", err);
           alert("Request error");
         });
     }

       // Create modal elements
        let createPostNo = document.getElementById('createPostNo');
        let createPostNoError = document.getElementById('createPostNoError');
        let createReplaceBtn = document.getElementById('createReplaceBtn');
        let createReplaceFlag = document.getElementById('createReplaceFlag');

        // Live check on input
        function checkCreatePostNo() {
            const value = createPostNo.value;

            if (!value) {
                createPostNoError.classList.add('hidden');
                createReplaceBtn.classList.add('hidden');
                createReplaceFlag.value = 0;
                return;
            }

            fetch('check_postno', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `postNo=${value}`
                })
                .then(r => r.json())
                .then(d => {
                    if (d.exists) {
                        createPostNoError.classList.remove('hidden');
                        createReplaceBtn.classList.remove('hidden');
                        createReplaceFlag.value = 0;
                    } else {
                        createPostNoError.classList.add('hidden');
                        createReplaceBtn.classList.add('hidden');
                        createReplaceFlag.value = 0;
                    }
                });
        }

        // Enable replace (UI only)
        function enableCreateReplace() {
            createReplaceFlag.value = 1;
            createPostNoError.classList.add('hidden');
            createReplaceBtn.classList.add('hidden');
        }