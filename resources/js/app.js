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
    let current = 0;
    let timer;

    const show = (index) => {
        current = (index + slides.length) % slides.length;
        slides.forEach((slide, position) => {
            const active = position === current;
            slide.classList.toggle('is-active', active);
            slide.setAttribute('aria-hidden', String(!active));
        });
        dots.forEach((dot, position) => dot.classList.toggle('is-active', position === current));
    };

    const play = () => { timer = window.setInterval(() => show(current + 1), 6000); };
    const restart = () => { window.clearInterval(timer); play(); };

    slider.querySelector('[data-slider-prev]')?.addEventListener('click', () => { show(current - 1); restart(); });
    slider.querySelector('[data-slider-next]')?.addEventListener('click', () => { show(current + 1); restart(); });
    dots.forEach((dot) => dot.addEventListener('click', () => { show(Number(dot.dataset.sliderDot)); restart(); }));
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
