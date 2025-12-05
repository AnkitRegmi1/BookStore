/* js_carousel.js
 * Full-page hero carousel for three featured books.
 * - Each slide fills the viewport
 * - Auto-advances every 10 seconds
 * - Supports manual navigation via arrows and dots
 */

document.addEventListener('DOMContentLoaded', function () {
    const track = document.getElementById('book-carousel');
    if (!track) return;

    const slides = Array.from(track.querySelectorAll('.book-slide'));
    const nextButton = document.querySelector('.hero-nav--next');
    const prevButton = document.querySelector('.hero-nav--prev');
    const dots = Array.from(document.querySelectorAll('.hero-dot'));

    let currentIndex = 0;
    const AUTO_INTERVAL_MS = 10000;
    let autoTimer = null;

    const updateDots = (index) => {
        dots.forEach((dot, i) => {
            if (i === index) {
                dot.classList.add('hero-dot--active');
            } else {
                dot.classList.remove('hero-dot--active');
            }
        });
    };

    const goToSlide = (index) => {
        currentIndex = (index + slides.length) % slides.length;
        const offsetPercent = currentIndex * 100;
        track.style.transform = 'translateX(-' + offsetPercent + '%)';
        updateDots(currentIndex);
    };

    const goToNext = () => goToSlide(currentIndex + 1);
    const goToPrev = () => goToSlide(currentIndex - 1);

    const resetAutoTimer = () => {
        if (autoTimer) clearInterval(autoTimer);
        autoTimer = setInterval(goToNext, AUTO_INTERVAL_MS);
    };

    // Wire up navigation
    if (nextButton) {
        nextButton.addEventListener('click', () => {
            goToNext();
            resetAutoTimer();
        });
    }

    if (prevButton) {
        prevButton.addEventListener('click', () => {
            goToPrev();
            resetAutoTimer();
        });
    }

    dots.forEach((dot, index) => {
        dot.addEventListener('click', () => {
            goToSlide(index);
            resetAutoTimer();
        });
    });

    // Initial state
    goToSlide(0);
    resetAutoTimer();
});