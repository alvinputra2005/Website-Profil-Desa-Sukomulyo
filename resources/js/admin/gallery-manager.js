import { prepareImageFile } from '../image-upload';

const MAX_FILES = 5;
const MAX_FILE_SIZE = 5 * 1024 * 1024;

export const init = (root = document) => {
    const manager = root.querySelector('[data-gallery-manager]');
    if (!manager || manager.dataset.galleryBound) return;

    const fileInput = manager.querySelector('[data-gallery-files]');
    const grid = manager.querySelector('[data-gallery-photo-grid]');
    if (!fileInput || !grid) return;
    manager.dataset.galleryBound = 'true';

    let selectedFiles = [];
    let draggedCard = null;
    const addButton = grid.querySelector('[data-gallery-add]');
    const uploadStatus = manager.querySelector('[data-gallery-upload-status]');
    const initialCards = [...grid.querySelectorAll('[data-gallery-photo-card]')];
    const activeCards = () => [...grid.querySelectorAll('[data-gallery-photo-card]:not(.is-removed)')];

    const syncFiles = () => {
        const transfer = new DataTransfer();
        selectedFiles.forEach((file) => transfer.items.add(file));
        fileInput.files = transfer.files;
        fileInput.dataset.imagePrepared = 'true';
    };

    const updateCards = () => {
        let uploadIndex = 0;
        activeCards().forEach((card, index) => {
            card.classList.toggle('is-cover', index === 0);
            card.querySelector('[data-gallery-cover]')?.setAttribute('title', index === 0 ? 'Gambar sampul' : 'Jadikan sampul');
            const order = card.querySelector('[data-gallery-order]');
            if (order) order.value = String(index + 1);

            if (card.matches('[data-new-photo]')) {
                card.dataset.fileIndex = String(uploadIndex);
                const caption = card.querySelector('[data-gallery-caption]');
                if (caption) caption.name = `gallery_item_captions[${uploadIndex}]`;
                card.querySelector('[data-gallery-sequence]').value = `new:${uploadIndex}`;
                uploadIndex += 1;
            }
        });
        syncFiles();
    };

    const renderNewCards = () => {
        grid.querySelectorAll('[data-new-photo]').forEach((card) => card.remove());
        selectedFiles.forEach((file) => {
            const card = document.createElement('div');
            card.className = 'gallery-photo-card';
            card.draggable = true;
            card.dataset.galleryPhotoCard = '';
            card.dataset.newPhoto = '';
            card.innerHTML = '<img alt=""><button type="button" class="gallery-photo-cover" data-gallery-cover title="Jadikan sampul" aria-label="Jadikan sampul"><i class="fa fa-star"></i></button><button type="button" class="gallery-photo-remove" data-gallery-remove title="Hapus gambar" aria-label="Hapus gambar">&times;</button><span class="gallery-photo-drag"><i class="fa fa-bars"></i></span><input type="hidden" data-gallery-caption><input type="hidden" name="gallery_sequence[]" data-gallery-sequence>';
            card.querySelector('img').src = URL.createObjectURL(file);
            grid.insertBefore(card, addButton);
        });
        updateCards();
    };

    addButton?.addEventListener('click', () => fileInput.click());
    fileInput.addEventListener('change', async () => {
        const incoming = [...fileInput.files];
        if (!incoming.length) return;

        const remainingSlots = Math.max(0, MAX_FILES - selectedFiles.length);
        const acceptedIncoming = incoming.slice(0, remainingSlots);
        const rejectedCount = incoming.length - acceptedIncoming.length;
        fileInput.disabled = true;

        if (uploadStatus) {
            uploadStatus.hidden = false;
            uploadStatus.classList.remove('text-danger');
            uploadStatus.textContent = acceptedIncoming.length
                ? `Menyiapkan ${acceptedIncoming.length} gambar...`
                : `Maksimal ${MAX_FILES} gambar dapat diunggah per sekali simpan.`;
        }

        const prepared = [];
        const failures = [];
        for (const file of acceptedIncoming) {
            try {
                const result = await prepareImageFile(file);
                if (result.size > MAX_FILE_SIZE) failures.push(`${file.name} (lebih dari 5 MB)`);
                else prepared.push(result);
            } catch {
                failures.push(`${file.name} (gambar tidak dapat dibaca)`);
            }
        }

        selectedFiles = [...selectedFiles, ...prepared].slice(0, MAX_FILES);
        renderNewCards();
        fileInput.disabled = false;

        if (!uploadStatus) return;
        if (failures.length || rejectedCount) {
            const messages = [];
            uploadStatus.classList.add('text-danger');
            if (rejectedCount) messages.push(`${rejectedCount} gambar tidak ditambahkan karena batas maksimal adalah ${MAX_FILES} gambar`);
            if (failures.length) messages.push(`Tidak dapat menambahkan: ${failures.join(', ')}`);
            uploadStatus.textContent = `${messages.join('. ')}.`;
        } else {
            uploadStatus.hidden = true;
            uploadStatus.textContent = '';
        }
    });

    grid.addEventListener('click', (event) => {
        const card = event.target.closest('[data-gallery-photo-card]');
        if (!card) return;

        if (event.target.closest('[data-gallery-cover]')) {
            grid.insertBefore(card, grid.querySelector('[data-gallery-photo-card]:not(.is-removed)'));
            selectedFiles = activeCards()
                .filter((item) => item.matches('[data-new-photo]'))
                .map((item) => selectedFiles[Number(item.dataset.fileIndex)]);
        } else if (event.target.closest('[data-gallery-remove]')) {
            if (card.matches('[data-new-photo]')) {
                selectedFiles.splice(Number(card.dataset.fileIndex), 1);
                card.remove();
            } else {
                card.classList.add('is-removed');
                card.querySelector('[data-gallery-remove-input]').checked = true;
            }
        } else {
            return;
        }
        updateCards();
    });

    grid.addEventListener('dragstart', (event) => {
        draggedCard = event.target.closest('[data-gallery-photo-card]');
        draggedCard?.classList.add('is-dragging');
    });
    grid.addEventListener('dragover', (event) => {
        if (!draggedCard) return;
        event.preventDefault();
        const target = event.target.closest('[data-gallery-photo-card]');
        if (!target || target === draggedCard || target.classList.contains('is-removed')) return;
        const box = target.getBoundingClientRect();
        grid.insertBefore(draggedCard, event.clientX < box.left + box.width / 2 ? target : target.nextSibling);
    });
    grid.addEventListener('dragend', () => {
        draggedCard?.classList.remove('is-dragging');
        draggedCard = null;
        selectedFiles = activeCards()
            .filter((card) => card.matches('[data-new-photo]'))
            .map((card) => selectedFiles[Number(card.dataset.fileIndex)]);
        updateCards();
    });
    fileInput.form?.addEventListener('reset', () => {
        window.setTimeout(() => {
            selectedFiles = [];
            if (uploadStatus) {
                uploadStatus.hidden = true;
                uploadStatus.classList.remove('text-danger');
                uploadStatus.textContent = '';
            }
            grid.querySelectorAll('[data-new-photo]').forEach((card) => card.remove());
            initialCards.forEach((card) => {
                card.classList.remove('is-removed', 'is-dragging');
                grid.insertBefore(card, addButton);
            });
            updateCards();
        }, 0);
    });
    updateCards();
};
