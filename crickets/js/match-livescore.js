const TABS_KEY = "activeTab"; // localStorage key for active tab
const leagueKey = window.MATCH_LIVESCORE.leagueKey;

// Restore active tab from localStorage
let activeTab = localStorage.getItem(TABS_KEY) || "overview";
const tabs = document.querySelectorAll(".tab");
const panels = document.querySelectorAll(".tab-panel");

// Function to activate a tab
function activateTab(tabId) {
  tabs.forEach((t) => t.classList.remove("border-red-600"));
  panels.forEach((p) => p.classList.add("hidden"));

  const btn = document.querySelector(`.tab[data-tab="${tabId}"]`);
  const panel = document.getElementById(tabId);

  if (btn && panel) {
    btn.classList.add("border-red-600");
    panel.classList.remove("hidden");
    localStorage.setItem(TABS_KEY, tabId);
  }
}

// Lazy load standings only once
let standingsLoaded = false;

async function loadStandings() {
  const container = document.getElementById("standings-container");
  container.innerHTML = "Loading standings…";
  try {
    const res = await fetch(
      `/crickets/pages/match-standings.php?league_key=${leagueKey}`
    );
    if (!res.ok) throw new Error("Network response not OK");

    const data = await res.text();
    container.innerHTML = data;

    standingsLoaded = true;
  } catch (e) {
    container.innerHTML = "Failed to load standings.";
    console.error(e);
  }
}

// Click event for tabs
tabs.forEach((btn) => {
  btn.onclick = () => {
    activateTab(btn.dataset.tab);

    if (btn.dataset.tab === "standings" && !standingsLoaded) {
      loadStandings();
    }
  };
});

// Activate tab on page load
activateTab(activeTab);

// Auto-load standings if it was the active tab
if (activeTab === "standings" && !standingsLoaded) loadStandings();
