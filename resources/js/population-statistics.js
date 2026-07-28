import * as echarts from 'echarts/core';
import { LineChart, PieChart } from 'echarts/charts';
import {
    DataZoomComponent,
    DatasetComponent,
    GridComponent,
    LegendComponent,
    TitleComponent,
    TooltipComponent,
} from 'echarts/components';
import { CanvasRenderer, SVGRenderer } from 'echarts/renderers';

echarts.use([
    PieChart,
    LineChart,
    DatasetComponent,
    GridComponent,
    LegendComponent,
    TitleComponent,
    TooltipComponent,
    DataZoomComponent,
    CanvasRenderer,
    SVGRenderer,
]);

const COLORS = {
    total: '#2f6b45',
    male: '#2f6fb5',
    female: '#c94f70',
    grid: '#dce5dc',
    text: '#26352a',
    muted: '#69766d',
};

const integerFormatter = new Intl.NumberFormat('id-ID');
const percentageFormatter = new Intl.NumberFormat('id-ID', {
    minimumFractionDigits: 0,
    maximumFractionDigits: 2,
});

const formatInteger = (value) => integerFormatter.format(Number(value) || 0);
const formatPercentage = (value) => `${percentageFormatter.format(Number(value) || 0)}%`;
const signedInteger = (value) => {
    if (value === null || value === undefined) return '—';

    return `${value >= 0 ? '+' : ''}${formatInteger(value)} jiwa`;
};
const signedPercentage = (value) => {
    if (value === null || value === undefined) return '—';

    return `${value >= 0 ? '+' : ''}${formatPercentage(value)}`;
};

const parsePayload = (root) => {
    const element = root.querySelector('#population-statistics-data');
    if (!element) return null;

    try {
        return JSON.parse(element.textContent);
    } catch {
        return null;
    }
};

const withCalculatedGrowth = (rows) => {
    let previousTotal = null;

    return rows.map((row) => {
        const change = previousTotal === null ? null : row.total - previousTotal;
        const growth = previousTotal === null || previousTotal === 0
            ? null
            : Math.round((change / previousTotal) * 10000) / 100;
        previousTotal = row.total;

        return { ...row, change, growth_percentage: growth };
    });
};

const createCell = (tag, value) => {
    const cell = document.createElement(tag);
    cell.textContent = value;

    return cell;
};

const escapeXml = (value) => String(value)
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&apos;');

const cleanCellText = (cell) => cell.textContent.trim().replace(/\s+/g, ' ');

const wrapText = (value, maximumCharacters, maximumLines = 2) => {
    const words = String(value).split(/\s+/);
    const lines = [];
    let current = '';

    words.forEach((word) => {
        const candidate = current ? `${current} ${word}` : word;
        if (candidate.length <= maximumCharacters || !current) {
            current = candidate;
            return;
        }

        lines.push(current);
        current = word;
    });
    if (current) lines.push(current);

    if (lines.length > maximumLines) {
        const visible = lines.slice(0, maximumLines);
        visible[maximumLines - 1] = `${visible[maximumLines - 1].slice(0, Math.max(1, maximumCharacters - 1))}…`;
        return visible;
    }

    return lines;
};

const svgText = (value, x, centerY, width, options = {}) => {
    const {
        color = COLORS.text,
        weight = 500,
        align = 'center',
        fontSize = 14,
    } = options;
    const anchor = align === 'left' ? 'start' : 'middle';
    const textX = align === 'left' ? x + 14 : x + (width / 2);
    const maximumCharacters = Math.max(8, Math.floor((width - 24) / (fontSize * .58)));
    const lines = wrapText(value, maximumCharacters);
    const firstDy = lines.length === 1 ? 0 : -((lines.length - 1) * (fontSize + 4)) / 2;

    return `<text x="${textX}" y="${centerY}" fill="${color}" font-family="Arial, sans-serif" font-size="${fontSize}" font-weight="${weight}" text-anchor="${anchor}" dominant-baseline="middle">`
        + lines.map((line, index) => `<tspan x="${textX}" dy="${index === 0 ? firstDy : fontSize + 4}">${escapeXml(line)}</tspan>`).join('')
        + '</text>';
};

