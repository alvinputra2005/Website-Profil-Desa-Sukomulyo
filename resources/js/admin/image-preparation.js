import { bindImagePreparation } from '../image-upload';

export const init = (root = document) => {
    root.querySelectorAll('form[data-image-preparation]').forEach(bindImagePreparation);
};
