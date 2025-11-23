
const menuButton = document.getElementById("mobile-menu-button");
const mobileMenu = document.getElementById("mobile-menu");
const menuPath = document.getElementById("mobile-menu-path");

menuButton.addEventListener("click", () => {
  const isOpen = mobileMenu.classList.contains("max-h-[500px]");

  if (isOpen) {
    // Close animation
    mobileMenu.classList.remove("max-h-[500px]", "opacity-100");
    mobileMenu.classList.add("max-h-0", "opacity-0");

    menuPath.setAttribute("d", "M4 6h16M4 12h16M4 18h16"); // Hamburger
  } else {
    // Open animation
    mobileMenu.classList.remove("max-h-0", "opacity-0");
    mobileMenu.classList.add("max-h-[500px]", "opacity-100");

    menuPath.setAttribute("d", "M6 6l12 12M6 18L18 6"); // X icon
  }
});


const html = document.documentElement;
const themeButton = document.getElementById("theme-toggle");
let themeIcon = document.getElementById("theme-icon"); 

function setIcons(dark) {
  const sunImg = "./images/sun-24.ico";
  const moonIcon = `
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
        d="M21 12.79A9 9 0 1111.21 3a7 7 0 009.79 9.79z"/>
    `;

  if (dark) {
    themeIcon.outerHTML = `
        <img id="theme-icon" src="${sunImg}" alt="Sun"
          class="transition-transform duration-300 cursor-pointer text-white">
      `;
  } else {
    themeIcon.outerHTML = `
        <svg id="theme-icon"
          class="h-6 w-6 text-white transition-transform duration-300 cursor-pointer"
          fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          ${moonIcon}
        </svg>
      `;
  }

  themeIcon = document.getElementById("theme-icon");
}

function setTheme(dark) {
  if (dark) {
    html.classList.add("dark");
  } else {
    html.classList.remove("dark");
  }

  localStorage.setItem("theme", dark ? "dark" : "light");
  setIcons(dark);
}

function toggleTheme() {
  const isDark = html.classList.contains("dark");
  setTheme(!isDark);

  themeIcon.classList.add("rotate-180");
  setTimeout(() => themeIcon.classList.remove("rotate-180"), 300);
}

themeButton.addEventListener("click", toggleTheme);

const saved = localStorage.getItem("theme");
const isDarkMode =
  saved === "dark" ||
  (!saved && window.matchMedia("(prefers-color-scheme: dark)").matches);
setTheme(isDarkMode);
