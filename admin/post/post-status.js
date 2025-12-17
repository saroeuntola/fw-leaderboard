
let currentPostId = 0;

function openPostNoModal(id, postNo) {
    currentPostId = id;
    document.getElementById('modalPostId').value = id;
    const input = document.getElementById('modalPostNo');
    input.value = postNo;

    document.getElementById('modalPostNoError').classList.add('hidden');
    document.getElementById('modalSaveBtn').disabled = false;

    document.getElementById('postNoModal').showModal();
}

function checkModalPostNo() {
    const value = document.getElementById('modalPostNo').value;
    const error = document.getElementById('modalPostNoError');
    const saveBtn = document.getElementById('modalSaveBtn');

    if (value === '') return;

    fetch('check_postno', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `postNo=${value}&id=${currentPostId}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.exists) {
            error.classList.remove('hidden');
            saveBtn.disabled = true;
        } else {
            error.classList.add('hidden');
            saveBtn.disabled = false;
        }
    });
}

function saveModalPostNo() {
    const postNo = document.getElementById('modalPostNo').value;

    fetch('update_postno', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `id=${currentPostId}&postNo=${postNo}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            // Update table
            const rowInput = document.querySelector(`#postNoText${currentPostId}`);
            if (rowInput) rowInput.innerText = postNo;

            document.getElementById('postNoModal').close();
        } else {
            alert(data.message || 'Failed to update');
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
