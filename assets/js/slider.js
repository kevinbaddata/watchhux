document.addEventListener('DOMContentLoaded', function() {
    var splide = new Splide('.splide', {
        type: 'loop',
        perPage: 6,
        rewind: true,
        gap: '1rem',
        autoWidth: true,
        autoplay: true,
        interval: 2000

    });

    splide.mount();
});