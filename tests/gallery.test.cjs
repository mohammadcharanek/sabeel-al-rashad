const assert = require('node:assert/strict');
const { readFileSync } = require('node:fs');
const { join } = require('node:path');
const { test } = require('node:test');
const { runInNewContext } = require('node:vm');

for (const reducedMotion of [false, true]) {
    test(`thumbnail selection keeps arrow navigation usable with reduced motion ${reducedMotion}`, () => {
        const document = { activeElement: null };
        const element = () => ({
            listeners: {},
            attributes: {},
            addEventListener(type, listener) { this.listeners[type] = listener; },
            setAttribute(name, value) { this.attributes[name] = value; },
            focus() { document.activeElement = this; },
            scrollIntoView(options) { this.scrollBehavior = options.behavior; },
        });
        const gallery = element();
        const slides = [element(), element(), element()];
        const thumbnails = [element(), element(), element()];
        const counter = element();
        gallery.querySelectorAll = () => slides;
        gallery.querySelector = (selector) => selector === '[data-gallery-counter]' ? counter : element();
        document.querySelector = () => gallery;
        document.querySelectorAll = () => thumbnails;
        runInNewContext(readFileSync(join(__dirname, '../public/js/gallery.js'), 'utf8'), {
            document,
            window: { matchMedia: () => ({ matches: reducedMotion }) },
        });

        thumbnails[1].focus();
        thumbnails[1].listeners.click();

        assert.equal(document.activeElement, gallery);
        assert.equal(counter.textContent, '2 / 3');
        assert.equal(gallery.scrollBehavior, reducedMotion ? 'auto' : 'smooth');

        document.activeElement.listeners.keydown({ key: 'ArrowLeft', preventDefault() {} });

        assert.equal(counter.textContent, '3 / 3');
        assert.deepEqual(slides.map(slide => slide.hidden), [true, true, false]);
        assert.deepEqual(thumbnails.map(thumb => thumb.attributes['aria-pressed']), ['false', 'false', 'true']);
    });
}
