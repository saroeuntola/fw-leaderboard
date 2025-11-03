  function startCountdown(id, dateString, durationMinutes) {
        const countdown = document.getElementById(id);
        const eventStart = new Date(dateString).getTime();
        const eventEnd = eventStart + durationMinutes * 60 * 1000;
        let timer = null; // define per event

        function updateCountdown() {
            const now = new Date().getTime();
            if (now < eventStart) {
                const diff = eventStart - now;
                const d = Math.floor(diff / (1000 * 60 * 60 * 24));
                const h = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const m = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                const s = Math.floor((diff % (1000 * 60)) / 1000);

                countdown.innerHTML = `
                <div class="text-cyan-400 text-sm mb-1">Starts in:</div>
                <span class="text-yellow-400">${d}d</span> :
                <span class="text-cyan-400">${h}h</span> :
                <span class="text-green-400">${m}m</span> :
                <span class="text-red-500">${s}s</span>
            `;
            } else if (now >= eventStart && now < eventEnd) {
                const diff = eventEnd - now;
                const h = Math.floor((diff / (1000 * 60 * 60)) % 24);
                const m = Math.floor((diff / (1000 * 60)) % 60);
                const s = Math.floor((diff / 1000) % 60);

                countdown.innerHTML = `
                <div class="text-yellow-400 text-sm mb-1 animate-pulse">Time left:</div>
                <span class="text-yellow-400">${h}h</span> :
                <span class="text-green-400">${m}m</span> :
                <span class="text-pink-400">${s}s</span>
            `;
            } else {
                countdown.innerHTML = "<span class='text-red-500 font-semibold'>Event Ended</span>";
                clearInterval(timer);
            }
        }
        timer = setInterval(updateCountdown, 1000);
        updateCountdown();
    }

