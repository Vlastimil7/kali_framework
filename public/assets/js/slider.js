
        document.addEventListener('DOMContentLoaded', function () {
            const track = document.getElementById('gallery-track');
            if (!track) return;

            const slides = Array.from(track.querySelectorAll('[data-gallery-slide]'));
            const dots = Array.from(document.querySelectorAll('[data-gallery-dot]'));
            const prevBtn = document.getElementById('gallery-prev');
            const nextBtn = document.getElementById('gallery-next');

            let currentIndex = 0;
            const slideCount = slides.length;

            function updateSlider(index) {
                if (index < 0) index = slideCount - 1;
                if (index >= slideCount) index = 0;
                currentIndex = index;

                const offset = -currentIndex * 100;
                track.style.transform = 'translateX(' + offset + '%)';

                dots.forEach((dot, i) => {
                    dot.dataset.active = (i === currentIndex).toString();
                });
            }

            if (prevBtn) {
                prevBtn.addEventListener('click', function () {
                    updateSlider(currentIndex - 1);
                });
            }

            if (nextBtn) {
                nextBtn.addEventListener('click', function () {
                    updateSlider(currentIndex + 1);
                });
            }

            dots.forEach((dot, i) => {
                dot.addEventListener('click', function () {
                    updateSlider(i);
                });
            });

            let autoSlide = setInterval(function () {
                updateSlider(currentIndex + 1);
            }, 7000);

            track.addEventListener('mouseenter', function () {
                clearInterval(autoSlide);
            });
            track.addEventListener('mouseleave', function () {
                autoSlide = setInterval(function () {
                    updateSlider(currentIndex + 1);
                }, 7000);
            });

            updateSlider(0);
        });
 