import { bindImagePreparation, prepareImageInput } from '../image-upload';

const emptyPreview = (preview) => {
    preview.innerHTML = '<i class="fa fa-picture-o" aria-hidden="true"></i><span>Belum ada gambar</span>';
    preview.classList.add('is-empty');
    preview.setAttribute('aria-label', 'Pilih gambar untuk diunggah');
};

export const init = (root = document) => {
    root.querySelectorAll('[data-image-picker]').forEach((picker) => {
        if (picker.dataset.imagePickerBound) return;

        const select = picker.querySelector('[data-image-select]');
        const upload = picker.querySelector('[data-image-upload]');
        const preview = picker.querySelector('[data-image-preview]');
        const alt = picker.querySelector('[data-image-alt]');
        const remove = picker.querySelector('[data-image-remove]');
        if (!select || !upload || !preview || !alt || !remove) return;

        picker.dataset.imagePickerBound = 'true';
        const initialPreviewUrl = preview.querySelector('img')?.getAttribute('src') || '';

        const clearImage = () => {
            select.value = '';
            upload.value = '';
            alt.value = '';
            remove.value = '1';
            emptyPreview(preview);
        };

        const showPreview = (url) => {
            if (!url) {
                emptyPreview(preview);
                return;
            }

            preview.replaceChildren();
            preview.classList.remove('is-empty');
            preview.setAttribute('aria-label', 'Pratinjau gambar');

            const image = document.createElement('img');
            image.src = url;
            image.alt = alt.value;
            preview.appendChild(image);

            const clear = document.createElement('button');
            clear.type = 'button';
            clear.className = 'image-picker-remove';
            clear.dataset.imageClear = '';
            clear.dataset.toggle = 'tooltip';
            clear.title = 'Hapus gambar';
            clear.setAttribute('aria-label', 'Hapus gambar');
            clear.innerHTML = '<i class="fa fa-trash" aria-hidden="true"></i>';
            clear.addEventListener('click', clearImage);
            preview.appendChild(clear);
            if (window.jQuery) window.jQuery(clear).tooltip();
        };

        const openUpload = (event) => {
            if (event.target.closest?.('[data-image-clear]')) return;
            if (!preview.classList.contains('is-empty')) return;
            if (event.type === 'keydown' && !['Enter', ' '].includes(event.key)) return;
            event.preventDefault();
            upload.click();
        };

        preview.addEventListener('click', openUpload);
        preview.addEventListener('keydown', openUpload);
        upload.addEventListener('change', async () => {
            const file = upload.files?.[0];
            if (!file) return;
            select.value = '';
            remove.value = '0';
            showPreview(URL.createObjectURL(file));
            if (!alt.value) alt.value = file.name.replace(/\.[^/.]+$/, '');
            await prepareImageInput(upload);
        });
        alt.addEventListener('input', () => {
            const image = preview.querySelector('img');
            if (image) image.alt = alt.value;
        });
        picker.querySelector('[data-image-clear]')?.addEventListener('click', clearImage);
        upload.form?.addEventListener('reset', () => {
            window.setTimeout(() => {
                if (initialPreviewUrl) showPreview(initialPreviewUrl);
                else emptyPreview(preview);
            }, 0);
        });
        bindImagePreparation(upload.form);
    });
};
