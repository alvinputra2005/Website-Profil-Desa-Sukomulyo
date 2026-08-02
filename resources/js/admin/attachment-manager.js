const MAX_FILES = 10;
const MAX_FILE_SIZE = 10 * 1024 * 1024;

export const init = (root = document) => {
    const manager = root.querySelector('[data-publication-attachments]');
    if (!manager || manager.dataset.attachmentsBound) return;

    const fileInput = manager.querySelector('[data-attachment-files]');
    const list = manager.querySelector('[data-attachment-list]');
    if (!fileInput || !list) return;
    manager.dataset.attachmentsBound = 'true';

    let selectedFiles = [];
    const rows = () => [...list.querySelectorAll('[data-attachment-row]')];
    const syncOrder = () => {
        rows().forEach((row, index) => {
            const order = row.querySelector('[data-attachment-order]');
            if (order) order.value = String(index + 1);
        });
    };
    const syncFiles = () => {
        const transfer = new DataTransfer();
        selectedFiles.forEach((file) => transfer.items.add(file));
        fileInput.files = transfer.files;
    };
    const renderNewRows = () => {
        list.querySelectorAll('[data-new-attachment]').forEach((row) => row.remove());
        selectedFiles.forEach((file, index) => {
            const row = document.createElement('div');
            row.className = 'publication-attachment-row';
            row.dataset.attachmentRow = '';
            row.dataset.newAttachment = '';
            row.dataset.fileIndex = String(index);
            row.innerHTML = `
                <span class="publication-attachment-handle" title="Urutkan lampiran"><i class="fa fa-bars"></i></span>
                <i class="fa fa-file-pdf-o text-red publication-attachment-file-icon"></i>
                <div class="publication-attachment-fields">
                    <strong></strong>
                    <small></small>
                    <input type="text" name="attachment_upload_titles[${index}]" class="form-control input-sm" placeholder="Judul tampilan (opsional)">
                    <input type="hidden" name="attachment_sequence[]" value="new:${index}" data-attachment-sequence>
                </div>
                <div class="publication-attachment-controls">
                    <button type="button" class="btn btn-default btn-xs" data-attachment-up aria-label="Naikkan urutan"><i class="fa fa-arrow-up"></i></button>
                    <button type="button" class="btn btn-default btn-xs" data-attachment-down aria-label="Turunkan urutan"><i class="fa fa-arrow-down"></i></button>
                    <button type="button" class="btn btn-danger btn-xs" data-attachment-remove-new aria-label="Hapus lampiran baru"><i class="fa fa-times"></i></button>
                </div>`;
            row.querySelector('strong').textContent = file.name;
            row.querySelector('small').textContent = `PDF baru · ${(file.size / 1048576).toLocaleString('id-ID', { maximumFractionDigits: 1 })} MB`;
            list.appendChild(row);
        });
        syncOrder();
        syncFiles();
    };

    fileInput.addEventListener('change', () => {
        const incoming = [...(fileInput.files || [])];
        const invalid = incoming.find((file) => file.type !== 'application/pdf' && !file.name.toLowerCase().endsWith('.pdf'));
        const oversized = incoming.find((file) => file.size > MAX_FILE_SIZE);
        if (invalid || oversized || selectedFiles.length + incoming.length > MAX_FILES) {
            const message = invalid
                ? 'Hanya file PDF yang dapat dilampirkan.'
                : oversized
                    ? 'Ukuran setiap PDF maksimal 10 MB.'
                    : 'Maksimal 10 PDF per sekali simpan.';
            if (window.AdminDialog || window.Swal) (window.AdminDialog || window.Swal).fire({ icon: 'error', title: 'Lampiran tidak valid', text: message });
            else window.alert(message);
            syncFiles();
            return;
        }
        selectedFiles.push(...incoming);
        renderNewRows();
    });

    list.addEventListener('click', (event) => {
        const row = event.target.closest('[data-attachment-row]');
        if (!row) return;
        if (event.target.closest('[data-attachment-up]')) {
            const previous = row.previousElementSibling;
            if (previous) list.insertBefore(row, previous);
        } else if (event.target.closest('[data-attachment-down]')) {
            const next = row.nextElementSibling;
            if (next) list.insertBefore(next, row);
        } else if (event.target.closest('[data-attachment-remove-new]')) {
            selectedFiles.splice(Number(row.dataset.fileIndex), 1);
            renderNewRows();
            return;
        } else {
            return;
        }
        syncOrder();
    });
    list.addEventListener('change', (event) => {
        const row = event.target.closest('[data-existing-attachment]');
        if (row && event.target.matches('[data-attachment-remove]')) {
            row.classList.toggle('is-removed', event.target.checked);
        }
    });
    fileInput.form?.addEventListener('submit', syncFiles);
    fileInput.form?.addEventListener('reset', () => window.setTimeout(() => {
        selectedFiles = [];
        list.querySelectorAll('[data-new-attachment]').forEach((row) => row.remove());
        list.querySelectorAll('[data-existing-attachment]').forEach((row) => row.classList.remove('is-removed'));
        syncOrder();
        syncFiles();
    }, 0));
    syncOrder();
};