const tableToSvg = (table, title, subtitle) => {
    const headers = [...table.querySelectorAll('thead th')].map(cleanCellText);
    const rows = [...table.querySelectorAll('tbody tr')].map((row) => (
        [...row.querySelectorAll('th, td')].map(cleanCellText)
    )).filter((row) => row.length === headers.length);

    const columnWidths = headers.map((header, index) => {
        const longest = Math.max(
            header.length,
            ...rows.map((row) => row[index]?.length || 0),
        );
        const isLastColumn = index === headers.length - 1;
        return Math.min(isLastColumn ? 360 : 230, Math.max(125, (longest * 8) + 34));
    });
    const width = columnWidths.reduce((sum, columnWidth) => sum + columnWidth, 0);
    const titleHeight = 92;
    const headerHeight = 62;
    const rowHeight = 62;
    const height = titleHeight + headerHeight + (Math.max(rows.length, 1) * rowHeight) + 24;
    let svg = `<svg xmlns="http://www.w3.org/2000/svg" width="${width}" height="${height}" viewBox="0 0 ${width} ${height}">`;
    svg += `<rect width="${width}" height="${height}" fill="#ffffff"/>`;
    svg += `<text x="18" y="32" fill="${COLORS.text}" font-family="Arial, sans-serif" font-size="24" font-weight="700">${escapeXml(title)}</text>`;
    svg += `<text x="18" y="59" fill="${COLORS.muted}" font-family="Arial, sans-serif" font-size="13">${escapeXml(subtitle)}</text>`;

    let x = 0;
    headers.forEach((header, index) => {
        const columnWidth = columnWidths[index];
        svg += `<rect x="${x}" y="${titleHeight}" width="${columnWidth}" height="${headerHeight}" fill="${COLORS.total}"/>`;
        svg += svgText(header, x, titleHeight + (headerHeight / 2), columnWidth, { color: '#ffffff', weight: 700, fontSize: 13 });
        x += columnWidth;
    });

    if (rows.length === 0) {
        svg += svgText('Belum ada data yang tersedia.', 0, titleHeight + headerHeight + (rowHeight / 2), width);
    }

    rows.forEach((row, rowIndex) => {
        const y = titleHeight + headerHeight + (rowIndex * rowHeight);
        svg += `<rect x="0" y="${y}" width="${width}" height="${rowHeight}" fill="${rowIndex % 2 === 0 ? '#ffffff' : '#f4f7f2'}"/>`;
        x = 0;
        row.forEach((value, index) => {
            const columnWidth = columnWidths[index];
            svg += svgText(value, x, y + (rowHeight / 2), columnWidth, {
                align: index === 0 || index === row.length - 1 ? 'left' : 'center',
                weight: index === 0 ? 700 : 500,
                fontSize: 13,
            });
            x += columnWidth;
        });
    });

    svg += '</svg>';

    return { svg, width, height };
};

export const downloadBlob = (blob, filename) => {
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = filename;
    link.click();
    window.setTimeout(() => URL.revokeObjectURL(url), 0);
};

export const downloadDataUrl = (dataUrl, filename) => {
    const link = document.createElement('a');
    link.href = dataUrl;
    link.download = filename;
    link.click();
};

export const svgToRaster = (svg, width, height, format = 'png') => new Promise((resolve, reject) => {
    const blob = new Blob([svg], { type: 'image/svg+xml;charset=utf-8' });
    const url = URL.createObjectURL(blob);
    const image = new Image();

    image.addEventListener('load', () => {
        const scale = 2;
        const canvas = document.createElement('canvas');
        canvas.width = Math.ceil(width * scale);
        canvas.height = Math.ceil(height * scale);
        const context = canvas.getContext('2d');
        context.fillStyle = '#ffffff';
        context.fillRect(0, 0, canvas.width, canvas.height);
        context.drawImage(image, 0, 0, canvas.width, canvas.height);
        URL.revokeObjectURL(url);
        resolve(canvas.toDataURL(format === 'jpg' ? 'image/jpeg' : 'image/png', .94));
    }, { once: true });
    image.addEventListener('error', () => {
        URL.revokeObjectURL(url);
        reject(new Error('SVG tidak dapat dikonversi menjadi gambar.'));
    }, { once: true });
    image.src = url;
});

