const header = document.querySelector('[data-site-header]');
const headerMenu = document.querySelector('[data-header-menu]');
const menuToggle = headerMenu?.querySelector('summary');

if (headerMenu) {
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && headerMenu.open) {
            headerMenu.open = false;
            menuToggle.focus();
        }
    });

    document.addEventListener('click', (event) => {
        if (!headerMenu.contains(event.target)) {
            headerMenu.open = false;
        }
    });

    headerMenu.addEventListener('focusout', () => {
        requestAnimationFrame(() => {
            if (!headerMenu.contains(document.activeElement)) {
                headerMenu.open = false;
            }
        });
    });

    window.matchMedia('(min-width: 80rem)').addEventListener('change', (event) => {
        const focusWasInMenu = headerMenu.contains(document.activeElement);
        headerMenu.open = false;

        if (event.matches && focusWasInMenu) {
            header.querySelector('a').focus({ preventScroll: true });
        }
    });
}

document.querySelectorAll('a[href^="#"]').forEach((link) => {
    const target = document.getElementById(link.hash.slice(1));

    if (!target) {
        return;
    }

    link.addEventListener('click', (event) => {
        if (event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) {
            return;
        }

        if (headerMenu) {
            headerMenu.open = false;
        }

        target.setAttribute('tabindex', '-1');
        target.focus({ preventScroll: true });
    });
});

const navigationLinks = [...document.querySelectorAll('[data-nav-link]')];
const navigationTargets = [...new Set(navigationLinks.map((link) => document.getElementById(link.hash.slice(1))))].filter(Boolean);
let updatePending = false;

function updateCurrentSection() {
    const offset = (header?.getBoundingClientRect().height ?? 0) + 32;
    let current = null;
    let currentTop = -Infinity;

    navigationTargets.forEach((target) => {
        const top = target.getBoundingClientRect().top;

        if (top <= offset && top > currentTop) {
            current = target.id;
            currentTop = top;
        }
    });

    navigationLinks.forEach((link) => {
        if (link.hash === '#' + current) {
            link.setAttribute('aria-current', 'location');
        } else {
            link.removeAttribute('aria-current');
        }
    });

    updatePending = false;
}

function scheduleSectionUpdate() {
    if (!updatePending) {
        updatePending = true;
        requestAnimationFrame(updateCurrentSection);
    }
}

window.addEventListener('scroll', scheduleSectionUpdate, { passive: true });
window.addEventListener('resize', scheduleSectionUpdate);
window.addEventListener('load', scheduleSectionUpdate);
updateCurrentSection();
