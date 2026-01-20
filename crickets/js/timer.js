function startCountdown() {
  const statusEl = document.getElementById("match-status");
  const matchStartTs = parseInt(statusEl.dataset.start, 10);
  const isLive = statusEl.dataset.live === "1";
  const eventStatus = statusEl.dataset.status;
  const apiStatusInfo = statusEl.dataset.api || "";

  if (eventStatus === "finished") {
    statusEl.textContent = apiStatusInfo || "Match Ended";
  } else if (isLive) {
    statusEl.textContent = "LIVE";
  } else {
    // Upcoming match countdown
    const interval = setInterval(() => {
      const nowTs = Math.floor(Date.now() / 1000);
      let diff = matchStartTs - nowTs;

      if (diff <= 0) {
        statusEl.textContent = apiStatusInfo || "Match Started";
        clearInterval(interval);
        return;
      }

      const days = Math.floor(diff / 86400);
      const hours = Math.floor((diff % 86400) / 3600);
      const minutes = Math.floor((diff % 3600) / 60);
      const seconds = diff % 60;

      statusEl.textContent = `Match starts in ${days}d ${hours}h ${minutes}m ${seconds}s`;
    }, 1000);
  }
}

document.addEventListener("DOMContentLoaded", startCountdown);
