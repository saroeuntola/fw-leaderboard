const searchInput = document.getElementById("searchInput");
const filterCategory = document.getElementById("filterCategory");
const tableBody = document.querySelector("table tbody");

const normalize = (str) => str.toLowerCase().trim();

function filterPosts() {
  const searchValue = normalize(searchInput.value);
  const categoryValue = filterCategory ? normalize(filterCategory.value) : "";

  const rows = tableBody.querySelectorAll("tr");

  rows.forEach((row) => {
    const postNoCell = row.querySelector("td:nth-child(1)");
    const titleCell = row.querySelector("td:nth-child(3)");
    const categoryCell = row.querySelector("td:nth-child(4)");

    if (!postNoCell || !titleCell || !categoryCell) return;

    const postNo = normalize(postNoCell.textContent);
    const title = normalize(titleCell.textContent);
    const category = normalize(categoryCell.textContent);

    const matchesSearch =
      postNo.includes(searchValue) || title.includes(searchValue);
    const matchesCategory = categoryValue === "" || category === categoryValue;

    row.style.display = matchesSearch && matchesCategory ? "" : "none";
  });
}

// attach listeners
searchInput.addEventListener("input", filterPosts);
filterCategory.addEventListener("change", filterPosts);
