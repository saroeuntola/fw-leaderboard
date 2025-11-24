
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

// Set dark theme
function setTheme(dark) {
  html.classList.toggle("dark", dark);
  localStorage.setItem("theme", dark ? "dark" : "light");
}

function toggleTheme() {
  const isDark = html.classList.contains("dark");
  setTheme(!isDark);
}

themeButton.addEventListener("click", toggleTheme);

// --- Default Dark Mode Logic ---
const saved = localStorage.getItem("theme");

if (saved === "dark") {
  setTheme(true);
} else if (saved === "light") {
  setTheme(false);
} else {
  // No saved mode → default to DARK
  setTheme(true);
}
