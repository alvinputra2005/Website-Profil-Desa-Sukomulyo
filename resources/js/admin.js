import { initAjaxNavigation } from './ajax';
import { initAdminShell } from './admin/core';

const featureLoaders = [
    {
        selector: '[data-dirty-form], [data-submit-status], #title-count',
        load: () => import('./admin/form-state'),
    },
    {
        selector: '[data-image-picker]',
        load: () => import('./admin/image-picker'),
    },
    {
        selector: 'form[data-image-preparation]',
        load: () => import('./admin/image-preparation'),
    },
    {
        selector: '#official-form',
        load: () => import('./admin/official-form'),
    },
    {
        selector: '[data-check-all-officials], [data-statistics-select-all]',
        load: () => import('./admin/bulk-selection'),
    },
    {
        selector: '[data-gallery-manager]',
        load: () => import('./admin/gallery-manager'),
    },
    {
        selector: '[data-publication-attachments]',
        load: () => import('./admin/attachment-manager'),
    },
    {
        selector: '[data-letter-application-detail]',
        load: () => import('./admin/letter-application-detail'),
    },
    {
        selector: '[data-letter-service-form]',
        load: () => import('./admin/letter-service-form'),
    },
    {
        selector: 'textarea[data-tinymce]',
        load: () => import('./admin/rich-editor'),
    },
    {
        selector: '[data-region-selector]',
        load: () => import('./regions').then(({ initRegionSelectors }) => ({
            init: initRegionSelectors,
        })),
    },
];

const loadPageFeatures = (root = document) => {
    initAdminShell(root);

    featureLoaders.forEach(({ selector, load }) => {
        if (!root.querySelector(selector)) return;

        load()
            .then(({ init }) => {
                if (root !== document && !root.isConnected) return;
                init(root);
            })
            .catch((error) => console.error(`Fitur admin gagal dimuat untuk ${selector}.`, error));
    });
};

window.initAdminPage = loadPageFeatures;

initAjaxNavigation({
    rootSelector: '.wrapper',
    onRender: loadPageFeatures,
});

loadPageFeatures();
