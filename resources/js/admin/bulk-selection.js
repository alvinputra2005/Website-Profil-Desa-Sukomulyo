const bindBulkSelection = (root, { selectAll, item, button }) => {
    const master = root.querySelector(selectAll);
    if (!master || master.dataset.bulkBound) return;
    master.dataset.bulkBound = 'true';

    const checkboxes = [...root.querySelectorAll(item)];
    const bulkButton = root.querySelector(button);
    const update = () => {
        const checked = checkboxes.filter((checkbox) => checkbox.checked).length;
        if (bulkButton) bulkButton.disabled = checked === 0;
        master.checked = checkboxes.length > 0 && checked === checkboxes.length;
        master.indeterminate = checked > 0 && checked < checkboxes.length;
    };

    master.addEventListener('change', () => {
        checkboxes.forEach((checkbox) => { checkbox.checked = master.checked; });
        update();
    });
    checkboxes.forEach((checkbox) => checkbox.addEventListener('change', update));
    update();
};

export const init = (root = document) => {
    bindBulkSelection(root, {
        selectAll: '[data-check-all-officials]',
        item: '[data-official-check]',
        button: '[data-bulk-delete]',
    });
    bindBulkSelection(root, {
        selectAll: '[data-statistics-select-all]',
        item: '[data-statistics-check]',
        button: '[data-statistics-bulk-delete]',
    });
};
