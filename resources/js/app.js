const headerMenu = document.querySelector('[data-header-menu]');

if (headerMenu) {
    const toggle = headerMenu.querySelector('summary');

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && headerMenu.open) {
            headerMenu.open = false;
            toggle.focus();
        }
    });

    document.addEventListener('click', (event) => {
        if (!headerMenu.contains(event.target)) {
            headerMenu.open = false;
        }
    });

    headerMenu.querySelectorAll('a').forEach((link) => {
        link.addEventListener('click', () => {
            headerMenu.open = false;
            toggle.focus({ preventScroll: true });
        });
    });

    window.matchMedia('(min-width: 80rem)').addEventListener('change', () => {
        headerMenu.open = false;
    });
}
