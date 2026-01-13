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
  }
}

tabs.forEach((btn) => {
  btn.addEventListener("click", () => showTab(btn.dataset.tab));
});

// Restore last active tab
showTab(localStorage.getItem("cric_tab") || "upcoming");


 const liveContainerId = "livescore-scroll";

 function updateLiveArrows() {
   const container = document.getElementById(liveContainerId);
   if (!container) return;

   const wrapper = container.parentElement;
   const leftArrow = wrapper.querySelector(".scroll-arrow.left");
   const rightArrow = wrapper.querySelector(".scroll-arrow.right");

   if (container.scrollWidth <= container.clientWidth) {
     // Not scrollable → hide arrows
     leftArrow.style.display = "none";
     rightArrow.style.display = "none";
   } else {
     // Scrollable → show arrows
     leftArrow.style.display = "block";
     rightArrow.style.display = "block";

     // Optional: disable arrows at edges
     leftArrow.disabled = container.scrollLeft === 0;
     rightArrow.disabled =
       container.scrollLeft + container.clientWidth >= container.scrollWidth;
   }
 }

 // Scroll function
 function scrollContainer(id, direction) {
   const el = document.getElementById(id);
   if (!el) return;
   const scrollAmount = el.offsetWidth * 0.8;
   el.scrollBy({
     left: direction * scrollAmount,
     behavior: "smooth",
   });

   // Small timeout to update disabled state after scroll
   setTimeout(updateLiveArrows, 200);
 }

 // Initial check
 updateLiveArrows();

 // Update on resize
 window.addEventListener("resize", updateLiveArrows);


   document.addEventListener("click", function (e) {
     const btn = e.target.closest(".series-tab");
     if (!btn) return;

     const panel = btn.closest(".tab-panel");
     const series = btn.dataset.series;

     // Toggle active state
     panel
       .querySelectorAll(".series-tab")
       .forEach((b) => b.classList.remove("series-active"));
     btn.classList.add("series-active");

     // Filter cards
     panel.querySelectorAll(".match-card").forEach((card) => {
       if (series === "all" || card.dataset.series === series) {
         card.style.display = "";
       } else {
         card.style.display = "none";
       }
     });
   });

   document
     .querySelectorAll(".tab-panel .flex.overflow-x-auto")
     .forEach((container) => {
       let isDown = false,
         startX,
         scrollLeft;

       container.addEventListener("mousedown", (e) => {
         isDown = true;
         container.classList.add("cursor-grabbing");
         startX = e.pageX - container.offsetLeft;
         scrollLeft = container.scrollLeft;
       });
       container.addEventListener("mouseleave", () => {
         isDown = false;
         container.classList.remove("cursor-grabbing");
       });
       container.addEventListener("mouseup", () => {
         isDown = false;
         container.classList.remove("cursor-grabbing");
       });
       container.addEventListener("mousemove", (e) => {
         if (!isDown) return;
         e.preventDefault();
         const x = e.pageX - container.offsetLeft;
         const walk = (x - startX) * 2; // scroll-fast
         container.scrollLeft = scrollLeft - walk;
       });
     });