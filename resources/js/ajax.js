const AJAX_STATE_KEY = '__sukomulyoAjaxState';

const isSameOrigin = (url) => url.origin === window.location.origin;

const isModifiedClick = (event) => event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey;

const shouldSkipLink = (link, event) => {
    if (isModifiedClick(event) || link.hasAttribute('download') || link.dataset.noAjax !== undefined) return true;
    if (link.target && link.target !== '_self') return true;

    const href = link.getAttribute('href');
    if (!href || href.startsWith('#')) return true;

    const url = new URL(link.href, window.location.href);
    if (!isSameOrigin(url) || !['http:', 'https:'].includes(url.protocol)) return true;

    return /\.(?:pdf|csv|xlsx?|docx?|zip|jpe?g|png|gif|webp|svg)(?:$|\?)/i.test(url.pathname);
};

const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.content || '';

const setBusy = (busy) => {
    const root = document.querySelector('[data-ajax-root]');
    if (root) root.setAttribute('aria-busy', String(busy));

    let progress = document.querySelector('[data-ajax-progress]');
    if (busy && !progress) {
        progress = document.createElement('div');
        progress.dataset.ajaxProgress = '';
        progress.setAttribute('role', 'status');
        progress.setAttribute('aria-live', 'polite');
        progress.textContent = 'Memuat halaman…';
        document.body.append(progress);
    }

    progress?.classList.toggle('is-visible', busy);
};

const confirmAction = async (element) => {
    const message = element.dataset.confirm;
    if (!message) return true;

    if (window.Swal) {
        const result = await window.Swal.fire({
            title: 'Konfirmasi',
            text: message,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#526b42',
            cancelButtonColor: '#6f7870',
            confirmButtonText: 'Ya, lanjutkan',
            cancelButtonText: 'Batal',
        });

        return result.isConfirmed;
    }

    return window.confirm(message);
};

const confirmDiscardChanges = () => {
    const dirtyForm = document.querySelector('[data-dirty-form][data-dirty="true"]');
    if (!dirtyForm) return true;

    return window.confirm('Perubahan yang belum disimpan akan hilang. Tetap pindah halaman?');
};

const updateDocumentMetadata = (incomingDocument) => {
    if (incomingDocument.title) document.title = incomingDocument.title;

    const incomingDescription = incomingDocument.querySelector('meta[name="description"]');
    const currentDescription = document.querySelector('meta[name="description"]');
    if (incomingDescription && currentDescription) {
        currentDescription.setAttribute('content', incomingDescription.content);
    } else if (incomingDescription && !currentDescription) {
        document.head.append(incomingDescription.cloneNode(true));
    } else if (!incomingDescription) {
        currentDescription?.remove();
    }
};

const renderPageResponse = async (response, state, historyMode = 'push') => {
    const contentType = response.headers.get('content-type') || '';
    const finalUrl = response.url || window.location.href;

    if (!contentType.includes('text/html')) {
        window.location.assign(finalUrl);
        return false;
    }

    const html = await response.text();
    const incomingDocument = new DOMParser().parseFromString(html, 'text/html');
    const currentRoot = document.querySelector(state.rootSelector);
    const incomingRoot = incomingDocument.querySelector(state.rootSelector);

    if (!currentRoot || !incomingRoot) {
        window.location.assign(finalUrl);
        return false;
    }

    window.dispatchEvent(new CustomEvent('ajax:before-render', {
        detail: { url: finalUrl, status: response.status },
    }));

    incomingRoot.setAttribute('data-ajax-root', '');
    currentRoot.replaceWith(incomingRoot);
    updateDocumentMetadata(incomingDocument);

    if (historyMode === 'replace') {
        window.history.replaceState({}, '', finalUrl);
    } else if (historyMode === 'push') {
        window.history.pushState({}, '', finalUrl);
    }

    window.dispatchEvent(new CustomEvent('ajax:page-loaded', {
        detail: { url: finalUrl, status: response.status },
    }));

    state.onRender?.(incomingRoot);
    window.scrollTo({ top: 0, left: 0, behavior: 'auto' });

    return true;
};