const svgDataUrlToText = (dataUrl) => {
    const separator = dataUrl.indexOf(',');
    const metadata = dataUrl.slice(0, separator);
    const content = dataUrl.slice(separator + 1);

    return metadata.includes(';base64')
        ? window.atob(content)
        : decodeURIComponent(content);
};

const svgBody = (svg) => svg.slice(svg.indexOf('>') + 1, svg.lastIndexOf('</svg>'));

const chartToSvgAsset = (chart, container) => {
    const width = Math.max(720, Math.round(container.clientWidth || 0));
    const height = Math.max(380, Math.round(container.clientHeight || 0));
    const exportContainer = document.createElement('div');
    exportContainer.style.cssText = `position:fixed;left:-10000px;top:0;width:${width}px;height:${height}px;`;
    document.body.append(exportContainer);
    const exportChart = echarts.init(exportContainer, null, { renderer: 'svg', width, height });

    try {
        exportChart.setOption(chart.getOption(), true);
        return {
            svg: svgDataUrlToText(exportChart.getDataURL({ type: 'svg', backgroundColor: '#ffffff' })),
            width,
            height,
        };
    } finally {
        exportChart.dispose();
        exportContainer.remove();
    }
};

export const combineChartAndTable = (chart, container, table, title, subtitle) => {
    const chartAsset = chartToSvgAsset(chart, container);
    const tableAsset = tableToSvg(table, title, subtitle);
    const gap = 24;
    const width = Math.max(chartAsset.width, tableAsset.width);
    const height = chartAsset.height + gap + tableAsset.height;
    const chartX = (width - chartAsset.width) / 2;
    const tableX = (width - tableAsset.width) / 2;
    const svg = [
        `<svg xmlns="http://www.w3.org/2000/svg" width="${width}" height="${height}" viewBox="0 0 ${width} ${height}">`,
        `<rect width="${width}" height="${height}" fill="#ffffff"/>`,
        `<svg x="${chartX}" y="0" width="${chartAsset.width}" height="${chartAsset.height}" viewBox="0 0 ${chartAsset.width} ${chartAsset.height}">`,
        svgBody(chartAsset.svg),
        '</svg>',
        `<svg x="${tableX}" y="${chartAsset.height + gap}" width="${tableAsset.width}" height="${tableAsset.height}" viewBox="0 0 ${tableAsset.width} ${tableAsset.height}">`,
        svgBody(tableAsset.svg),
        '</svg>',
        '</svg>',
    ].join('');

    return { svg, width, height };
};

export const savePdf = async (dataUrl, sourceWidth, sourceHeight, filename) => {
    const { jsPDF } = await import('jspdf');
    const landscape = sourceWidth >= sourceHeight;
    const pdf = new jsPDF({
        orientation: landscape ? 'landscape' : 'portrait',
        unit: 'mm',
        format: 'a4',
        compress: true,
    });
    const pageWidth = pdf.internal.pageSize.getWidth();
    const pageHeight = pdf.internal.pageSize.getHeight();
    const margin = 10;
    const ratio = Math.min(
        (pageWidth - (margin * 2)) / sourceWidth,
        (pageHeight - (margin * 2)) / sourceHeight,
    );
    const width = sourceWidth * ratio;
    const height = sourceHeight * ratio;
    pdf.addImage(
        dataUrl,
        'PNG',
        (pageWidth - width) / 2,
        (pageHeight - height) / 2,
        width,
        height,
        undefined,
        'FAST',
    );
    pdf.save(filename);
};

