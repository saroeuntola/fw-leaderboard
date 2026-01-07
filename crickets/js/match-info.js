 const TABS_KEY = 'activeTab';
        const leagueKey = window.MATCH_INFO.leagueKey;
        let activeTab = localStorage.getItem(TABS_KEY) || 'overview';
        const tabs = document.querySelectorAll('.tab');
        const panels = document.querySelectorAll('.tab-panel');

        function activateTab(tabId) {
            tabs.forEach(t => t.classList.remove('border-blue-600'));
            panels.forEach(p => p.classList.add('hidden'));
            const btn = document.querySelector(`.tab[data-tab="${tabId}"]`);
            const panel = document.getElementById(tabId);
            if (btn && panel) {
                btn.classList.add('border-blue-600');
                panel.classList.remove('hidden');
                localStorage.setItem(TABS_KEY, tabId);
            }
        }

        let standingsLoaded = false;
        async function loadStandings() {
            const container = document.getElementById('standings-container');
            container.innerHTML = 'Loading standings…';
            try {
                const res = await fetch(`/crickets/pages/match-standings.php?league_key=${leagueKey}`);
                if (!res.ok) throw new Error('Network response not OK');
                const data = await res.text();
                container.innerHTML = data;
                standingsLoaded = true;
            } catch (e) {
                container.innerHTML = 'Failed to load standings.';
                console.error(e);
            }
        }

        tabs.forEach(btn => {
            btn.onclick = () => {
                activateTab(btn.dataset.tab);
                if (btn.dataset.tab === 'standings' && !standingsLoaded) loadStandings();
            };
        });
        activateTab(activeTab);
        if (activeTab === 'standings' && !standingsLoaded) loadStandings();





