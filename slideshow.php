<?php
include "./admin/lib/banner_lib.php";
$bannerObj = new Banner();
$banners = $bannerObj->getBannerBySatus();
?>

<style>
    #myCarousel .carousel-wrapper {
        display: flex;
        transition: transform 0.5s ease-in-out;
    }

    #myCarousel .carousel-item {
        flex: 0 0 100%;
    }

    #carouselDots .dot.active {
        background-color: brown !important;
    }

    #myCarousel .btn-circle {
    background: none;
    color: white;
    border: none;
box-shadow: none;
font-size: 20px;
    }
</style>

<div class="carousel relative w-full overflow-hidden" id="myCarousel">
  <div class="carousel-wrapper flex transition-transform duration-500 w-full">
    <?php foreach ($banners as $index => $banner): ?>
      <div class="carousel-item flex justify-center items-center flex-shrink-0 w-full">
        <?php if (!empty($banner['link'])): ?>
          <a href="<?= htmlspecialchars($banner['link']) ?>" target="_blank" class="w-full flex justify-center">
            <img src="/admin/<?= htmlspecialchars($banner['image']) ?>" loading="lazy"
              class="w-full max-w-full lg:h-[400px] h-[180px] object-cover rounded-md" />
          </a>
        <?php else: ?>
          <img src="/admin/<?= htmlspecialchars($banner['image']) ?>" loading="lazy"
            class="w-full max-w-full lg:h-[400px] h-[180px] object-cover rounded-md" />
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- Prev/Next Buttons -->
  <div class="absolute inset-y-[70px] lg:inset-y-[180px] left-4 right-4 flex justify-between">
    <button class="btn-circle bg-black/30 text-white w-10 h-10 rounded-full flex items-center justify-center prev text-2xl">❮</button>
    <button class="btn-circle bg-black/30 text-white w-10 h-10 rounded-full flex items-center justify-center next text-2xl">❯</button>
  </div>

  <!-- Dots -->
  <div class="absolute bottom-3 left-1/2 transform -translate-x-1/2 flex space-x-2" id="carouselDots">
    <?php foreach ($banners as $i => $_): ?>
      <button class="dot w-3 h-3 rounded-full bg-white/50 <?= $i === 0 ? 'bg-white' : '' ?>"></button>
    <?php endforeach; ?>
  </div>
</div>