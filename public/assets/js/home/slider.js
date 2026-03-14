    document.addEventListener('DOMContentLoaded', function() {
        // HERO fade
        document.querySelectorAll('[data-hero-slider]').forEach(function(el) {
            new Slider(el, {
                mode: 'fade'
            }).init();
        });

        // GALLERY track 
        //   document.querySelectorAll('[data-gallery-slider]').forEach(function (el) {
        //     new Slider(el, { mode: 'track' }).init();
        //   });
    });