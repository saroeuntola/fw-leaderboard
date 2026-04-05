
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>
 .container {
    background-color: #2c2a2a !important;
}
</style>
   <link rel="stylesheet" href="/src/output.css?v=<?= time() ?>">
    <link rel="stylesheet" href="/css/style.css?v=<?= time() ?>">
    <script src="/js/jquery-3.7.1.min.js"></script>
      <base target="_blank"> 
</head>

<body>

<div class="container">

<?php
// INCLUDE your existing live cricket logic
include("livescore.php"); 
?>

</div>
<script>
function sendHeight() {
  const height = document.body.scrollHeight;
  window.parent.postMessage({ iframeHeight: height }, "*");
}

// Run multiple times (important for live data)
window.onload = sendHeight;
window.onresize = sendHeight;

// Optional: update every 1s (for live score changes)
setInterval(sendHeight, 1000);
</script>
</body>
</html>