import './bootstrap';
import { initAjaxNavigation } from './ajax';

const initPublicPage = () => {
    const menuButton = document.querySelector('.menu-toggle');
    const menu = document.querySelector('#primary-menu');

    menuButton?.addEventListener('click', () => {
        const open = menu.classList.toggle('is-open');
        menuButton.setAttribute('aria-expanded', String(open));
    });

    const revealSections = document.querySelectorAll('main section:not(.hero-slider), .footer-wrapper .footer-widget');

    if (revealSections.length && 'IntersectionObserver' in window && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;

                entry.target.classList.add('is-revealed');
                observer.unobserve(entry.target);
            });
        }, { threshold: 0.08, rootMargin: '0px 0px -8% 0px' });

        revealSections.forEach((section) => {
            section.classList.add('scroll-reveal');
            observer.observe(section);
        });
    }

    const slider = document.querySelector('[data-slider]');

    if (slider) {
        const slides = [...slider.querySelectorAll('[data-slide]')];
        const dots = [...slider.querySelectorAll('[data-slider-dot]')];
        const pixelLayer = slider.querySelector('[data-pixel-transition]');
        const controls = slider.querySelector('.slider-controls');
        let current = 0;
        let timer;
        let animating = false;
        let transition;

        const syncControls = () => {
            const cta = slides[current]?.querySelector('.slide_more');

            if (!controls || !cta) return;

            const sliderRect = slider.getBoundingClientRect();
            const ctaRect = cta.getBoundingClientRect();
            const styles = window.getComputedStyle(slider);
            const gap = Number.parseFloat(styles.getPropertyValue('--hero-controls-gap')) || 48;
            const desiredTop = ctaRect.bottom - sliderRect.top + gap;
            const maximumTop = slider.clientHeight - controls.offsetHeight - 18;

            controls.style.top = `${Math.min(desiredTop, maximumTop)}px`;
            controls.style.bottom = 'auto';
        };

        const controlsObserver = 'ResizeObserver' in window
            ? new ResizeObserver(syncControls)
            : null;

        controlsObserver?.observe(slider);
        slides.forEach((slide) => {
            const copy = slide.querySelector('.hero-copy');
            if (copy) controlsObserver?.observe(copy);
        });
        window.requestAnimationFrame(syncControls);

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
            window.requestAnimationFrame(syncControls);
        };

        const show = (index, direction = 'next') => {
            const next = (index + slides.length) % slides.length;

            if (next === current || animating) return;

            animating = true;
            const outgoing = slides[current];
            const incoming = slides[next];
            dots.forEach((dot, position) => dot.classList.toggle('is-active', position === next));

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
            transition = animation;

            animation.addEventListener('finish', () => {
                if (transition !== animation) return;

                transition = undefined;
                activate(outgoing, incoming, next);
                pixelLayer.replaceChildren();
            }, { once: true });
        };

        const jumpTo = (index) => {
            const next = (index + slides.length) % slides.length;

            transition?.cancel();
            transition = undefined;
            pixelLayer?.replaceChildren();
            animating = false;

            if (next !== current) activate(slides[current], slides[next], next);
        };

        const play = () => { timer = window.setInterval(() => show(current + 1, 'next'), 6000); };
        const restart = () => { window.clearInterval(timer); play(); };

        slider.querySelector('[data-slider-prev]')?.addEventListener('click', () => { show(current - 1, 'prev'); restart(); });
        slider.querySelector('[data-slider-next]')?.addEventListener('click', () => { show(current + 1, 'next'); restart(); });
        dots.forEach((dot) => dot.addEventListener('click', () => {
            jumpTo(Number(dot.dataset.sliderDot));
            restart();
        }));
        slider.addEventListener('mouseenter', () => window.clearInterval(timer));
        slider.addEventListener('mouseleave', play);
        window.addEventListener('ajax:before-render', () => {
            window.clearInterval(timer);
            transition?.cancel();
            controlsObserver?.disconnect();
        }, { once: true });
        play();
    }

    const villageStats = document.querySelector('[data-village-stats]');

    if (villageStats && 'IntersectionObserver' in window && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        const counters = [...villageStats.querySelectorAll('[data-stat-count]')];
        const formatter = new Intl.NumberFormat('id-ID');

        counters.forEach((counter) => { counter.textContent = '0'; });

        const observer = new IntersectionObserver(([entry]) => {
            if (!entry.isIntersecting) return;

            counters.forEach((counter) => {
                const target = Number(counter.dataset.statCount);
                const startedAt = performance.now();

                const count = (now) => {
                    const progress = Math.min((now - startedAt) / 800, 1);
                    const eased = 1 - ((1 - progress) ** 3);
                    counter.textContent = formatter.format(Math.round(target * eased));

                    if (progress < 1) requestAnimationFrame(count);
                };

                requestAnimationFrame(count);
            });

            observer.disconnect();
        }, { threshold: 0.35 });

        observer.observe(villageStats);
    }

    const budgetSection = document.querySelector('[data-budget-section]');

    if (budgetSection && 'IntersectionObserver' in window && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        budgetSection.classList.add('is-animated');

        const observer = new IntersectionObserver(([entry]) => {
            if (!entry.isIntersecting) return;

            budgetSection.classList.add('is-visible');
            observer.disconnect();
        }, { threshold: 0.18 });

        observer.observe(budgetSection);
    }

    const newsFilters = [...document.querySelectorAll('[data-news-filter]')];
    const homeNewsCards = [...document.querySelectorAll('#home-news-grid [data-news-category]')];

    newsFilters.forEach((button) => {
        button.addEventListener('click', () => {
            const category = button.dataset.newsFilter;

            homeNewsCards.forEach((card) => {
                card.hidden = category !== 'all' && card.dataset.newsCategory !== category;
            });

            newsFilters.forEach((filter) => {
                const active = filter === button;
                filter.classList.toggle('is-active', active);
                filter.setAttribute('aria-pressed', String(active));
            });
        });
    });

    document.querySelectorAll('[data-sidebar-toggle]').forEach((toggle) => {
        if (toggle.dataset.bound === 'true') return;

        const panel = document.getElementById(toggle.getAttribute('aria-controls'));
        if (!panel) return;

        const setExpanded = (expanded) => {
            toggle.setAttribute('aria-expanded', String(expanded));
            panel.hidden = !expanded;
        };

        toggle.dataset.bound = 'true';
        toggle.addEventListener('click', () => {
            const expanded = toggle.getAttribute('aria-expanded') === 'true';

            setExpanded(!expanded);
        });
    });

    document.querySelectorAll('[data-gallery-carousel]').forEach((carousel) => {
        const items = [...carousel.querySelectorAll('[data-carousel-item]')];
        const previous = carousel.querySelector('[data-gallery-prev]');
        const next = carousel.querySelector('[data-gallery-next]');
        let selected = Math.floor(items.length / 2);

        const select = (index) => {
            selected = Math.min(Math.max(index, 0), items.length - 1);

            items.forEach((item, itemIndex) => {
                const position = itemIndex - selected;

                item.dataset.carouselPosition = position;
                item.classList.toggle('is-outside', Math.abs(position) > 2);
                item.setAttribute('aria-pressed', String(position === 0));
                item.setAttribute('aria-label', `${position === 0 ? 'Buka detail' : 'Pilih'} ${item.dataset.title}`);
            });

            if (previous) previous.disabled = selected === 0;
            if (next) next.disabled = selected === items.length - 1;
        };

        items.forEach((item, index) => {
            item.addEventListener('click', (event) => {
                if (index === selected) return;

                event.stopImmediatePropagation();
                select(index);
            });
        });

        previous?.addEventListener('click', () => select(selected - 1));
        next?.addEventListener('click', () => select(selected + 1));
        select(selected);
    });

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

    if (backToTop && !backToTop.dataset.ajaxBound) {
        const updateButton = () => {
            const hero = document.querySelector('.hero-slider');
            const heroPassed = hero ? hero.getBoundingClientRect().bottom <= 0 : window.scrollY >= 500;
            backToTop.classList.toggle('is-visible', heroPassed);
            backToTop.setAttribute('aria-hidden', String(!heroPassed));
            backToTop.tabIndex = heroPassed ? 0 : -1;
        };

        backToTop.hidden = false;
        backToTop.dataset.ajaxBound = 'true';
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
};

initAjaxNavigation({
    rootSelector: '#sitelayout_type',
    onRender: initPublicPage,
});

initPublicPage();
