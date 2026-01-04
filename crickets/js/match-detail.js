document.addEventListener("DOMContentLoaded", () => {
  console.log("match-detail.js loaded");

  const tabs = document.querySelectorAll(".tab-btn");
  const panels = document.querySelectorAll(".tab-panel");

  // ✅ Get data from PHP
  const matchId = window.MATCH_DETAIL?.matchId || "";
  const seriesId = window.MATCH_DETAIL?.seriesId || "";

  function showTab(tabName) {
    console.log("showTab:", tabName);

    // Highlight active tab
    tabs.forEach((b) => b.classList.remove("border-red-600"));
    const activeBtn = document.querySelector(`.tab-btn[data-tab="${tabName}"]`);
    if (activeBtn) activeBtn.classList.add("border-red-600");

    // Hide all panels
    panels.forEach((p) => p.classList.add("hidden"));

    const panel = document.getElementById(tabName);
    if (!panel) return;
    panel.classList.remove("hidden");

    // Lazy load
    if (tabName !== "live" && panel.dataset.loaded !== "true") {
      panel.innerHTML = `
                <div class="flex justify-center items-center py-10">
                    <div class="w-8 h-8 border-4 border-t-red-600 border-gray-300 rounded-full animate-spin"></div>
                </div>
            `;

      let url = "";
      if (tabName === "standings") {
        url = `/crickets/pages/match-standings?series_id=${seriesId}`;
      } else if (tabName === "match-points") {
        url = `/crickets/pages/match-match-points?id=${matchId}`;
      } else if (tabName === "squad") {
        url = `/crickets/pages/match-squad?id=${matchId}`;
      }

      console.log("Fetching:", url);

      fetch(url)
        .then((res) => res.text())
        .then((html) => {
          panel.innerHTML = html;
          panel.dataset.loaded = "true";
        })
        .catch((err) => {
          console.error(err);
          panel.innerHTML = `
                        <div class="text-red-500 text-center py-10">
                            Failed to load.
                            <button class="underline" onclick="location.reload()">Retry</button>
                        </div>
                    `;
        });
    }

    localStorage.setItem("activeTab", tabName);
  }

  // Restore last tab
  const lastTab = localStorage.getItem("activeTab") || "live";
  showTab(lastTab);

  tabs.forEach((btn) => {
    btn.addEventListener("click", () => showTab(btn.dataset.tab));
  });
});
