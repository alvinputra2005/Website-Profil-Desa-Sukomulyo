const MAX_IMAGE_DIMENSION = 1920;
const MAX_CLIENT_BYTES = 2.5 * 1024 * 1024;
const WEBP_QUALITY = 0.92;

const isProcessableImage = (file) => (
  file
  && ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'].includes(file.type)
);

const loadBitmap = async (file) => {
  if (window.createImageBitmap) {
    try {
      return await window.createImageBitmap(file, { imageOrientation: 'from-image' });
    } catch {
      // Fall back to an HTMLImageElement for older browser implementations.
    }
  }

  const objectUrl = URL.createObjectURL(file);

  try {
    const image = await new Promise((resolve, reject) => {
      const element = new Image();
      element.onload = () => resolve(element);
      element.onerror = () => reject(new Error('Gambar tidak dapat dibaca oleh browser.'));
      element.src = objectUrl;
    });

    return image;
  } finally {
    URL.revokeObjectURL(objectUrl);
  }
};

const canvasBlob = (canvas) => new Promise((resolve) => {
  canvas.toBlob(resolve, 'image/webp', WEBP_QUALITY);
});

const outputName = (name) => `${name.replace(/\.[^/.]+$/, '')}.webp`;

/**
 * Reduce large camera/photos before the request reaches PHP/GD.
 *
 * Small images are returned untouched. This preserves their original
 * quality and avoids spending CPU in the browser for already-optimized files.
 */
export const prepareImageFile = async (file) => {
  if (!isProcessableImage(file)) return file;

  const bitmap = await loadBitmap(file);
  const width = bitmap.width;
  const height = bitmap.height;
  const scale = Math.min(1, MAX_IMAGE_DIMENSION / Math.max(width, height));
  const needsResize = scale < 1 || file.size > MAX_CLIENT_BYTES;

  if (!needsResize) {
    bitmap.close?.();
    return file;
  }

  const canvas = document.createElement('canvas');
  canvas.width = Math.max(1, Math.round(width * scale));
  canvas.height = Math.max(1, Math.round(height * scale));
  const context = canvas.getContext('2d');
  if (!context) {
    bitmap.close?.();
    return file;
  }

  context.drawImage(bitmap, 0, 0, canvas.width, canvas.height);
  bitmap.close?.();

  const blob = await canvasBlob(canvas);

  if (!blob || (blob.size >= file.size && scale === 1)) {
    return file;
  }

  return new File([blob], outputName(file.name), {
    type: 'image/webp',
    lastModified: file.lastModified,
  });
};

const replaceInputFile = (input, file) => {
  if (!window.DataTransfer) return;

  const transfer = new DataTransfer();
  transfer.items.add(file);
  input.files = transfer.files;
};

/**
 * Prepare one file input and keep the Promise on the element so a form
 * submitted immediately after file selection waits for the conversion.
 */
export const prepareImageInput = (input) => {
  const file = input.files?.[0];

  if (!file) {
    input.__imagePreparation = null;
    return Promise.resolve(null);
  }

  if (input.__preparedImageSource === file && input.__imagePreparation) {
    return input.__imagePreparation;
  }

  input.__preparedImageSource = file;
  input.dataset.imagePrepared = 'false';
  input.__imagePreparation = prepareImageFile(file).then((prepared) => {
    if (input.files?.[0] === file && prepared !== file) {
      replaceInputFile(input, prepared);
      input.__preparedImageSource = prepared;
    }

    input.dataset.imagePrepared = 'true';
    return prepared;
  }).catch(() => {
    // Server-side validation and processing remain the safety net when a
    // browser cannot decode or resize the image.
    input.dataset.imagePrepared = 'true';
    return file;
  });

  return input.__imagePreparation;
};

export const bindImagePreparation = (form) => {
  if (!form || form.dataset.imagePreparationBound) return;

  form.dataset.imagePreparationBound = 'true';
  form.addEventListener('submit', (event) => {
    if (form.dataset.imageSubmitting === 'true') return;

    const inputs = [...form.querySelectorAll('input[type="file"]')]
      .filter((input) => input.files?.length && input.dataset.imagePrepared !== 'true');

    if (!inputs.length) return;

    const preparations = inputs.map((input) => prepareImageInput(input));
    if (preparations.every((preparation) => preparation === null)) return;

    event.preventDefault();
    Promise.all(preparations).then(() => {
      form.dataset.imageSubmitting = 'true';

      if (form.requestSubmit) {
        form.requestSubmit(event.submitter);
      } else {
        form.submit();
      }
    });
  });
};
