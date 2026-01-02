// matches-tabs.js
const MATCH_TAB_KEY = "matches_tab"; // key for localStorage

export function initMatchTabs() {
  const tabBtns = document.querySelectorAll(".tab-btn");
  const tabContents = document.querySelectorAll(".tab-content");

  async function loadTabData(tab) {
    const container = document.querySelector(`.tab-content[data-tab="${tab}"]`);
    if (!container) return;

    // Only load if empty
    if (container.dataset.loaded === "true") return;

    container.innerHTML = `<div class="text-center py-6 text-gray-500 dark:text-gray-400">Loading...</div>`;

    let url = "";
    if (tab === "upcoming") url = "/pages/matches?type=upcoming";
    if (tab === "live") url = "/pages/matches?type=live";

    try {
      const res = await fetch(url);
      const html = await res.text();
      container.innerHTML = html;
      container.dataset.loaded = "true";
    } catch (err) {
      container.innerHTML = `<div class="text-center py-6 text-red-500">Failed to load matches.</div>`;
      console.error(err);
    }
  }

  function activateTab(tab) {
    tabContents.forEach((c) =>
      c.classList.toggle("hidden", c.dataset.tab !== tab)
    );
    tabBtns.forEach((btn) => {
      btn.classList.remove("bg-red-600", "text-white");
      btn.classList.add(
        "bg-white",
        "dark:bg-[#252525]",
        "text-gray-700",
        "dark:text-gray-200"
      );
    });
    const activeBtn = document.querySelector(`.tab-btn[data-tab="${tab}"]`);
    if (activeBtn) activeBtn.classList.add("bg-red-600", "text-white");

    // Save active tab
    localStorage.setItem(MATCH_TAB_KEY, tab);
  }

  // Event listeners
  tabBtns.forEach((btn) => {
    btn.addEventListener("click", async () => {
      const tab = btn.dataset.tab;
      activateTab(tab);
      await loadTabData(tab);
    });
  });

  // Activate last tab or default
  const lastTab = localStorage.getItem(MATCH_TAB_KEY) || "upcoming";
  activateTab(lastTab);
  loadTabData(lastTab);
}
