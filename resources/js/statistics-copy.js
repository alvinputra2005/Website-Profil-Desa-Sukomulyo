const dataUrlToBlob = (dataUrl) => {
    const separator = dataUrl.indexOf(',');
    const metadata = dataUrl.slice(0, separator);
    const encoded = dataUrl.slice(separator + 1);
    const mimeType = metadata.match(/^data:([^;,]+)/)?.[1] || 'image/png';
    const binary = metadata.includes(';base64')
        ? window.atob(encoded)
        : decodeURIComponent(encoded);
    const bytes = new Uint8Array(binary.length);

    for (let index = 0; index < binary.length; index += 1) {
        bytes[index] = binary.charCodeAt(index);
    }

    return new Blob([bytes], { type: mimeType });
};

const copyImageWithSelection = (dataUrl) => {
    const container = document.createElement('div');
    const image = document.createElement('img');
    const selection = window.getSelection();
    const range = document.createRange();

    container.contentEditable = 'true';
    container.setAttribute('aria-hidden', 'true');
    container.style.cssText = 'position:fixed;left:-10000px;top:0;width:1px;height:1px;overflow:hidden;';
    image.src = dataUrl;
    image.alt = 'Diagram statistik';
    container.append(image);
    document.body.append(container);

    range.selectNode(image);
    selection?.removeAllRanges();
    selection?.addRange(range);
    const copied = document.execCommand('copy');
    selection?.removeAllRanges();
    container.remove();

    if (!copied) throw new Error('Browser tidak mengizinkan penyalinan gambar.');
};

const tableToText = (table) => [...table.rows]
    .map((row) => [...row.cells]
        .map((cell) => cell.innerText.trim().replace(/\s+/g, ' '))
        .join('\t'))
    .join('\n');

const INLINE_STYLE_PROPERTIES = [
    'background-color', 'border', 'border-bottom', 'border-collapse', 'border-left',
    'border-right', 'border-spacing', 'border-top', 'color', 'font-family', 'font-size',
    'font-style', 'font-weight', 'line-height', 'padding', 'text-align', 'text-decoration',
    'vertical-align', 'white-space',
];

/**
 * Create a clipboard-safe table clone. The clone is deliberately limited to
 * table markup, so charts and other images can never be copied with it.
 */
const prepareTableClone = (table) => {
    const removedSelector = 'img, canvas, svg, button, input, select, textarea, [data-budget-export-exclude]';
    const clone = table.cloneNode(true);
    clone.querySelectorAll(removedSelector)
        .forEach((element) => element.remove());
    clone.removeAttribute('hidden');

    const sourceElements = [table, ...table.querySelectorAll('*')]
        .filter((element) => !element.matches(removedSelector) && !element.closest(removedSelector));
    const cloneElements = [clone, ...clone.querySelectorAll('*')];
    cloneElements.forEach((element, index) => {
        const source = sourceElements[index];
        if (!source || !(source instanceof Element)) return;

        const computed = window.getComputedStyle(source);
        const inlineStyle = INLINE_STYLE_PROPERTIES
            .map((property) => `${property}:${computed.getPropertyValue(property)};`)
            .join('');
        const compactTableStyle = source === table
            ? 'width:auto;min-width:0;max-width:none;table-layout:auto;'
            : '';
        const copiedCellStyle = source.matches('th,td')
            ? 'border:1px solid #cfd8ca;'
            : '';
        const headerStyle = source.matches('thead th')
            ? 'color:#fff;background-color:#526b42;'
            : '';
        element.setAttribute(
            'style',
            `${element.getAttribute('style') || ''}${inlineStyle}${compactTableStyle}${copiedCellStyle}${headerStyle}`,
        );
    });

    return clone;
};

