const normalize = (value = '') => value
    .toLocaleLowerCase('id-ID')
    .normalize('NFD')
    .replace(/\p{Diacritic}/gu, '')
    .trim();

const setExpanded = (toggle, expanded) => {
    if (!toggle) return;

    const panel = document.getElementById(toggle.getAttribute('aria-controls'));
    toggle.setAttribute('aria-expanded', String(expanded));
    if (panel) panel.hidden = !expanded;
};

export const initAdministrativeServices = () => {
    window.__administrationCleanup?.();
    window.__administrationCleanup = null;

    const root = document.querySelector('[data-administrative-services]');
    if (!root || root.dataset.administrationBound === 'true') return;

    const input = root.querySelector('[data-administration-search]');
    const clearButton = root.querySelector('[data-administration-search-clear]');
    const emptyState = root.querySelector('[data-administration-empty]');
    const serviceItems = [...root.querySelectorAll('[data-service-item]')];
    let filterTimer = 0;

    if (!input || !clearButton || !emptyState) return;
    root.dataset.administrationBound = 'true';

    const resetAccordion = () => {
        serviceItems.forEach((item, index) => {
            const toggle = item.querySelector(':scope > .service-accordion-heading .service-accordion-toggle');
            setExpanded(toggle, index === 0);

            item.querySelectorAll('[data-service-child-item]').forEach((child, childIndex) => {
                const childToggle = child.querySelector('.service-child-toggle');
                setExpanded(childToggle, index === 0 && childIndex === 0);
            });
        });
    };

    const filterServices = () => {
        const query = normalize(input.value);
        let matchCount = 0;
        let firstMatchingItem = null;

        clearButton.hidden = query === '';

        serviceItems.forEach((item) => {
            const children = [...item.querySelectorAll('[data-service-child-item]')];
            const ownMatch = query === '' || normalize(item.dataset.serviceOwnSearch).includes(query);
            let childMatchCount = 0;
            let firstMatchingChild = null;

            children.forEach((child) => {
                const childMatches = query === ''
                    || ownMatch
                    || normalize(child.dataset.serviceSearch).includes(query);

                child.hidden = !childMatches;
                if (childMatches) {
                    childMatchCount += 1;
                    firstMatchingChild ??= child;
                }
            });

            const matches = query === ''
                || ownMatch
                || normalize(item.dataset.serviceSearch).includes(query)
                || childMatchCount > 0;

            item.hidden = !matches;
            if (matches) {
                matchCount += 1;
                firstMatchingItem ??= item;
            }

            if (query !== '' && children.length) {
                children.forEach((child) => {
                    const childToggle = child.querySelector('.service-child-toggle');
                    setExpanded(childToggle, child === firstMatchingChild);
                });
            }
        });

        if (query === '') {
            resetAccordion();
        } else {
            serviceItems.forEach((item) => {
                const toggle = item.querySelector(':scope > .service-accordion-heading .service-accordion-toggle');
                setExpanded(toggle, item === firstMatchingItem);
            });
        }

        emptyState.hidden = matchCount > 0;
    };

    const queueFilter = () => {
        window.clearTimeout(filterTimer);
        filterTimer = window.setTimeout(filterServices, 80);
    };

    const clearSearch = () => {
        input.value = '';
        filterServices();
        input.focus();
    };

    input.addEventListener('input', queueFilter);
    clearButton.addEventListener('click', clearSearch);
    filterServices();

    const cleanup = () => {
        window.clearTimeout(filterTimer);
        input.removeEventListener('input', queueFilter);
        clearButton.removeEventListener('click', clearSearch);
        window.removeEventListener('ajax:before-render', cleanup);
        delete root.dataset.administrationBound;
        if (window.__administrationCleanup === cleanup) {
            window.__administrationCleanup = null;
        }
    };

    window.__administrationCleanup = cleanup;
    window.addEventListener('ajax:before-render', cleanup, { once: true });
};
