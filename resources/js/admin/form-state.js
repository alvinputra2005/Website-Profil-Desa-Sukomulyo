const bindDirtyForms = (root) => {
    root.querySelectorAll('[data-dirty-form]').forEach((form) => {
        if (form.dataset.dirtyBound) return;
        form.dataset.dirtyBound = 'true';
        form.addEventListener('input', () => { form.dataset.dirty = 'true'; });
        form.addEventListener('submit', () => { delete form.dataset.dirty; });
    });

    if (window.__dirtyFormWarningBound) return;
    window.__dirtyFormWarningBound = true;
    window.addEventListener('beforeunload', (event) => {
        if (!document.querySelector('[data-dirty-form][data-dirty="true"]')) return;
        event.preventDefault();
        event.returnValue = '';
    });
};

const bindPublicationStatus = (root) => {
    root.querySelectorAll('[data-submit-status]').forEach((button) => {
        if (button.dataset.submitStatusBound) return;
        button.dataset.submitStatusBound = 'true';
        button.addEventListener('click', () => {
            const status = button.form?.querySelector('[data-publication-status]');
            if (status) status.value = button.dataset.submitStatus;
        });
    });
};

const bindTitleCounter = (root) => {
    const title = root.querySelector('#title');
    const counter = root.querySelector('#title-count');
    if (!title || !counter || title.dataset.counterBound) return;

    title.dataset.counterBound = 'true';
    const update = () => { counter.textContent = String(title.value.length); };
    title.addEventListener('input', update);
    title.form?.addEventListener('reset', () => window.setTimeout(update, 0));
    update();
};

const bindFormReset = (root) => {
    root.querySelectorAll('form').forEach((form) => {
        if (form.dataset.resetSyncBound) return;
        form.dataset.resetSyncBound = 'true';
        form.addEventListener('reset', () => {
            window.setTimeout(() => {
                delete form.dataset.dirty;
                if (window.jQuery?.fn?.select2) {
                    window.jQuery(form).find('.select2').trigger('change.select2');
                }
            }, 0);
        });
    });
};

export const init = (root = document) => {
    bindDirtyForms(root);
    bindPublicationStatus(root);
    bindTitleCounter(root);
    bindFormReset(root);
};
