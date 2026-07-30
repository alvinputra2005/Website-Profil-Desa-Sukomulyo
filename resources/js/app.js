import './bootstrap';
import { initAjaxNavigation } from './ajax';
import { initGenericStatistics } from './generic-statistics';
import { initPopulationStatistics } from './population-statistics';

const initPublicPage = () => {
    initPopulationStatistics();
    initGenericStatistics();

    const documentForm = document.querySelector('[data-r2-documents]');
    if (documentForm && documentForm.dataset.presigned === 'true' && documentForm.dataset.bound !== 'true') {
        const rows = [...documentForm.querySelectorAll('.letter-upload-row[data-requirement-key]')];
        const requiredRows = rows.filter((row) => row.dataset.requiredDocument === 'true');
        const requiredInputs = requiredRows.map((row) => row.querySelector('input[type="file"]'));
        const complete = new Set();
        documentForm.dataset.bound = 'true';
        rows.forEach((row) => {
            const input = row.querySelector('input[type="file"]');
            input.required = false;
            input.addEventListener('change', async () => {
                if (documentForm.dataset.fallback === 'true') return;
                const file = input.files?.[0];
                if (!file) return;
                if (!['image/jpeg', 'image/png', 'application/pdf'].includes(file.type) || file.size > 5242880) {
                    window.alert('File harus JPG, PNG, atau PDF dengan ukuran maksimal 5 MB.');
                    input.value = '';
                    return;
                }
                const key = row.dataset.requirementKey;
                row.querySelector('.letter-file-picker span').textContent = file.name;
                try {
                    const presign = await window.axios.post(documentForm.dataset.presignUrl, { requirement_key: key, original_name: file.name, mime_type: file.type, size_bytes: file.size });
                    const uploadResponse = await fetch(presign.data.upload_url, {
                        method: 'PUT',
                        headers: { 'Content-Type': file.type },
                        body: file,
                    });
                    if (!uploadResponse.ok) {
                        const body = await uploadResponse.text();
                        throw new Error(`Upload gagal (${uploadResponse.status}): ${body}`);
                    }
                    await window.axios.post(documentForm.dataset.completeUrl, { document_id: presign.data.document_id });
                    complete.add(key);
                    row.classList.add('is-uploaded');
                    if (requiredRows.every((requiredRow) => complete.has(requiredRow.dataset.requirementKey))) window.location.assign(documentForm.dataset.finalUrl);
                } catch (error) {
                    documentForm.dataset.fallback = 'true';
                    requiredInputs.forEach((requiredInput) => { requiredInput.required = true; });
                    window.alert('Upload langsung ke R2 dibatasi browser. File akan dikirim melalui server saat Anda menekan “Unggah & Lanjutkan”.');
                }
            });
        });
        documentForm.addEventListener('submit', (event) => {
            if (documentForm.dataset.fallback === 'true') return;
            if (!requiredRows.every((requiredRow) => complete.has(requiredRow.dataset.requirementKey))) {
                event.preventDefault();
                window.alert('Unggah semua dokumen wajib terlebih dahulu.');
            }
        });
    }

    const letterSelector = document.querySelector('[data-letter-selector]');

    if (letterSelector && letterSelector.dataset.bound !== 'true') {
        const choices = [...letterSelector.querySelectorAll('[data-letter-choice]')];
        const next = letterSelector.querySelector('[data-letter-next]');

        letterSelector.dataset.bound = 'true';
        choices.forEach((choice) => {
            choice.addEventListener('click', () => {
                choices.forEach((item) => {
                    const selected = item === choice;
                    item.classList.toggle('is-selected', selected);
                    item.setAttribute('aria-checked', String(selected));
                });
                if (next) next.href = choice.dataset.url;
            });
        });
    }

    const menuButton = document.querySelector('.menu-toggle');
    const menu = document.querySelector('#primary-menu');

    menuButton?.addEventListener('click', () => {
        const open = menu.classList.toggle('is-open');
        menuButton.setAttribute('aria-expanded', String(open));
    });

    const disableScrollReveal = Boolean(document.querySelector('[data-disable-scroll-reveal]'));
    const revealSections = disableScrollReveal
        ? []
        : [...document.querySelectorAll('main section:not(.hero-slider), .footer-wrapper .footer-widget')]
            .filter((section) => !section.closest('[data-no-scroll-reveal]'));

    if (disableScrollReveal) {
        document.querySelectorAll('.scroll-reveal').forEach((section) => {
            section.classList.remove('scroll-reveal', 'is-revealed');
        });
    }

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

            const isAccordionToggle = toggle.hasAttribute('data-profile-widget-toggle')
                || toggle.hasAttribute('data-sidebar-accordion-toggle');

            if (!expanded && isAccordionToggle) {
                const accordion = toggle.closest('[data-profile-accordion], [data-sidebar-accordion]');

                accordion?.querySelectorAll('[data-profile-widget-toggle][aria-expanded="true"], [data-sidebar-accordion-toggle][aria-expanded="true"]').forEach((openToggle) => {
                    if (openToggle === toggle) return;

                    openToggle.setAttribute('aria-expanded', 'false');
                    const openPanel = document.getElementById(openToggle.getAttribute('aria-controls'));
                    if (openPanel) openPanel.hidden = true;
                });
            }

            setExpanded(!expanded);
        });
    });

    const streetViewDialog = document.querySelector('[data-streetview-dialog]');

    if (streetViewDialog && streetViewDialog.dataset.bound !== 'true') {
        const frame = streetViewDialog.querySelector('[data-streetview-frame]');
        const close = () => streetViewDialog.close();

        streetViewDialog.dataset.bound = 'true';
        document.querySelectorAll('[data-streetview-open]').forEach((button) => {
            button.addEventListener('click', () => {
                if (frame && !frame.getAttribute('src')) frame.src = frame.dataset.src;
                streetViewDialog.showModal();
            });
        });
        streetViewDialog.querySelector('[data-streetview-close]')?.addEventListener('click', close);
        streetViewDialog.addEventListener('click', (event) => {
            if (event.target === streetViewDialog) close();
        });
    }

    const shareStatus = document.querySelector('[data-share-status]');
    const setShareStatus = (message) => {
        if (!shareStatus) return;

        shareStatus.textContent = message;
        window.setTimeout(() => {
            if (shareStatus.textContent === message) shareStatus.textContent = '';
        }, 2600);
    };

    const sharePanel = document.querySelector('.article-share-panel');
    const detailArticle = sharePanel?.closest('.news-detail-layout')?.querySelector('.single-article');
    const detailHero = detailArticle?.querySelector('.news-detail-hero');
    window.__detailShareCleanup?.();
    window.__detailShareCleanup = null;
    const alignSharePanel = () => {
        if (!sharePanel || !detailArticle || !detailHero) return;

        if (window.matchMedia('(max-width: 767px)').matches) {
            sharePanel.style.removeProperty('margin-top');
            return;
        }

        const articleRect = detailArticle.getBoundingClientRect();
        const heroRect = detailHero.getBoundingClientRect();
        sharePanel.style.marginTop = `${Math.max(0, heroRect.top - articleRect.top)}px`;
    };

    if (sharePanel && detailHero) {
        alignSharePanel();
        window.addEventListener('resize', alignSharePanel, { passive: true });
        let detailObserver = null;
        if (window.ResizeObserver) {
            detailObserver = new ResizeObserver(alignSharePanel);
            detailObserver.observe(detailArticle);
            detailObserver.observe(detailHero);
        }
        window.__detailShareCleanup = () => {
            window.removeEventListener('resize', alignSharePanel);
            detailObserver?.disconnect();
        };
    }

    const copyShareText = async (text) => {
        if (navigator.clipboard?.writeText) {
            await navigator.clipboard.writeText(text);
            return true;
        }

        const input = document.createElement('textarea');
        input.value = text;
        input.setAttribute('readonly', '');
        input.style.position = 'fixed';
        input.style.opacity = '0';
        document.body.appendChild(input);
        input.select();
        const copied = document.execCommand('copy');
        input.remove();

        return copied;
    };

    document.querySelector('[data-share-native]')?.addEventListener('click', async (event) => {
        const button = event.currentTarget;
        const url = button.dataset.shareUrl;
        const text = button.dataset.shareText;

        if (navigator.share) {
            await navigator.share({ title: text, text, url }).catch(() => {});
            return;
        }

        const copied = await copyShareText(`${text} ${url}`).catch(() => false);
        setShareStatus(copied ? 'Tautan disalin' : 'Salin tautan artikel');
    });

    document.querySelector('[data-share-instagram]')?.addEventListener('click', async (event) => {
        const link = event.currentTarget;
        const copied = await copyShareText(`${link.dataset.shareText} ${link.dataset.shareUrl}`).catch(() => false);
        setShareStatus(copied ? 'Tautan disalin' : 'Salin tautan artikel');
    });

    document.querySelectorAll('[data-print-article]').forEach((button) => {
        if (button.dataset.bound === 'true') return;

        button.dataset.bound = 'true';
        button.addEventListener('click', () => window.print());
    });

    document.querySelectorAll('[data-gallery-carousel]').forEach((carousel) => {
        const items = [...carousel.querySelectorAll('[data-carousel-item]')];
        const stage = carousel.querySelector('.home-gallery-stage');
        const previous = carousel.querySelector('[data-gallery-prev]');
        const next = carousel.querySelector('[data-gallery-next]');
        let selected = Math.floor(items.length / 2);
        const positionRadius = Math.floor(items.length / 2);
        const recyclingTimers = new WeakMap();
        let cardSelectionTimer = 0;

        const cancelCardSelection = () => {
            window.clearTimeout(cardSelectionTimer);
            cardSelectionTimer = 0;
        };

        const select = (index, direction = 0) => {
            if (!items.length) return;

            // Keep the active index in a circular range so the carousel never
            // gets stuck at either end of the five gallery items.
            selected = ((index % items.length) + items.length) % items.length;
            const recyclePosition = direction > 0 ? positionRadius + 1 : -(positionRadius + 1);
            const edgePosition = direction > 0 ? -positionRadius : positionRadius;
            const recycledItem = direction && items.length > 2
                ? items.find((item) => Number(item.dataset.carouselPosition) === edgePosition)
                : null;
            const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

            // Cancel a pending recycle before starting the next movement. This
            // keeps rapid clicks from leaving a card hidden at the wrong edge.
            items.forEach((item) => {
                const pending = recyclingTimers.get(item);
                if (pending) {
                    window.cancelAnimationFrame(pending.teleportFrame);
                    window.cancelAnimationFrame(pending.revealFrame);
                    window.clearTimeout(pending.timer);
                    recyclingTimers.delete(item);
                    item.classList.remove('is-recycling', 'is-recycling-teleport');
                }
            });

            // Let a visual copy of the outgoing card glide behind the stack
            // while the real card is recycled to the opposite side.
            if (recycledItem && stage && !reducedMotion) {
                const outgoingClone = recycledItem.cloneNode(true);
                outgoingClone.removeAttribute('data-gallery-item');
                outgoingClone.removeAttribute('data-carousel-item');
                outgoingClone.removeAttribute('aria-pressed');
                outgoingClone.setAttribute('aria-hidden', 'true');
                outgoingClone.tabIndex = -1;
                outgoingClone.classList.remove('is-outside', 'is-recycling', 'is-recycling-teleport');
                outgoingClone.classList.add('is-recycle-clone');
                outgoingClone.dataset.carouselPosition = edgePosition;
                stage.appendChild(outgoingClone);
                void outgoingClone.offsetWidth;

                window.requestAnimationFrame(() => {
                    outgoingClone.dataset.carouselPosition = direction > 0
                        ? -(positionRadius + 1)
                        : positionRadius + 1;
                    outgoingClone.classList.add('is-leaving');
                });

                window.setTimeout(() => outgoingClone.remove(), 500);
            }

            recycledItem?.classList.add('is-recycling', 'is-recycling-teleport');

            items.forEach((item, itemIndex) => {
                // Pick the shortest direction around the carousel. For five
                // items this keeps every card between positions -2 and 2,
                // including when moving from the last item back to the first.
                const position = ((itemIndex - selected + items.length + positionRadius) % items.length) - positionRadius;

                item.dataset.carouselPosition = item === recycledItem ? recyclePosition : position;
                item.classList.toggle('is-outside', Math.abs(position) > 2);
                item.setAttribute('aria-pressed', String(position === 0));
                item.setAttribute('aria-label', `${position === 0 ? 'Buka detail' : 'Pilih'} ${item.dataset.title}`);
            });

            if (recycledItem) {
                const targetPosition = direction > 0 ? positionRadius : -positionRadius;
                const pending = { teleportFrame: 0, revealFrame: 0, timer: 0 };

                if (reducedMotion) {
                    recycledItem.dataset.carouselPosition = targetPosition;
                    recycledItem.classList.remove('is-recycling', 'is-recycling-teleport');
                } else {
                    // Teleport the recycled card outside the visible stack,
                    // then slide and fade it in from that same edge.
                    void recycledItem.offsetWidth;
                    pending.teleportFrame = window.requestAnimationFrame(() => {
                        recycledItem.classList.remove('is-recycling-teleport');
                        pending.revealFrame = window.requestAnimationFrame(() => {
                            recycledItem.dataset.carouselPosition = targetPosition;
                            recycledItem.classList.remove('is-recycling');
                            pending.timer = window.setTimeout(() => {
                                recyclingTimers.delete(recycledItem);
                            }, 450);
                        });
                    });
                    recyclingTimers.set(recycledItem, pending);
                }
            }

            // A circular carousel only disables controls when there is
            // nothing to navigate (zero or one item).
            if (previous) previous.disabled = items.length < 2;
            if (next) next.disabled = items.length < 2;
        };

        items.forEach((item, index) => {
            item.addEventListener('click', (event) => {
                if (index === selected) return;

                event.stopImmediatePropagation();
                cancelCardSelection();

                const itemPosition = Number(item.dataset.carouselPosition);
                const direction = Math.sign(itemPosition);
                let remainingSteps = Math.abs(itemPosition);

                // Move through the same one-step animation used by the arrow
                // controls. An outer card therefore takes two smooth steps
                // instead of visibly rotating across the whole carousel.
                const selectNextStep = () => {
                    if (!direction || remainingSteps <= 0) return;

                    select(selected + direction, direction);
                    remainingSteps -= 1;

                    if (remainingSteps > 0) {
                        cardSelectionTimer = window.setTimeout(selectNextStep, 470);
                    }
                };

                selectNextStep();
            });
        });

        previous?.addEventListener('click', () => {
            cancelCardSelection();
            select(selected - 1, -1);
        });
        next?.addEventListener('click', () => {
            cancelCardSelection();
            select(selected + 1, 1);
        });
        select(selected);
    });

    const galleryDialog = document.querySelector('[data-gallery-dialog]');

    if (galleryDialog) {
        const image = galleryDialog.querySelector('[data-gallery-image]');
        const main = galleryDialog.querySelector('.gallery-dialog-main');
        const title = galleryDialog.querySelector('[data-gallery-title]');
        const caption = galleryDialog.querySelector('[data-gallery-caption]');
        const thumbnails = [...galleryDialog.querySelectorAll('[data-gallery-thumb]')];
        const galleryItems = [...document.querySelectorAll('[data-gallery-item]')];
        let slides = thumbnails.length ? thumbnails : galleryItems;
        const previousButton = galleryDialog.querySelector('[data-gallery-prev]');
        const nextButton = galleryDialog.querySelector('[data-gallery-next]');
        let selectedIndex = -1;
        let changeTimer;
        let settleTimer;
        const navigationTimers = new WeakMap();
        const galleryKey = (item) => item.dataset.galleryIndex
            ?? `${item.dataset.title}\u0000${item.dataset.caption}\u0000${item.dataset.image}`;

        const contextualCaption = (item) => {
            const text = item.dataset.caption?.trim() || '';
            return text || `Dokumentasi ${item.dataset.title || 'kegiatan desa'}.`;
        };

        const updateGalleryContent = (item, updateDetails = true) => {
            image.src = item.dataset.image;
            image.alt = item.dataset.title;

            // The dialog represents one gallery card. Its photo can change
            // while browsing, but the card's title and caption stay tied to
            // the item that opened the dialog.
            if (updateDetails) {
                title.textContent = item.dataset.title;
                caption.textContent = contextualCaption(item);
            }
        };

        const animateGalleryChange = (item, direction = 'next') => {
            if (!main || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                updateGalleryContent(item, false);
                return;
            }

            window.clearTimeout(changeTimer);
            window.clearTimeout(settleTimer);
            main.classList.remove('is-leaving', 'is-entering', 'is-next', 'is-prev');
            void main.offsetWidth;
            main.classList.add('is-leaving', direction === 'prev' ? 'is-prev' : 'is-next');

            changeTimer = window.setTimeout(() => {
                updateGalleryContent(item, false);
                main.classList.remove('is-leaving');
                main.classList.add('is-entering');

                settleTimer = window.setTimeout(() => {
                    main.classList.remove('is-entering', 'is-next', 'is-prev');
                }, 320);
            }, 140);
        };

        const selectGalleryItem = (item, animate = false, direction = 'next') => {
            const selectedKey = galleryKey(item);

            thumbnails.forEach((thumbnail) => {
                const isSelected = galleryKey(thumbnail) === selectedKey;
                thumbnail.classList.toggle('is-selected', isSelected);
                thumbnail.setAttribute('aria-pressed', String(isSelected));
            });

            const itemIndex = slides.findIndex((slide) => galleryKey(slide) === selectedKey);
            if (itemIndex >= 0) selectedIndex = itemIndex;

            if (animate) {
                animateGalleryChange(item, direction);
            } else {
                window.clearTimeout(changeTimer);
                window.clearTimeout(settleTimer);
                main?.classList.remove('is-leaving', 'is-entering', 'is-next', 'is-prev');
                updateGalleryContent(item);
            }
        };

        const selectAdjacentGalleryItem = (offset) => {
            if (!slides.length) return;

            const nextIndex = (selectedIndex + offset + slides.length) % slides.length;
            selectGalleryItem(slides[nextIndex], true, offset < 0 ? 'prev' : 'next');
        };

        const indicateKeyboardNavigation = (button) => {
            if (!button) return;

            window.clearTimeout(navigationTimers.get(button));
            button.classList.remove('is-keyboard-active');
            void button.offsetWidth;
            button.classList.add('is-keyboard-active');
            navigationTimers.set(button, window.setTimeout(() => {
                button.classList.remove('is-keyboard-active');
            }, 180));
        };

        galleryItems.forEach((item) => {
            item.addEventListener('click', () => {
                const group = item.dataset.galleryGroup;
                slides = thumbnails.filter((thumbnail) => thumbnail.dataset.galleryGroup === group);
                thumbnails.forEach((thumbnail) => {
                    thumbnail.hidden = thumbnail.dataset.galleryGroup !== group;
                });
                selectGalleryItem(slides[0] || item);
                galleryDialog.showModal();
            });
        });

        thumbnails.forEach((thumbnail) => {
            thumbnail.addEventListener('click', () => {
                const direction = thumbnails.indexOf(thumbnail) < selectedIndex ? 'prev' : 'next';
                selectGalleryItem(thumbnail, true, direction);
            });
        });

        previousButton?.addEventListener('click', () => selectAdjacentGalleryItem(-1));
        nextButton?.addEventListener('click', () => selectAdjacentGalleryItem(1));
        galleryDialog.querySelector('[data-gallery-close]')?.addEventListener('click', () => galleryDialog.close());
        galleryDialog.addEventListener('click', (event) => {
            if (event.target === galleryDialog) galleryDialog.close();
        });
        galleryDialog.addEventListener('keydown', (event) => {
            if (event.key === 'ArrowLeft') {
                event.preventDefault();
                indicateKeyboardNavigation(previousButton);
                selectAdjacentGalleryItem(-1);
            }
            if (event.key === 'ArrowRight') {
                event.preventDefault();
                indicateKeyboardNavigation(nextButton);
                selectAdjacentGalleryItem(1);
            }
        });
    }

    const backToTop = document.querySelector('.back-to-top');

    if (backToTop) {
        const updateButton = () => {
            const isDisabled = Boolean(document.querySelector('[data-hide-back-to-top]'));

            if (isDisabled) {
                backToTop.hidden = true;
                backToTop.classList.remove('is-visible');
                backToTop.setAttribute('aria-hidden', 'true');
                backToTop.tabIndex = -1;
                return;
            }

            const hero = document.querySelector('.hero-slider');
            const heroPassed = hero ? hero.getBoundingClientRect().bottom <= 0 : window.scrollY >= 500;
            backToTop.hidden = false;
            backToTop.classList.toggle('is-visible', heroPassed);
            backToTop.setAttribute('aria-hidden', String(!heroPassed));
            backToTop.tabIndex = heroPassed ? 0 : -1;
        };

        if (!backToTop.dataset.ajaxBound) {
            backToTop.dataset.ajaxBound = 'true';
            window.addEventListener('scroll', updateButton, { passive: true });
            backToTop.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
        }

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
    explicitOnly: true,
});

initPublicPage();
