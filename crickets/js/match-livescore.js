const TABS_KEY = "activeTab"; // optional: still keep for clicks if you want
const leagueKey = window.MATCH_LIVESCORE.leagueKey;

const tabs = document.querySelectorAll(".tab");
const panels = document.querySelectorAll(".tab-panel");

// Always start with overview
let activeTab = "overview";

// Function to activate a tab
function activateTab(tabId) {
  tabs.forEach((t) => t.classList.remove("border-red-600"));
  panels.forEach((p) => p.classList.add("hidden"));

  const btn = document.querySelector(`.tab[data-tab="${tabId}"]`);
  const panel = document.getElementById(tabId);

  if (btn && panel) {
    btn.classList.add("border-red-600");
    panel.classList.remove("hidden");
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
    container.innerHTML = await res.text();
    standingsLoaded = true;
  } catch (e) {
    container.innerHTML = "Failed to load standings.";
    console.error(e);
  }
}

// Click event for tabs
tabs.forEach((btn) => {
  btn.addEventListener("click", () => {
    activateTab(btn.dataset.tab);

    if (btn.dataset.tab === "standings" && !standingsLoaded) {
      loadStandings();
    }
  });
});

// ---------- Page load ----------
document.addEventListener("DOMContentLoaded", () => {
  // Always activate overview on page load
  activateTab("overview");
});
