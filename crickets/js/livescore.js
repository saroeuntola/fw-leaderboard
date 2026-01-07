/* ================= SCROLLABLE CONTAINERS ================= */
const scrollContainers = [
  "upcoming-scroll",
  "livescore-scroll",
  "result-scroll",
];

/* ================= TABS ================= */
const tabs = document.querySelectorAll(".tab-btn");
const panels = document.querySelectorAll(".tab-panel");

function showTab(tab) {
  tabs.forEach((b) => b.classList.remove("tab-active"));
  panels.forEach((p) => p.classList.add("hidden"));

  const btn = document.querySelector(`[data-tab="${tab}"]`);
  const pnl = document.getElementById(tab);

  if (btn && pnl) {
    btn.classList.add("tab-active");
    pnl.classList.remove("hidden");
    localStorage.setItem("cric_tab", tab);
    updateAllArrows();
  }
}

tabs.forEach((btn) => {
  btn.addEventListener("click", () => showTab(btn.dataset.tab));
});

// Restore last tab
showTab(localStorage.getItem("cric_tab") || "upcoming");

/* ================= SCROLL ARROWS ================= */
function updateArrows(id) {
  const container = document.getElementById(id);
  if (!container || container.offsetParent === null) return;

  const wrapper = container.parentElement;
  const left = wrapper.querySelector(".scroll-arrow.left");
  const right = wrapper.querySelector(".scroll-arrow.right");
  if (!left || !right) return;

  if (container.scrollWidth <= container.clientWidth + 5) {
    left.style.display = right.style.display = "none";
  } else {
    left.style.display = right.style.display = "block";
  }

  left.disabled = container.scrollLeft <= 0;
  right.disabled =
    container.scrollLeft + container.clientWidth >= container.scrollWidth - 1;
}

function updateAllArrows() {
  scrollContainers.forEach(updateArrows);
}

function scrollContainer(id, direction) {
  const el = document.getElementById(id);
  if (!el) return;

  el.scrollBy({
    left: direction * el.clientWidth * 0.8,
    behavior: "smooth",
  });

  setTimeout(() => updateArrows(id), 200);
}

/* ========== SAFE UPDATE WITH FILTERED CARDS ========== */
function updateContainerCards(id, newHTML) {
  const container = document.getElementById(id);
  if (!container) return;

  // Preserve height to prevent layout jump
  const currentHeight = container.offsetHeight;
  container.style.minHeight = currentHeight + "px";

  container.innerHTML = newHTML;
  container.scrollLeft = 0;

  requestAnimationFrame(() => {
    requestAnimationFrame(() => {
      updateArrows(id);
      container.style.minHeight = "";
    });
  });
}

/* ================= SERIES / LEAGUE FILTER ================= */
document.addEventListener("click", (e) => {
  const btn = e.target.closest(".series-tab");
  if (!btn) return;

  const panel = btn.closest(".tab-panel");
  const series = btn.dataset.series;

  // Activate clicked series tab
  panel.querySelectorAll(".series-tab").forEach((b) =>
    b.classList.remove("series-active")
  );
  btn.classList.add("series-active");

  // Show/hide entire <a> wrapper flex items
  panel.querySelectorAll(".match-card").forEach((card) => {
    const wrapper = card.closest("a"); // get the flex item
    if (!wrapper) return;

    if (series === "all" || card.dataset.league === series) {
      wrapper.classList.remove("hidden");
    } else {
      wrapper.classList.add("hidden");
    }
  });

  // Reset scroll and update arrows
  const scroll = panel.querySelector('[id$="scroll"]');
  if (scroll) {
    scroll.scrollLeft = 0;
    updateArrows(scroll.id);
  }
});

/* ================= DRAG TO SCROLL ================= */
document
  .querySelectorAll(".series-scroll, .tab-panel .overflow-x-auto")
  .forEach((container) => {
    let isDown = false;
    let startX = 0;
    let startScroll = 0;

    container.style.userSelect = "none";
    container.style.cursor = "grab";

    container.addEventListener("mousedown", (e) => {
      if (e.button !== 0) return; // left mouse only
      isDown = true;
      container.classList.add("cursor-grabbing");
      startX = e.clientX;
      startScroll = container.scrollLeft;
    });

    document.addEventListener("mousemove", (e) => {
      if (!isDown) return;
      e.preventDefault();
      const dx = e.clientX - startX;
      container.scrollLeft = startScroll - dx * 1.3;
      updateAllArrows();
    });

    document.addEventListener("mouseup", () => {
      if (!isDown) return;
      isDown = false;
      container.classList.remove("cursor-grabbing");
      updateAllArrows();
    });
  });

/* ================= AUTO INIT ================= */
window.addEventListener("resize", updateAllArrows);
window.addEventListener("DOMContentLoaded", updateAllArrows);
updateAllArrows();