const copyTableWithSelection = (table) => {
    const container = document.createElement('div');
    const selection = window.getSelection();
    const range = document.createRange();

    container.contentEditable = 'true';
    container.setAttribute('aria-hidden', 'true');
    container.style.cssText = 'position:fixed;left:-10000px;top:0;width:1px;height:1px;overflow:hidden;';
    container.append(prepareTableClone(table));
    document.body.append(container);

    range.selectNodeContents(container);
    selection?.removeAllRanges();
    selection?.addRange(range);
    const copied = document.execCommand('copy');
    selection?.removeAllRanges();
    container.remove();

    if (!copied) throw new Error('Browser tidak mengizinkan penyalinan tabel.');
};

const copyTable = async (table) => {
    const clone = prepareTableClone(table);
    const html = clone.outerHTML;
    const text = tableToText(clone);

    if (navigator.clipboard?.write && typeof window.ClipboardItem !== 'undefined') {
        try {
            await navigator.clipboard.write([new window.ClipboardItem({
                'text/html': new Blob([html], { type: 'text/html' }),
                'text/plain': new Blob([text], { type: 'text/plain' }),
            })]);
            return;
        } catch {
            // Gunakan fallback seleksi DOM untuk browser yang membatasi Clipboard API.
        }
    }

    copyTableWithSelection(table);
};

const copyChart = async (chart) => {
    if (!chart || chart.isDisposed?.()) {
        throw new Error('Diagram belum tersedia untuk disalin.');
    }

    const dataUrl = chart.getDataURL({
        type: 'png',
        pixelRatio: 2,
        backgroundColor: '#ffffff',
    });
    const blob = dataUrlToBlob(dataUrl);

    if (navigator.clipboard?.write && typeof window.ClipboardItem !== 'undefined') {
        const html = `<img src="${dataUrl}" alt="Diagram statistik">`;

        try {
            await navigator.clipboard.write([new window.ClipboardItem({
                'image/png': blob,
                'text/html': new Blob([html], { type: 'text/html' }),
            })]);
            return;
        } catch {
            try {
                await navigator.clipboard.write([new window.ClipboardItem({ 'image/png': blob })]);
                return;
            } catch {
                // Gunakan fallback seleksi DOM untuk browser yang membatasi Clipboard API.
            }
        }
    }

    copyImageWithSelection(dataUrl);
};

const setButtonResult = (button, success) => {
    const icon = button.querySelector('i');
    const originalTitle = button.dataset.originalTitle || button.title;
    button.dataset.originalTitle = originalTitle;
    button.title = success ? 'Berhasil disalin' : 'Gagal menyalin';
    button.classList.toggle('is-copied', success);
    button.classList.toggle('is-error', !success);
    if (icon) icon.className = success ? 'fas fa-check' : 'fas fa-exclamation';

    window.setTimeout(() => {
        button.title = originalTitle;
        button.classList.remove('is-copied', 'is-error');
        if (icon) icon.className = 'far fa-copy';
    }, 1800);
};

export const bindStatisticsCopyButtons = ({
    root,
    charts = {},
    tables = {},
    signal,
}) => {
    const status = root.querySelector('[data-statistics-copy-status]');

    root.querySelectorAll('[data-statistics-copy]').forEach((button) => {
        if (button.dataset.statisticsCopyBound === 'true') return;
        button.dataset.statisticsCopyBound = 'true';
        button.addEventListener('click', async () => {
            const target = button.dataset.statisticsCopy;
            button.disabled = true;

            try {
                if (target in charts) {
                    const chartTarget = charts[target];
                    const chart = typeof chartTarget === 'function'
                        ? chartTarget()
                        : chartTarget;
                    await copyChart(chart);
                } else {
                    const table = tables[target]?.();
                    if (!table) throw new Error('Tabel belum tersedia untuk disalin.');
                    await copyTable(table);
                }
                setButtonResult(button, true);
                if (status) status.textContent = 'Data berhasil disalin ke clipboard.';
            } catch (error) {
                setButtonResult(button, false);
                if (status) status.textContent = error?.message || 'Data gagal disalin.';
            } finally {
                button.disabled = false;
            }
        }, { signal });
    });
};
