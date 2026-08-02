import { bindImagePreparation, prepareImageInput } from '../image-upload';

export const init = (root = document) => {
    const form = root.querySelector('#official-form');
    if (!form || form.dataset.officialBound) return;
    form.dataset.officialBound = 'true';

    const sourceInputs = [...form.querySelectorAll('input[name="source"]')];
    const residentPicker = form.querySelector('[data-resident-picker]');
    const residentSelect = form.querySelector('#resident_id');
    const residentFields = [...form.querySelectorAll('[data-resident-value]')];
    const fullNamePreview = form.querySelector('[data-full-name-preview]');
    const color = form.querySelector('#organization_color');
    const colorPreview = form.querySelector('.official-color-preview');
    const camera = form.querySelector('#photo_camera');
    const imagePreview = form.querySelector('[data-image-preview]');

    const currentSource = () => sourceInputs.find((input) => input.checked)?.value || 'external';
    const updateFullName = () => {
        const prefix = form.querySelector('#title_prefix')?.value.trim() || '';
        const name = form.querySelector('#name')?.value.trim() || '';
        const suffix = form.querySelector('#title_suffix')?.value.trim() || '';
        if (fullNamePreview) {
            fullNamePreview.value = [prefix, name].filter(Boolean).join(' ') + (suffix ? `, ${suffix}` : '');
        }
    };
    const populateResident = () => {
        if (currentSource() !== 'resident' || !residentSelect) return;
        const option = residentSelect.options[residentSelect.selectedIndex];
        if (!option?.value) return;

        residentFields.forEach((field) => {
            field.value = option.dataset[field.dataset.residentValue] || '';
            if (window.jQuery && field.matches('select')) {
                window.jQuery(field).trigger('change.select2');
            }
        });
        updateFullName();
    };
    const updateSource = () => {
        const fromResident = currentSource() === 'resident';
        if (residentPicker) residentPicker.hidden = !fromResident;
        if (residentSelect) residentSelect.required = fromResident;
        residentFields.forEach((field) => {
            field.readOnly = fromResident && !field.matches('select');
            field.classList.toggle('official-readonly', fromResident);
        });
        if (fromResident) populateResident();
    };

    sourceInputs.forEach((input) => input.addEventListener('change', updateSource));
    residentSelect?.addEventListener('change', populateResident);
    if (window.jQuery && residentSelect) {
        window.jQuery(residentSelect).on('select2:select select2:clear', populateResident);
    }
    ['name', 'title_prefix', 'title_suffix'].forEach((id) => {
        form.querySelector(`#${id}`)?.addEventListener('input', updateFullName);
    });
    color?.addEventListener('input', () => {
        if (/^#[0-9A-Fa-f]{6}$/.test(color.value) && colorPreview) {
            colorPreview.style.background = color.value;
        }
    });
    camera?.addEventListener('change', async () => {
        const file = camera.files?.[0];
        if (!file || !imagePreview) return;

        const image = document.createElement('img');
        image.src = URL.createObjectURL(file);
        image.alt = 'Pratinjau foto dari kamera';
        imagePreview.replaceChildren(image);
        const remove = form.querySelector('[data-image-remove]');
        if (remove) remove.value = '0';

        const prepared = await prepareImageInput(camera);
        if (prepared && prepared !== file) image.src = URL.createObjectURL(prepared);
    });
    form.addEventListener('reset', () => {
        window.setTimeout(() => {
            updateSource();
            updateFullName();
            if (colorPreview) colorPreview.style.background = color?.value || '#526b42';
        }, 0);
    });

    bindImagePreparation(form);
    updateSource();
    updateFullName();
};