const fetchPage = async (url, state, options = {}, historyMode = 'push') => {
    state.controller?.abort();
    const controller = new AbortController();
    const { headers = {}, ...requestOptions } = options;
    state.controller = controller;
    setBusy(true);

    try {
        const response = await fetch(url, {
            credentials: 'same-origin',
            signal: controller.signal,
            ...requestOptions,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                Accept: 'text/html, application/xhtml+xml',
                ...headers,
            },
        });

        return await renderPageResponse(response, state, historyMode);
    } catch (error) {
        if (error.name !== 'AbortError') window.location.assign(url);
        return false;
    } finally {
        if (state.controller === controller) {
            setBusy(false);
            state.controller = null;
        }
    }
};

const submitForm = async (form, state) => {
    if (form.dataset.ajaxSubmitting === 'true') return;
    if (!(await confirmAction(form))) return;

    form.dataset.ajaxSubmitting = 'true';
    form.setAttribute('aria-busy', 'true');
    setBusy(true);
    const submitButtons = [...form.querySelectorAll('button[type="submit"], input[type="submit"]')];
    submitButtons.forEach((button) => { button.disabled = true; });

    const method = (form.getAttribute('method') || 'get').toUpperCase();
    const action = new URL(form.getAttribute('action') || window.location.href, window.location.href);
    state.controller?.abort();
    const controller = new AbortController();
    state.controller = controller;

    try {
        if (method === 'GET') {
            const query = new URLSearchParams();
            new FormData(form).forEach((value, key) => {
                if (typeof value === 'string' && value !== '') query.append(key, value);
            });
            action.search = query.toString();
            await fetchPage(action.href, state);
            return;
        }

        const body = new FormData(form);
        const response = await fetch(action.href, {
            method,
            body,
            credentials: 'same-origin',
            signal: controller.signal,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                Accept: 'text/html, application/xhtml+xml',
                'X-CSRF-TOKEN': csrfToken(),
            },
        });

        await renderPageResponse(response, state);
    } catch (error) {
        if (error.name !== 'AbortError') {
            const message = 'Permintaan gagal dikirim. Periksa koneksi lalu coba lagi.';
            if (window.Swal) {
                window.Swal.fire({ title: 'Gagal', text: message, icon: 'error' });
            } else {
                window.alert(message);
            }
        }
    } finally {
        form.removeAttribute('aria-busy');
        delete form.dataset.ajaxSubmitting;
        submitButtons.forEach((button) => { button.disabled = false; });
        if (state.controller === controller) {
            setBusy(false);
            state.controller = null;
        }
    }
};

export const initAjaxNavigation = ({ rootSelector, onRender } = {}) => {
    if (!rootSelector) return;

    const existing = window[AJAX_STATE_KEY];
    if (existing) {
        existing.rootSelector = rootSelector;
        existing.onRender = onRender;
        document.querySelector(rootSelector)?.setAttribute('data-ajax-root', '');
        return existing;
    }

    const state = { rootSelector, onRender, controller: null };
    window[AJAX_STATE_KEY] = state;
    document.querySelector(rootSelector)?.setAttribute('data-ajax-root', '');

    document.addEventListener('click', (event) => {
        const link = event.target.closest?.('a');
        if (!link || shouldSkipLink(link, event)) return;

        const url = new URL(link.href, window.location.href);
        event.preventDefault();
        if (!confirmDiscardChanges()) return;
        fetchPage(url.href, state);
    });

    document.addEventListener('submit', (event) => {
        const form = event.target.closest?.('form');
        if (!form || form.dataset.noAjax !== undefined || form.target) return;
        if (!isSameOrigin(new URL(form.action || window.location.href, window.location.href))) return;

        event.preventDefault();
        submitForm(form, state);
    });

    window.addEventListener('popstate', () => {
        fetchPage(window.location.href, state, {}, 'none');
    });

    return state;
};
