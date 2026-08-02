export const init = (root = document) => {
    const scope = root.querySelector('[data-letter-service-form]') || root;
    const list = scope.querySelector('#requirements-list');
    const template = scope.querySelector('#requirement-row-template');
    const addButton = scope.querySelector('#add-requirement');
    const countLabel = scope.querySelector('#requirements-help');

    if (!list || !template || !addButton || !countLabel || list.dataset.initialized === 'true') return;

    list.dataset.initialized = 'true';
    let nextIndex = list.querySelectorAll('.requirement-row').length;

    const updateRows = () => {
        const rows = list.querySelectorAll('.requirement-row');
        countLabel.textContent = `${rows.length} persyaratan dokumen ditambahkan.`;
        rows.forEach((row) => {
            const button = row.querySelector('.btn-remove-requirement');
            button.disabled = rows.length === 1;
            button.title = rows.length === 1 ? 'Minimal satu persyaratan dokumen' : 'Hapus persyaratan';
        });
    };

    addButton.addEventListener('click', () => {
        const fragment = template.content.cloneNode(true);
        const row = fragment.querySelector('.requirement-row');
        const fieldNames = { code: 'code', label: 'label', description: 'description', 'required-hidden': 'required', required: 'required' };

        row.querySelectorAll('[data-field]').forEach((field) => {
            const fieldName = fieldNames[field.dataset.field];
            field.name = `requirements[${nextIndex}][${fieldName}]`;
            field.id = `requirement-${field.dataset.field}-${nextIndex}`;
        });
        row.querySelectorAll('label.sr-only').forEach((label) => {
            const input = label.parentElement.querySelector('input[data-field]');
            if (input) label.htmlFor = input.id;
        });

        nextIndex++;
        list.appendChild(fragment);
        updateRows();
        list.querySelector('.requirement-row:last-child input[data-field="code"]').focus();
    });

    list.addEventListener('click', (event) => {
        const button = event.target.closest('.btn-remove-requirement');
        if (!button || list.querySelectorAll('.requirement-row').length <= 1) return;
        button.closest('.requirement-row').remove();
        updateRows();
    });

    updateRows();
};
