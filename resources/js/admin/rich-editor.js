import tinymce from 'tinymce/tinymce';
import 'tinymce/icons/default';
import 'tinymce/themes/silver';
import 'tinymce/models/dom';
import 'tinymce/skins/ui/oxide/skin.js';
import 'tinymce/skins/ui/oxide/content.js';
import 'tinymce/skins/content/default/content.js';
import 'tinymce/plugins/advlist';
import 'tinymce/plugins/autolink';
import 'tinymce/plugins/anchor';
import 'tinymce/plugins/charmap';
import 'tinymce/plugins/code';
import 'tinymce/plugins/fullscreen';
import 'tinymce/plugins/image';
import 'tinymce/plugins/link';
import 'tinymce/plugins/lists';
import 'tinymce/plugins/media';
import 'tinymce/plugins/preview';
import 'tinymce/plugins/searchreplace';
import 'tinymce/plugins/table';
import 'tinymce/plugins/visualblocks';
import 'tinymce/plugins/wordcount';

const uploadImage = (uploadUrl, csrfToken) => (blobInfo) => new Promise((resolve, reject) => {
    if (!uploadUrl) {
        reject('URL upload gambar tidak tersedia.');
        return;
    }

    const data = new FormData();
    data.append('image', blobInfo.blob(), blobInfo.filename());
    const slug = document.querySelector('#slug')?.value.trim();
    const title = document.querySelector('#title')?.value.trim();
    data.append('folder_name', slug || title || 'berita-baru');

    fetch(uploadUrl, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken, Accept: 'application/json' },
        body: data,
    })
        .then((response) => response.json().then((result) => ({ response, result })))
        .then(({ response, result }) => {
            if (!response.ok) {
                const validationMessage = Object.values(result.errors || {}).flat()[0];
                throw new Error(result.message || validationMessage || 'Gambar gagal diunggah.');
            }
            resolve(result.location || result.url);
        })
        .catch((error) => reject(error.message || 'Gambar gagal diunggah.'));
});

export const init = async (root = document) => {
    const fields = [...root.querySelectorAll('textarea[data-tinymce]')];
    if (!fields.length) return;

    tinymce.remove();
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const uploadUrl = root.querySelector('[data-tinymce-upload-url]')?.dataset.tinymceUploadUrl;

    await tinymce.init({
        selector: 'textarea[data-tinymce]',
        license_key: 'gpl',
        menubar: false,
        height: 520,
        placeholder: fields[0].dataset.placeholder || 'Mulai tulis konten di sini...',
        branding: false,
        promotion: false,
        statusbar: false,
        plugins: 'advlist autolink anchor charmap code fullscreen image link lists media preview searchreplace table visualblocks wordcount',
        toolbar: 'undo redo | blocks | bold italic underline strikethrough | alignleft aligncenter alignright | bullist numlist blockquote | link image media table | removeformat code fullscreen preview',
        toolbar_mode: 'sliding',
        image_caption: true,
        image_advtab: true,
        image_class_list: [
            { title: 'Gambar artikel', value: 'article-image align-center' },
            { title: 'Rata kiri', value: 'article-image align-left' },
            { title: 'Rata kanan', value: 'article-image align-right' },
            { title: 'Lebar penuh', value: 'article-image align-full' },
        ],
        images_file_types: 'jpg,jpeg,png,webp',
        automatic_uploads: true,
        relative_urls: false,
        remove_script_host: false,
        content_style: `
            body { font-family: "Source Sans Pro", "Helvetica Neue", Helvetica, Arial, sans-serif; font-size: 15px; line-height: 1.65; }
            body.mce-content-body[data-mce-placeholder]::before { font-family: "Source Sans Pro", "Helvetica Neue", Helvetica, Arial, sans-serif; }
            figure.image, figure.article-image { width: 70%; margin: 18px auto; }
            figure.image.align-left, figure.article-image.align-left { float: left; width: 45%; margin: 8px 18px 12px 0; }
            figure.image.align-right, figure.article-image.align-right { float: right; width: 45%; margin: 8px 0 12px 18px; }
            figure.image.align-full, figure.article-image.align-full { width: 100%; }
            figure figcaption { padding: 6px; color: #6f786f; font-size: 12px; text-align: center; cursor: text; }
            figure figcaption:empty::before { content: "Klik untuk menulis keterangan gambar"; color: #9ba39c; }
        `,
        images_upload_handler: uploadImage(uploadUrl, csrfToken),
        setup: (editor) => {
            editor.on('init change input undo redo keyup', () => editor.save());
            editor.on('BeforeSetContent', (event) => {
                event.content = event.content.replaceAll(
                    '<figure class="image">',
                    '<figure class="article-image align-center">',
                );
            });
            editor.on('ObjectSelected', (event) => {
                if (event.target?.nodeName !== 'IMG') return;
                const figure = event.target.closest('figure');
                if (figure && !figure.classList.contains('article-image')) {
                    figure.classList.add('article-image', 'align-center');
                }
            });
        },
    });

    fields.forEach((field) => {
        const form = field.form;
        if (!form || form.dataset.tinyMceResetBound) return;
        form.dataset.tinyMceResetBound = 'true';
        form.addEventListener('reset', () => window.setTimeout(() => {
            tinymce.get().forEach((editor) => {
                if (editor.targetElm?.form === form) editor.setContent(editor.targetElm.value || '');
            });
        }, 0));
    });
};
