
<link rel="stylesheet" href="/css/scroll-to-top.css">
<button id="scrollTopBtn" title="Go to top">↑</button>
<script>

    window.addEventListener("scroll", function() {
        const btn = document.getElementById("scrollTopBtn");
        if (window.scrollY > 200) {
            btn.style.display = "block";
        } else {
            btn.style.display = "none";
        }
    });
    document.getElementById("scrollTopBtn").addEventListener("click", function() {
        window.scrollTo({
            top: 0,
            behavior: "smooth"
        });
    });
</script>