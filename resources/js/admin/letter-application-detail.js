export const init = (root = document) => {
    const scope = root.querySelector('[data-letter-application-detail]') || root;
    const modalElement = root.querySelector('#document-viewer') || document.getElementById('document-viewer');

    if (!modalElement || !window.jQuery || modalElement.dataset.initialized === 'true') return;

    modalElement.dataset.initialized = 'true';

    const modal = window.jQuery(modalElement);
    const image = modalElement.querySelector('#document-viewer-image');
    const imageWrap = modalElement.querySelector('#document-viewer-image-wrap');
    const pdf = modalElement.querySelector('#document-viewer-pdf');
    const loading = modalElement.querySelector('#document-viewer-loading');
    const title = modalElement.querySelector('#document-viewer-title');
    const meta = modalElement.querySelector('#document-viewer-meta');
    const download = modalElement.querySelector('#document-viewer-download');
    const imageControls = modalElement.querySelectorAll('.viewer-image-control');
    let viewerUrl = '';
    let isImage = false;
    let zoom = 1;
    let rotation = 0;

    const updateImageTransform = () => {
        image.style.transform = `scale(${zoom}) rotate(${rotation}deg)`;
    };

    const resetViewer = () => {
        viewerUrl = '';
        isImage = false;
        zoom = 1;
        rotation = 0;
        image.removeAttribute('src');
        pdf.src = 'about:blank';
        imageWrap.classList.add('is-hidden');
        pdf.classList.add('is-hidden');
        loading.classList.remove('is-hidden');
        loading.innerHTML = '<i class="fa fa-spinner fa-spin" aria-hidden="true"></i>Menyiapkan pratinjau aman...';
        imageControls.forEach((button) => button.classList.add('is-hidden'));
    };

    const showPreview = (data) => {
        viewerUrl = data.url;
        isImage = Boolean(data.is_image);
        title.textContent = data.name;
        meta.textContent = `${data.mime_type === 'application/pdf' ? 'PDF' : 'Gambar'} · URL sementara berlaku hingga ${new Date(data.expires_at).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })}`;
        loading.classList.add('is-hidden');
        download.href = download.dataset.url;

        if (isImage) {
            image.alt = `Pratinjau ${data.name}`;
            image.src = viewerUrl;
            updateImageTransform();
            imageWrap.classList.remove('is-hidden');
            imageControls.forEach((button) => button.classList.remove('is-hidden'));
        } else {
            pdf.src = viewerUrl;
            pdf.classList.remove('is-hidden');
        }
    };

    scope.querySelectorAll('[data-document-preview]').forEach((button) => {
        button.addEventListener('click', () => {
            resetViewer();
            title.textContent = button.dataset.documentName;
            meta.textContent = button.dataset.documentSize;
            download.dataset.url = button.dataset.downloadUrl;
            download.href = button.dataset.downloadUrl;
            modal.modal('show');

            fetch(button.dataset.previewUrl, { headers: { Accept: 'application/json' }, credentials: 'same-origin' })
                .then((response) => {
                    if (!response.ok) throw new Error('Gagal membuat URL pratinjau.');
                    return response.json();
                })
                .then(showPreview)
                .catch(() => {
                    loading.innerHTML = '<i class="fa fa-exclamation-triangle" aria-hidden="true"></i>Pratinjau tidak dapat dimuat. Silakan unduh dokumen untuk membukanya.';
                });
        });
    });

    modalElement.querySelectorAll('[data-viewer-action]').forEach((button) => {
        button.addEventListener('click', () => {
            const action = button.dataset.viewerAction;
            if (action === 'zoom-in' && isImage) { zoom = Math.min(zoom + .2, 3); updateImageTransform(); }
            if (action === 'zoom-out' && isImage) { zoom = Math.max(zoom - .2, .4); updateImageTransform(); }
            if (action === 'rotate' && isImage) { rotation = (rotation + 90) % 360; updateImageTransform(); }
            if (action === 'open' && viewerUrl) {
                const tab = window.open(viewerUrl, '_blank');
                if (tab) tab.opener = null;
            }
            if (action !== 'print') return;

            if (isImage) {
                const printWindow = window.open('', '_blank');
                if (!printWindow) { window.alert('Izinkan pop-up untuk mencetak gambar.'); return; }
                printWindow.opener = null;
                printWindow.document.write('<!doctype html><html><head><title>Cetak dokumen</title><style>body{margin:0;text-align:center}img{max-width:100%;max-height:100vh}</style></head><body></body></html>');
                const printableImage = printWindow.document.createElement('img');
                printableImage.src = viewerUrl;
                printableImage.alt = title.textContent;
                printableImage.onload = () => { printWindow.focus(); printWindow.print(); };
                printWindow.document.body.appendChild(printableImage);
                return;
            }

            const printWindow = window.open(viewerUrl, '_blank');
            if (printWindow) printWindow.opener = null;
            if (!printWindow) window.alert('Izinkan pop-up untuk mencetak dokumen PDF.');
        });
    });

    scope.querySelectorAll('[data-document-review-form]').forEach((form) => {
        const status = form.querySelector('[data-review-status]');
        const note = form.querySelector('[data-review-note]');
        const requiredMark = form.querySelector('[data-review-required-mark]');
        const help = form.querySelector('[data-review-note-help]');

        const syncReviewRequirement = () => {
            const needsNote = status.value === 'rejected';
            note.required = needsNote;
            note.setAttribute('aria-required', needsNote ? 'true' : 'false');
            requiredMark.hidden = !needsNote;
            help.hidden = !needsNote;
            form.closest('.document-review').classList.toggle('is-rejected', needsNote);
        };

        status.addEventListener('change', syncReviewRequirement);
        syncReviewRequirement();
    });

    modal.on('hidden.bs.modal.letterApplicationDetail', resetViewer);
};
