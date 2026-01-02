const TAB_KEY = "match_active_tab";

export function initMatchTabs(seriesId, matchId) {
  const tabBtns = document.querySelectorAll(".tab-btn");
  const tabContents = document.querySelectorAll(".tab-content");
  const matchEl = document.querySelector("html[data-match-id]");

  if (!matchEl) return;

  // Activate tab
  function activateTab(tab) {
    tabContents.forEach((c) =>
      c.classList.toggle("hidden", c.dataset.tab !== tab)
    );
    tabBtns.forEach((b) => {
      b.classList.remove("bg-red-600", "text-white");
      b.classList.add(
        "bg-white",
        "dark:bg-[#252525]",
        "text-gray-700",
        "dark:text-gray-200"
      );
    });
    const activeBtn = document.querySelector(`.tab-btn[data-tab="${tab}"]`);
    if (activeBtn) activeBtn.classList.add("bg-red-600", "text-white");

    localStorage.setItem(TAB_KEY, tab);
  }

  // Fetch tab content
  async function fetchTab(tab) {
    let url = "";
    if (tab === "scorecard") {
      url = `/crickets/pages/scorecard?id=${matchId}`;
    } else if (tab === "standing") {
      url = `/crickets/pages/standing.php?series_id=${seriesId}`;
    } else if (tab === "points") {
      url = `/crickets/pages/points.php?id=${matchId}`;
    } else return;

    const container = document.querySelector(`.tab-content[data-tab="${tab}"]`);
    if (!container) return;

    try {
      const res = await fetch(url);
      const html = await res.text();
      container.innerHTML = html;

      // If scorecard, update header scores without flashing
      if (tab === "scorecard") updateHeaderScores(container);
    } catch (err) {
      container.innerHTML = `<div class="text-center py-6 text-red-500">Failed to load ${tab}.</div>`;
      console.error("Fetch error:", err);
    }
  }

  // Update header scores and live probability
  function updateHeaderScores(container) {
    const liveScoreEl = document.querySelectorAll(".team-score-container");
    const probEl = document.querySelector(".live-prob-container");

    if (!liveScoreEl.length) return;

    const newScores = container.querySelectorAll(".team-score-container");
    newScores.forEach((el, idx) => {
      if (liveScoreEl[idx]) liveScoreEl[idx].innerHTML = el.innerHTML;
    });

    const newProb = container.querySelector(".live-prob-container");
    if (newProb && probEl) probEl.innerHTML = newProb.innerHTML;
  }

  // Tab click events
  tabBtns.forEach((btn) => {
    btn.addEventListener("click", async () => {
      const tab = btn.dataset.tab;
      activateTab(tab);
      await fetchTab(tab);
    });
  });

  // Restore last active tab
  const lastTab = localStorage.getItem(TAB_KEY) || "scorecard";
  activateTab(lastTab);
  fetchTab(lastTab);

  // Auto-refresh scorecard every 30s
  setInterval(() => {
    const activeTab = localStorage.getItem(TAB_KEY);
    if (activeTab === "scorecard") fetchTab("scorecard");
  }, 30000);
}
