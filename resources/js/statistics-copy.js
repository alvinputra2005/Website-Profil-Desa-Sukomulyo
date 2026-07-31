const dataUrlToBlob = async (dataUrl) => {
    const response = await fetch(dataUrl);

    return response.blob();
};

const copyText = async (text) => {
    if (navigator.clipboard?.writeText) {
        await navigator.clipboard.writeText(text);
        return;
    }

    const textarea = document.createElement('textarea');
    textarea.value = text;
    textarea.style.cssText = 'position:fixed;left:-10000px;top:0;';
    document.body.append(textarea);
    textarea.select();
    const copied = document.execCommand('copy');
    textarea.remove();

    if (!copied) throw new Error('Browser tidak mengizinkan penyalinan tabel.');
};

const tableToText = (table) => [...table.rows]
    .map((row) => [...row.cells]
        .map((cell) => cell.innerText.trim().replace(/\s+/g, ' '))
        .join('\t'))
    .join('\n');

const copyChart = async (chart) => {
    if (!chart || chart.isDisposed?.()) {
        throw new Error('Diagram belum tersedia untuk disalin.');
    }
    if (!navigator.clipboard?.write || typeof window.ClipboardItem === 'undefined') {
        throw new Error('Browser ini belum mendukung penyalinan gambar.');
    }

    const blob = await dataUrlToBlob(chart.getDataURL({
        type: 'png',
        pixelRatio: 2,
        backgroundColor: '#ffffff',
    }));
    await navigator.clipboard.write([new window.ClipboardItem({ 'image/png': blob })]);
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
        button.addEventListener('click', async () => {
            const target = button.dataset.statisticsCopy;
            button.disabled = true;

            try {
                if (target in charts) {
                    await copyChart(charts[target]());
                } else {
                    const table = tables[target]?.();
                    if (!table) throw new Error('Tabel belum tersedia untuk disalin.');
                    await copyText(tableToText(table));
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
