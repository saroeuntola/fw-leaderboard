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

    // Run initDots after short delay to ensure cards are rendered
    setTimeout(() => {
      initDots('upcoming-scroll', 'upcoming-dots');
      initDots('livescore-scroll', 'livescore-dots');
      initDots('result-scroll', 'result-dots');
    }, 100); // 100ms is usually enough
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
/* ================= SERIES / LEAGUE FILTER ================= */
const hotEmptyMessage = document.createElement("div");
hotEmptyMessage.className = "text-center text-red-500 text-sm py-3";
hotEmptyMessage.innerText = "🔥 Hot League Not Available";

document.addEventListener("click", (e) => {
  const btn = e.target.closest(".series-tab");
  if (!btn) return;

  const panel = btn.closest(".tab-panel");
  const series = btn.dataset.series;

  // ✅ ADD THIS PART (important)
  panel.querySelectorAll(".series-tab").forEach((b) =>
    b.classList.remove("series-active")
  );
  btn.classList.add("series-active");

  let visibleCount = 0;

  panel.querySelectorAll(".match-card").forEach((card) => {
    const wrapper = card.closest("a");
    if (!wrapper) return;

    const league = card.dataset.league;
    const isHot = card.dataset.hot === "1";

    if (series === "all") {
      wrapper.classList.remove("hidden");
      visibleCount++;
    } 
    else if (series === "Hot") {
      if (isHot) {
        wrapper.classList.remove("hidden");
        visibleCount++;
      } else {
        wrapper.classList.add("hidden");
      }
    } 
    else {
      if (league === series) {
        wrapper.classList.remove("hidden");
        visibleCount++;
      } else {
        wrapper.classList.add("hidden");
      }
    }
  });

  // Remove old message
  panel.querySelector(".hot-empty-msg")?.remove();

  // Show if no matches
  if (series === "Hot" && visibleCount === 0) {
    const msg = document.createElement("div");
    msg.className = "hot-empty-msg text-center text-red-500 text-sm py-3";
    msg.innerText = "🔥 Hot League Not Available";
    panel.appendChild(msg);
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


function initDots(containerId, dotsId) {
    const container = document.getElementById(containerId);
    const dotsContainer = document.getElementById(dotsId);
    if (!container || !dotsContainer) return;

    const gap = 16; // gap between cards

    function updateDots() {
        const cards = Array.from(container.querySelectorAll('.match-card'))
            .filter(c => !c.closest('a').classList.contains('hidden')); // only visible cards
        if (cards.length === 0) {
            dotsContainer.innerHTML = '';
            return;
        }

        const cardWidth = cards[0].offsetWidth + gap;
        const cardsPerView = Math.floor(container.offsetWidth / cardWidth) || 1;

        // Calculate totalDots based on visible cards
        const totalDots = cards.length <= cardsPerView ? 0 : (cards.length - cardsPerView + 1);

        dotsContainer.innerHTML = '';

        for (let i = 0; i < totalDots; i++) {
            const dot = document.createElement('span');
            dot.className = "w-2 h-2 sm:w-2.5 sm:h-2.5 mx-1 rounded-full bg-white cursor-pointer transition";

            dot.onclick = () => {
                const scrollLeft = i * cardWidth;
                container.scrollTo({ left: scrollLeft, behavior: 'smooth' });
            };

            dotsContainer.appendChild(dot);
        }

        updateActiveDot();
    }

    function updateActiveDot() {
        const cards = Array.from(container.querySelectorAll('.match-card'))
            .filter(c => !c.closest('a').classList.contains('hidden'));
        if (!cards.length) return;

        const cardWidth = cards[0].offsetWidth + gap;
        const cardsPerView = Math.floor(container.offsetWidth / cardWidth) || 1;

        const index = Math.round(container.scrollLeft / cardWidth);
        dotsContainer.querySelectorAll('span').forEach((dot, i) => {
            dot.classList.toggle('bg-sky-500', i === index);
            dot.classList.toggle('scale-125', i === index);
            dot.classList.toggle('bg-white', i !== index);
        });
    }

    container.addEventListener('scroll', updateActiveDot);

    // Observe filter changes
    const observer = new MutationObserver(updateDots);
    observer.observe(container, { childList: true, subtree: true, attributes: true, attributeFilter: ['class'] });

    // Responsive styling
    dotsContainer.classList.add('flex', 'space-x-1', 'overflow-x-auto', 'scrollbar-hide', 'py-1');

    // Initial render
    updateDots();
}

// Initialize after DOM ready
document.addEventListener("DOMContentLoaded", () => {
    initDots('upcoming-scroll', 'upcoming-dots');
    initDots('livescore-scroll', 'livescore-dots');
    initDots('result-scroll', 'result-dots');
});



// Ini