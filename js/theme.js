const html = document.documentElement;

function setTheme(dark) {
  html.classList.toggle("dark", dark);
  localStorage.setItem("theme", dark ? "dark" : "light");
}

function toggleTheme() {
  setTheme(!html.classList.contains("dark"));
}

// Initialize theme on page load
(function initTheme() {
  const saved = localStorage.getItem("theme");
  const prefersDark = window.matchMedia("(prefers-color-scheme: dark)").matches;

  if (saved === "light") setTheme(false);
  else if (saved === "dark") setTheme(true);
  else setTheme(prefersDark);
})();