export const initPopulationStatistics = () => {
    const root = document.querySelector('[data-population-statistics]');
    if (!root || root.dataset.bound === 'true') return;

    root.dataset.bound = 'true';
    const payload = parsePayload(root);
    const status = root.querySelector('[data-population-filter-status]');

    if (!payload || !payload.summary || !Array.isArray(payload.trend)) {
        if (status) status.textContent = 'Data statistik tidak dapat dibaca. Muat ulang halaman untuk mencoba kembali.';
        root.querySelector('[data-population-pie-empty]')?.removeAttribute('hidden');
        root.querySelector('[data-population-line-empty]')?.removeAttribute('hidden');
        return;
    }

    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const controller = new AbortController();
    const { signal } = controller;
    const summary = payload.summary;
    const allRows = [...payload.trend]
        .map((row) => ({
            ...row,
            year: Number(row.year),
            male: Number(row.male) || 0,
            female: Number(row.female) || 0,
            total: Number(row.total) || 0,
        }))
        .sort((left, right) => left.year - right.year);
    const availableYears = [...new Set(
        (payload.availableYears || allRows.map((row) => row.year)).map(Number),
    )].sort((left, right) => left - right);
    const fromSelect = root.querySelector('[data-population-from-year]');
    const toSelect = root.querySelector('[data-population-to-year]');
    const sortSelect = root.querySelector('[data-population-sort]');
    const historyBody = root.querySelector('[data-population-history-body]');
    const pieContainer = root.querySelector('[data-population-pie]');
    const lineContainer = root.querySelector('[data-population-line]');
    const pieEmpty = root.querySelector('[data-population-pie-empty]');
    const lineEmpty = root.querySelector('[data-population-line-empty]');
    let pieChart = null;
    let lineChart = null;

    const showStatus = (message, isError = false) => {
        if (!status) return;

        status.textContent = message;
        status.classList.toggle('is-error', isError);
    };

    if (pieContainer && Number(summary.total) > 0) {
        pieEmpty?.setAttribute('hidden', '');
        pieChart = echarts.init(pieContainer, null, { renderer: 'canvas' });
        pieChart.setOption({
            animation: !reducedMotion,
            color: [COLORS.male, COLORS.female],
            tooltip: {
                trigger: 'item',
                formatter: (item) => [
                    `<strong>${item.name}</strong>`,
                    `${formatInteger(item.value)} jiwa`,
                    formatPercentage(item.percent),
                    `Tahun ${summary.year}`,
                ].join('<br>'),
            },
            legend: {
                bottom: 0,
                selectedMode: true,
                textStyle: { color: COLORS.text },
            },
            series: [{
                name: 'Komposisi Penduduk',
                type: 'pie',
                radius: '74%',
                center: ['50%', '44%'],
                stillShowZeroSum: false,
                avoidLabelOverlap: true,
                itemStyle: {
                    borderColor: '#ffffff',
                    borderWidth: 3,
                    borderRadius: 7,
                },
                label: {
                    formatter: ({ name, percent }) => `${name}\n${formatPercentage(percent)}`,
                    color: COLORS.text,
                    fontWeight: 600,
                },
                emphasis: {
                    scale: true,
                    scaleSize: 8,
                    itemStyle: { shadowBlur: 14, shadowColor: 'rgba(38, 53, 42, .2)' },
                },
                data: [
                    { name: 'Laki-laki', value: Number(summary.male) || 0 },
                    { name: 'Perempuan', value: Number(summary.female) || 0 },
                ],
            }],
        });
        pieContainer.addEventListener('focus', () => {
            pieChart?.dispatchAction({ type: 'highlight', seriesIndex: 0, dataIndex: 0 });
            pieChart?.dispatchAction({ type: 'showTip', seriesIndex: 0, dataIndex: 0 });
        }, { signal });
        pieContainer.addEventListener('blur', () => {
            pieChart?.dispatchAction({ type: 'downplay', seriesIndex: 0 });
            pieChart?.dispatchAction({ type: 'hideTip' });
        }, { signal });
    } else {
        pieContainer?.setAttribute('hidden', '');
        pieEmpty?.removeAttribute('hidden');
    }

    const renderHistoryTable = (rows, sort) => {
        if (!historyBody) return;

        historyBody.replaceChildren();
        const tableRows = sort === 'desc' ? [...rows].reverse() : rows;

        if (tableRows.length === 0) {
            const row = document.createElement('tr');
            const cell = createCell('td', 'Belum ada data tahunan yang dipublikasikan pada rentang ini.');
            cell.colSpan = 7;
            row.append(cell);
            historyBody.append(row);
            return;
        }

        tableRows.forEach((item) => {
            const row = document.createElement('tr');
            const year = createCell('th', String(item.year));
            year.scope = 'row';
            const total = createCell('td', `${formatInteger(item.total)} jiwa`);
            const totalStrong = document.createElement('strong');
            totalStrong.textContent = total.textContent;
            total.replaceChildren(totalStrong);
            const source = createCell('td', item.source || 'Sumber belum dicantumkan');

            if (item.reference_date) {
                const reference = document.createElement('small');
                const date = new Date(`${item.reference_date}T00:00:00`);
                reference.textContent = `per ${new Intl.DateTimeFormat('id-ID').format(date)}`;
                source.append(document.createElement('br'), reference);
            }

            row.append(
                year,
                createCell('td', `${formatInteger(item.male)} jiwa`),
                createCell('td', `${formatInteger(item.female)} jiwa`),
                total,
                createCell('td', signedInteger(item.change)),
                createCell('td', signedPercentage(item.growth_percentage)),
                source,
            );
            historyBody.append(row);
        });
    };

    const renderLineChart = (rows, fromYear, toYear) => {
        if (!lineContainer) return;

        if (!lineChart) lineChart = echarts.init(lineContainer, null, { renderer: 'canvas' });

        const years = [];
        for (let year = fromYear; year <= toYear; year += 1) years.push(year);

        const byYear = new Map(rows.map((row) => [row.year, row]));
        const valueFor = (key) => years.map((year) => byYear.get(year)?.[key] ?? null);
        const hasData = rows.length > 0;
        lineContainer.toggleAttribute('hidden', !hasData);
        lineEmpty?.toggleAttribute('hidden', hasData);
        lineContainer.setAttribute(
            'aria-label',
            hasData
                ? `Grafik pertumbuhan penduduk Desa Sukomulyo periode ${fromYear} sampai ${toYear}, menampilkan total penduduk, laki-laki, dan perempuan.`
                : `Data pertumbuhan penduduk Desa Sukomulyo periode ${fromYear} sampai ${toYear} belum tersedia.`,
        );

        if (!hasData) {
            lineChart.clear();
            return;
        }

        lineChart.setOption({
            animation: !reducedMotion,
            color: [COLORS.total, COLORS.male, COLORS.female],
            tooltip: {
                trigger: 'axis',
                axisPointer: { type: 'cross' },
                formatter: (items) => {
                    const year = Number(items[0]?.axisValue);
                    const row = rows.find((item) => item.year === year);
                    if (!row) return `<strong>${year}</strong><br>Data belum tersedia`;

                    return [
                        `<strong>${year}</strong>`,
                        `Total Penduduk: ${formatInteger(row.total)} jiwa`,
                        `Laki-laki: ${formatInteger(row.male)} jiwa`,
                        `Perempuan: ${formatInteger(row.female)} jiwa`,
                        `Perubahan: ${signedInteger(row.change)}`,
                        `Pertumbuhan: ${signedPercentage(row.growth_percentage)}`,
                    ].join('<br>');
                },
            },
            legend: {
                bottom: 0,
                textStyle: { color: COLORS.text },
            },
            grid: {
                left: 0,
                right: 0,
                top: 72,
                bottom: years.length > 8 ? 106 : 52,
                containLabel: true,
            },
            xAxis: {
                type: 'category',
                boundaryGap: false,
                data: years,
                axisLine: { lineStyle: { color: COLORS.grid } },
                axisLabel: { color: COLORS.muted },
            },
            yAxis: {
                type: 'value',
                min: 0,
                name: 'Jiwa',
                nameTextStyle: { color: COLORS.muted },
                axisLabel: {
                    color: COLORS.muted,
                    formatter: (value) => formatInteger(value),
                },
                splitLine: { lineStyle: { color: COLORS.grid, type: 'dashed' } },
            },
            dataZoom: years.length > 8
                ? [
                    { type: 'inside', start: 0, end: 100 },
                    { type: 'slider', height: 22, bottom: 32, start: 0, end: 100 },
                ]
                : [],
            series: [
                {
                    name: 'Total Penduduk',
                    type: 'line',
                    data: valueFor('total'),
                    connectNulls: false,
                    symbolSize: 8,
                    lineStyle: { width: 4 },
                    itemStyle: { color: COLORS.total },
                },
                {
                    name: 'Laki-laki',
                    type: 'line',
                    data: valueFor('male'),
                    connectNulls: false,
                    symbolSize: 7,
                    lineStyle: { width: 2.5 },
                    itemStyle: { color: COLORS.male },
                },
                {
                    name: 'Perempuan',
                    type: 'line',
                    data: valueFor('female'),
                    connectNulls: false,
                    symbolSize: 7,
                    lineStyle: { width: 2.5 },
                    itemStyle: { color: COLORS.female },
                },
            ],
        }, true);
    };

    const closestAvailableYear = (target, direction = 'after') => {
        if (direction === 'before') {
            return [...availableYears].reverse().find((year) => year <= target) ?? availableYears.at(-1);
        }

        return availableYears.find((year) => year >= target) ?? availableYears[0];
    };

    const applyRange = (fromYear, toYear, sort = sortSelect?.value || 'asc', announce = true) => {
        const from = Number(fromYear);
        const to = Number(toYear);

        if (!Number.isInteger(from) || !Number.isInteger(to) || from > to) {
            showStatus('Tahun awal tidak boleh lebih besar dari tahun akhir.', true);
            return false;
        }

        const rows = withCalculatedGrowth(allRows.filter((row) => row.year >= from && row.year <= to));
        renderLineChart(rows, from, to);
        renderHistoryTable(rows, sort);
        if (fromSelect && availableYears.includes(from)) fromSelect.value = String(from);
        if (toSelect && availableYears.includes(to)) toSelect.value = String(to);
        if (sortSelect) sortSelect.value = sort;

        if (announce) {
            showStatus(
                rows.length
                    ? `Menampilkan ${rows.length} data tahunan untuk periode ${from}–${to}.`
                    : `Belum ada data yang dipublikasikan untuk periode ${from}–${to}.`,
            );
        }

        return true;
    };

    const lastAvailableYear = availableYears.at(-1) ?? Number(summary.year);
    const firstAvailableYear = availableYears[0] ?? Number(summary.year);
    const initialFrom = Number(fromSelect?.value || payload.defaultRange?.from || firstAvailableYear);
    const initialTo = Number(toSelect?.value || payload.defaultRange?.to || lastAvailableYear);
    applyRange(initialFrom, initialTo, payload.sort || 'asc', false);

    root.querySelector('[data-population-year-filter]')?.addEventListener('submit', (event) => {
        event.preventDefault();
        applyRange(fromSelect?.value, toSelect?.value, sortSelect?.value);
    }, { signal });

    sortSelect?.addEventListener('change', () => {
        applyRange(fromSelect?.value, toSelect?.value, sortSelect.value);
    }, { signal });

    const applyPreset = (preset) => {
        let from = firstAvailableYear;
        const to = lastAvailableYear;

        if (preset === 'five-years') {
            from = closestAvailableYear(Number(summary.year) - 4);
        } else if (preset === 'since-2020') {
            from = closestAvailableYear(2020);
        }

        applyRange(from, to);
    };

    root.querySelectorAll('[data-population-preset]').forEach((button) => {
        button.addEventListener('click', () => applyPreset(button.dataset.populationPreset), { signal });
    });

    const trendPanel = root.querySelector('[data-population-trend-panel]');
    const trendTitle = root.querySelector('#population-trend-title');
    const scrollToTrend = (moveFocus = false) => {
        trendPanel?.scrollIntoView({ behavior: reducedMotion ? 'auto' : 'smooth', block: 'start' });
        if (moveFocus) window.setTimeout(() => trendTitle?.focus(), reducedMotion ? 0 : 450);
    };

    root.querySelector('[data-population-trend-toggle]')?.addEventListener('click', () => scrollToTrend(), { signal });

    const exportControls = [...root.querySelectorAll('[data-population-export-toggle]')].map((toggle) => {
        const scope = toggle.dataset.populationExportToggle;
        return {
            scope,
            toggle,
            menu: root.querySelector(`[data-population-export-menu="${scope}"]`),
        };
    });

    const setExportMenuOpen = (control, open, restoreFocus = false) => {
        if (!control?.menu) return;

        control.menu.hidden = !open;
        control.toggle.setAttribute('aria-expanded', String(open));
        if (open) control.menu.querySelector('[role="menuitem"]')?.focus();
        if (!open && restoreFocus) control.toggle.focus();
    };

    exportControls.forEach((control) => {
        control.toggle.addEventListener('click', () => {
            const shouldOpen = control.toggle.getAttribute('aria-expanded') !== 'true';
            exportControls.forEach((item) => setExportMenuOpen(item, item === control && shouldOpen));
        }, { signal });

        const items = [...(control.menu?.querySelectorAll('[role="menuitem"]') || [])];
        control.menu?.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                event.preventDefault();
                setExportMenuOpen(control, false, true);
                return;
            }

            const currentIndex = items.indexOf(document.activeElement);
            if (event.key === 'ArrowDown' || event.key === 'ArrowRight') {
                event.preventDefault();
                items[(currentIndex + 1) % items.length]?.focus();
            }
            if (event.key === 'ArrowUp' || event.key === 'ArrowLeft') {
                event.preventDefault();
                items[(currentIndex - 1 + items.length) % items.length]?.focus();
            }
        }, { signal });
    });

    document.addEventListener('click', (event) => {
        exportControls.forEach((control) => {
            if (
                !control.menu?.hidden
                && !control.menu.contains(event.target)
                && !control.toggle.contains(event.target)
            ) {
                setExportMenuOpen(control, false);
            }
        });
    }, { signal });

    const exportCombinedAsset = async (chart, container, table, format, filename, title) => {
        const downloadedAt = new Intl.DateTimeFormat('id-ID', { dateStyle: 'long' }).format(new Date());
        const asset = combineChartAndTable(
            chart,
            container,
            table,
            title,
            `Pemerintah Desa Sukomulyo · Diunduh ${downloadedAt}`,
        );

        if (format === 'svg') {
            downloadBlob(
                new Blob([asset.svg], { type: 'image/svg+xml;charset=utf-8' }),
                `${filename}.svg`,
            );
            return;
        }

        const raster = await svgToRaster(asset.svg, asset.width, asset.height, format);
        if (format === 'pdf') {
            await savePdf(raster, asset.width * 2, asset.height * 2, `${filename}.pdf`);
            return;
        }

        downloadDataUrl(raster, `${filename}.${format}`);
    };

    root.querySelectorAll('[data-population-export-action]').forEach((button) => {
        button.addEventListener('click', async () => {
            const scope = button.dataset.exportScope;
            const format = button.dataset.exportFormat;
            const control = exportControls.find((item) => item.scope === scope);
            const annualFrom = Number(fromSelect?.value || initialFrom);
            const annualTo = Number(toSelect?.value || initialTo);
            const isActual = scope === 'actual';
            const chart = isActual ? pieChart : lineChart;
            const container = isActual ? pieContainer : lineContainer;
            const table = root.querySelector(isActual ? '.population-summary-table' : '.population-history-table');
            const period = isActual ? String(summary.year) : `${annualFrom}-${annualTo}`;
            const filename = `data-penduduk-${isActual ? 'aktual' : 'tahunan'}-${period}`;
            const title = isActual
                ? `Data Penduduk Aktual Tahun ${summary.year}`
                : `Data Penduduk Tahunan ${annualFrom}–${annualTo}`;

            setExportMenuOpen(control, false);
            button.disabled = true;
            control?.toggle.setAttribute('aria-busy', 'true');

            try {
                if (!chart || !container || container.hidden) {
                    throw new Error('Grafik belum tersedia untuk diunduh.');
                }
                if (!table) throw new Error('Tabel belum tersedia untuk diunduh.');
                await exportCombinedAsset(chart, container, table, format, filename, title);

                showStatus(
                    `Grafik dan tabel data ${isActual ? 'aktual' : 'tahunan'} format ${format.toUpperCase()} berhasil diunduh.`,
                );
            } catch (error) {
                showStatus(error?.message || 'File tidak dapat dibuat. Silakan coba kembali.', true);
            } finally {
                button.disabled = false;
                control?.toggle.removeAttribute('aria-busy');
                control?.toggle.focus();
            }
        }, { signal });
    });

    const resizeObserver = 'ResizeObserver' in window
        ? new ResizeObserver(() => {
            pieChart?.resize();
            lineChart?.resize();
        })
        : null;
    if (pieContainer) resizeObserver?.observe(pieContainer);
    if (lineContainer) resizeObserver?.observe(lineContainer);

    const cleanup = () => {
        pieChart?.dispose();
        lineChart?.dispose();
        resizeObserver?.disconnect();
        controller.abort();
        pieChart = null;
        lineChart = null;
    };

    window.addEventListener('ajax:before-render', cleanup, { once: true, signal });
};
