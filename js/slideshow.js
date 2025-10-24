document.addEventListener("DOMContentLoaded", function () {
  const slides = document.querySelectorAll("#myCarousel .carousel-item");
  const wrapper = document.querySelector("#myCarousel .carousel-wrapper");
  const dots = document.querySelectorAll("#carouselDots .dot");
  const totalSlides = slides.length;

  let currentIndex = 0;
  let autoSlide;

  slides.forEach((slide) => {
    slide.style.opacity = "0";
    slide.style.transition = "opacity 0.6s ease";
  });

  function showSlide(index) {
    slides.forEach((slide) => {
      slide.style.opacity = "0";
      slide.style.pointerEvents = "none";
    });

    slides[index].style.opacity = "1";
    slides[index].style.pointerEvents = "auto";

    wrapper.style.transform = `translateX(${-index * 100}%)`;

    dots.forEach((dot) => dot.classList.remove("active"));
    dots[index].classList.add("active");

    currentIndex = index;
  }

  function nextSlide() {
    showSlide((currentIndex + 1) % totalSlides);
  }

  function prevSlide() {
    showSlide((currentIndex - 1 + totalSlides) % totalSlides);
  }

  document
    .querySelector("#myCarousel .next")
    .addEventListener("click", nextSlide);
  document
    .querySelector("#myCarousel .prev")
    .addEventListener("click", prevSlide);

  dots.forEach((dot, i) => dot.addEventListener("click", () => showSlide(i)));

  // Auto slide
  function startAutoSlide() {
    autoSlide = setInterval(nextSlide, 4000);
  }

  function stopAutoSlide() {
    clearInterval(autoSlide);
  }

  slides.forEach((slide) => {
    slide.addEventListener("mouseenter", stopAutoSlide);
    slide.addEventListener("mouseleave", startAutoSlide);
  });
  showSlide(0);
  startAutoSlide();
});
