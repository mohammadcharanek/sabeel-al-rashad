(() => {
    const gallery = document.querySelector('[data-gallery-slideshow]');
    if (!gallery) return;
    const slides = [...gallery.querySelectorAll('[data-gallery-slide]')];
    const counter = gallery.querySelector('[data-gallery-counter]');
    const previous = gallery.querySelector('[data-gallery-prev]');
    const next = gallery.querySelector('[data-gallery-next]');
    const thumbnails = [...document.querySelectorAll('[data-gallery-go-to]')];
    let current = 0;

    function show(index) {
        if (!slides.length) return;
        current = (index + slides.length) % slides.length;
        slides.forEach((slide, i) => { slide.hidden = i !== current; });
        if (counter) counter.textContent = `${current + 1} / ${slides.length}`;
        thumbnails.forEach((thumb, i) => {
            thumb.setAttribute('aria-pressed', String(i === current));
        });
    }
    previous?.addEventListener('click', () => show(current - 1));
    next?.addEventListener('click', () => show(current + 1));
    thumbnails.forEach((thumb, i) => thumb.addEventListener('click', () => {
        show(i);
        gallery.scrollIntoView({ behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth', block: 'start' });
    }));
    gallery.addEventListener('keydown', event => {
        if (event.key === 'ArrowLeft') { event.preventDefault(); show(current + 1); }
        if (event.key === 'ArrowRight') { event.preventDefault(); show(current - 1); }
    });
    show(0);
})();
