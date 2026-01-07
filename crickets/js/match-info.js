const tabs = document.querySelectorAll(".tab");
const panels = document.querySelectorAll(".tab-panel");
const leagueKey = window.MATCH_INFO.leagueKey;

// Remove any previous saved tab
localStorage.removeItem("activeTab");

let standingsLoaded = false;
let overviewLoaded = false;

async function loadOverview() {
  const container = document.getElementById("overview-container");
  if (!container || overviewLoaded) return;
  container.innerHTML = "Loading overview…";
  try {
    const res = await fetch(
      `/crickets/pages/match-overview.php?league_key=${leagueKey}`
    );
    if (!res.ok) throw new Error("Network response not OK");
    container.innerHTML = await res.text();
    overviewLoaded = true;
  } catch (e) {
    container.innerHTML = "Failed to load overview.";
    console.error(e);
  }
}

async function loadStandings() {
  const container = document.getElementById("standings-container");
  if (!container || standingsLoaded) return;
  container.innerHTML = "Loading standings…";
  try {
    const res = await fetch(
      `/crickets/pages/match-standings.php?league_key=${leagueKey}`
    );
    if (!res.ok) throw new Error("Network response not OK");
    container.innerHTML = await res.text();
    standingsLoaded = true;
  } catch (e) {
    container.innerHTML = "Failed to load standings.";
    console.error(e);
  }
}

function activateTab(tabId) {
  tabs.forEach((t) => t.classList.remove("border-blue-600"));
  panels.forEach((p) => p.classList.add("hidden"));

  const btn = document.querySelector(`.tab[data-tab="${tabId}"]`);
  const panel = document.getElementById(tabId);

  if (btn && panel) {
    btn.classList.add("border-blue-600");
    panel.classList.remove("hidden");

    // Only save tab when user clicks it
    // localStorage.setItem("activeTab", tabId);

    if (tabId === "overview") loadOverview();
    if (tabId === "standings") loadStandings();
  }
}

// Handle tab clicks
tabs.forEach((btn) => {
  btn.addEventListener("click", () => {
    activateTab(btn.dataset.tab);
  });
});

// ---------- Page load ----------
document.addEventListener("DOMContentLoaded", () => {
  // Force "overview" tab on every page load
  activateTab("overview");
});
