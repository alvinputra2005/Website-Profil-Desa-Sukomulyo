import './bootstrap';

const menuButton = document.querySelector('.menu-toggle');
const menu = document.querySelector('#primary-menu');

menuButton?.addEventListener('click', () => {
    const open = menu.classList.toggle('is-open');
    menuButton.setAttribute('aria-expanded', String(open));
});

const slider = document.querySelector('[data-slider]');

if (slider) {
    const slides = [...slider.querySelectorAll('[data-slide]')];
    const dots = [...slider.querySelectorAll('[data-slider-dot]')];
    const pixelLayer = slider.querySelector('[data-pixel-transition]');
    let current = 0;
    let timer;
    let animating = false;

    const waveClip = (progress, direction) => {
        const offsets = [-4, 3, -2, 4, -3, 2, -1, 1];
        const edge = direction === 'prev' ? 100 - (progress * 100) : progress * 100;
        const wave = Math.sin(Math.PI * progress);
        const positions = offsets.map((offset) => Math.max(0, Math.min(100, edge + (offset * wave))));
        const points = direction === 'prev'
            ? ['100% 0%', `${positions[0]}% 0%`]
            : ['0% 0%', `${positions[0]}% 0%`];

        positions.forEach((position, band) => {
            const y = ((band + 1) / positions.length) * 100;
            points.push(`${position}% ${y}%`);

            if (band < positions.length - 1) {
                points.push(`${positions[band + 1]}% ${y}%`);
            }
        });

        points.push(direction === 'prev' ? '100% 100%' : '0% 100%');

        return `polygon(${points.join(', ')})`;
    };

    const activate = (outgoing, incoming, next) => {
        outgoing.classList.remove('is-active');
        outgoing.setAttribute('aria-hidden', 'true');
        incoming.classList.add('is-active');
        incoming.setAttribute('aria-hidden', 'false');
        current = next;
        dots.forEach((dot, position) => dot.classList.toggle('is-active', position === current));
        animating = false;
    };

    const show = (index, direction = 'next') => {
        const next = (index + slides.length) % slides.length;

        if (next === current || animating) return;

        animating = true;
        const outgoing = slides[current];
        const incoming = slides[next];

        if (!pixelLayer || !Element.prototype.animate || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            activate(outgoing, incoming, next);
            return;
        }

        const clone = incoming.cloneNode(true);
        const keyframes = Array.from({ length: 19 }, (_, step) => {
            const progress = step / 18;

            return { clipPath: waveClip(progress, direction), offset: progress };
        });

        clone.classList.remove('is-active');
        clone.classList.add('pixel-slide-clone');
        clone.removeAttribute('data-slide');
        clone.setAttribute('aria-hidden', 'true');
        pixelLayer.replaceChildren(clone);

        const animation = clone.animate(keyframes, {
            duration: 1000,
            easing: 'linear',
            fill: 'forwards',
        });

        animation.addEventListener('finish', () => {
            activate(outgoing, incoming, next);
            pixelLayer.replaceChildren();
        }, { once: true });
    };

    const play = () => { timer = window.setInterval(() => show(current + 1, 'next'), 6000); };
    const restart = () => { window.clearInterval(timer); play(); };

    slider.querySelector('[data-slider-prev]')?.addEventListener('click', () => { show(current - 1, 'prev'); restart(); });
    slider.querySelector('[data-slider-next]')?.addEventListener('click', () => { show(current + 1, 'next'); restart(); });
    dots.forEach((dot) => dot.addEventListener('click', () => {
        const next = Number(dot.dataset.sliderDot);
        show(next, next < current ? 'prev' : 'next');
        restart();
    }));
    slider.addEventListener('mouseenter', () => window.clearInterval(timer));
    slider.addEventListener('mouseleave', play);
    play();
}

const galleryDialog = document.querySelector('[data-gallery-dialog]');

if (galleryDialog) {
    const image = galleryDialog.querySelector('[data-gallery-image]');
    const title = galleryDialog.querySelector('[data-gallery-title]');
    const caption = galleryDialog.querySelector('[data-gallery-caption]');

    document.querySelectorAll('[data-gallery-item]').forEach((item) => {
        item.addEventListener('click', () => {
            image.src = item.dataset.image;
            image.alt = item.dataset.title;
            title.textContent = item.dataset.title;
            caption.textContent = item.dataset.caption;
            galleryDialog.showModal();
        });
    });

    galleryDialog.querySelector('[data-gallery-close]')?.addEventListener('click', () => galleryDialog.close());
    galleryDialog.addEventListener('click', (event) => {
        if (event.target === galleryDialog) galleryDialog.close();
    });
}

const backToTop = document.querySelector('.back-to-top');

if (backToTop) {
    const updateButton = () => { backToTop.hidden = window.scrollY < 500; };
    window.addEventListener('scroll', updateButton, { passive: true });
    backToTop.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
    updateButton();
}

const messageInput = document.querySelector('[data-message-input]');
const messageCount = document.querySelector('[data-message-count]');

if (messageInput && messageCount) {
    const updateCount = () => { messageCount.textContent = messageInput.value.length; };
    messageInput.addEventListener('input', updateCount);
    updateCount();
}
