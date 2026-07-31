import * as echarts from 'echarts/core';
import { BarChart, LineChart, PieChart } from 'echarts/charts';
import {
    DataZoomComponent,
    GridComponent,
    LegendComponent,
    TooltipComponent,
} from 'echarts/components';
import { CanvasRenderer } from 'echarts/renderers';
import {
    combineChartAndTable,
    downloadBlob,
    downloadDataUrl,
    savePdf,
    scalePdfLayout,
    svgToRaster,
} from './population-statistics';
import { bindStatisticsCopyButtons } from './statistics-copy';

echarts.use([
    BarChart,
    LineChart,
    PieChart,
    DataZoomComponent,
    GridComponent,
    LegendComponent,
    TooltipComponent,
    CanvasRenderer,
]);

const COLORS = [
    '#2f6b45',
    '#2f6fb5',
    '#c94f70',
    '#d49a2a',
    '#7656a8',
    '#3b8f8c',
    '#9c5b35',
    '#64748b',
];

const parsePayload = (root) => {
    try {
        return JSON.parse(root.querySelector('#generic-statistics-data')?.textContent || '');
    } catch {
        return null;
    }
};

const createCell = (tag, text) => {
    const cell = document.createElement(tag);
    cell.textContent = text;
    return cell;
};

export const initGenericStatistics = () => {
    const root = document.querySelector('[data-generic-statistics]');
    if (!root || root.dataset.bound === 'true') return;

    root.dataset.bound = 'true';
    const payload = parsePayload(root);
    if (!payload) return;

    const controller = new AbortController();
    const { signal } = controller;
    const { page, latest } = payload;
    const currentTitle = page.current_title || page.title;
    const decimals = Number(page.decimals) || 0;
    const numberFormatter = new Intl.NumberFormat('id-ID', {
        minimumFractionDigits: decimals,
        maximumFractionDigits: decimals,
    });
    const formatValue = (value) => numberFormatter.format(Number(value) || 0);
    const percentageFormatter = new Intl.NumberFormat('id-ID', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
    });
    const pieTotal = latest.items.reduce((total, item) => total + (Number(item.value) || 0), 0);
    const piePercentages = new Map(latest.items.map((item) => [
        item.label,
        pieTotal > 0 ? (Number(item.value) || 0) / pieTotal * 100 : 0,
    ]));
    const allRows = [...(payload.allHistory || payload.history || [])]
        .map((row) => ({
            ...row,
            year: Number(row.year),
            items: row.items.map((item) => ({ ...item, value: Number(item.value) || 0 })),
        }))
        .sort((left, right) => left.year - right.year);
    const labels = [...new Set(allRows.flatMap((row) => row.items.map((item) => item.label)))];
    const fromSelect = root.querySelector('[data-generic-from-year]');
    const toSelect = root.querySelector('[data-generic-to-year]');
    const sortSelect = root.querySelector('[data-generic-sort]');
    const seriesSelect = root.querySelector('[data-generic-series]');
    const status = root.querySelector('[data-generic-filter-status]');
    const currentContainer = root.querySelector('[data-generic-current-chart]');
    const trendContainer = root.querySelector('[data-generic-trend-chart]');
    const historyBody = root.querySelector('[data-generic-history-body]');
    const currentTable = root.querySelector('[data-generic-current-table]');
    const historyTable = root.querySelector('[data-generic-history-table]');
    const compactPie = (currentContainer?.clientWidth || 0) < 640;
    const pieLegendNameWidth = compactPie
        ? 84
        : Math.max(110, Math.min(150, Math.round((currentContainer?.clientWidth || 760) * 0.18)));
    const pieLegendLineHeight = compactPie ? 20 : 24;
    let currentChart = null;
    let trendChart = null;
    let visibleRows = [];

    const showStatus = (message, error = false) => {
        if (!status) return;
        status.textContent = message;
        status.classList.toggle('is-error', error);
    };

    if (currentContainer && latest.items.length) {
        if (page.chart !== 'pie') {
            currentContainer.style.minHeight = `${Math.max(360, latest.items.length * 38)}px`;
        }
        currentChart = echarts.init(currentContainer, null, { renderer: 'canvas' });
        const isPie = page.chart === 'pie';
        currentChart.setOption({
            color: COLORS,
            tooltip: {
                trigger: isPie ? 'item' : 'axis',
                axisPointer: { type: 'shadow' },
                formatter: (item) => {
                    const entry = Array.isArray(item) ? item[0] : item;
                    const data = isPie ? entry.data : latest.items[entry.dataIndex];
                    return `<strong>${data.label || data.name}</strong><br>${formatValue(data.value)} ${data.unit || page.unit}`;
                },
            },
            legend: isPie
                ? {
                    orient: 'vertical',
                    right: '1%',
                    top: 'middle',
                    width: '38%',
                    type: 'scroll',
                    itemWidth: compactPie ? 12 : 18,
                    itemHeight: compactPie ? 12 : 18,
                    itemGap: compactPie ? 10 : 12,
                    selectedMode: true,
                    formatter: (name) => `{name|${name}}{value|${percentageFormatter.format(piePercentages.get(name) || 0)}%}`,
                    textStyle: {
                        color: '#26352a',
                        fontSize: compactPie ? 10 : 13,
                        rich: {
                            name: {
                                width: pieLegendNameWidth,
                                overflow: 'truncate',
                                lineHeight: pieLegendLineHeight,
                            },
                            value: {
                                width: compactPie ? 50 : 70,
                                align: 'right',
                                fontWeight: 700,
                                lineHeight: pieLegendLineHeight,
                            },
                        },
                    },
                }
                : undefined,
            grid: isPie
                ? undefined
                : { left: 0, right: 20, top: 12, bottom: 12, containLabel: true },
            xAxis: isPie
                ? undefined
                : {
                    type: 'value',
                    min: 0,
                    axisLabel: { color: '#69766d', formatter: (value) => formatValue(value) },
                    splitLine: { lineStyle: { color: '#dce5dc', type: 'dashed' } },
                },
            yAxis: isPie
                ? undefined
                : {
                    type: 'category',
                    data: latest.items.map((item) => item.label),
                    axisLabel: { color: '#26352a' },
                    axisLine: { lineStyle: { color: '#dce5dc' } },
                },
            series: isPie
                ? [{
                    type: 'pie',
                    radius: compactPie ? '48%' : '68%',
                    center: compactPie ? ['25%', '50%'] : ['28%', '50%'],
                    itemStyle: { borderColor: '#fff', borderWidth: 3, borderRadius: 6 },
                    label: {
                        color: '#26352a',
                        fontWeight: 600,
                        fontSize: compactPie ? 10 : 12,
                        formatter: ({ percent }) => `${percentageFormatter.format(percent)}%`,
                    },
                    labelLine: {
                        show: true,
                        length: compactPie ? 6 : 12,
                        length2: compactPie ? 4 : 10,
                    },
                    data: latest.items.map((item) => ({ name: item.label, value: item.value, unit: item.unit, label: item.label })),
                }]
                : [{
                    type: 'bar',
                    barMaxWidth: 34,
                    data: latest.items.map((item, index) => ({
                        value: item.value,
                        itemStyle: { color: COLORS[index % COLORS.length] },
                    })),
                    label: {
                        show: true,
                        position: 'right',
                        color: '#26352a',
                        formatter: ({ value }) => formatValue(value),
                    },
                }],
        });
    } else {
        currentContainer?.setAttribute('hidden', '');
        root.querySelector('[data-generic-current-empty]')?.removeAttribute('hidden');
    }

    const renderTrendChart = (rows) => {
        if (!trendContainer) return;
        const sortedRows = [...rows].sort((left, right) => left.year - right.year);
        const selectedLabel = seriesSelect?.value || labels[0];
        const selectedUnit = latest.items.find((item) => item.label === selectedLabel)?.unit || page.unit;
        const hasData = sortedRows.length > 0;
        trendContainer.toggleAttribute('hidden', !hasData);
        root.querySelector('[data-generic-trend-empty]')?.toggleAttribute('hidden', hasData);

        if (!hasData) {
            trendChart?.clear();
            return;
        }
        if (!trendChart) trendChart = echarts.init(trendContainer, null, { renderer: 'canvas' });

        trendChart.setOption({
            color: [COLORS[Math.max(0, labels.indexOf(selectedLabel)) % COLORS.length]],
            tooltip: {
                trigger: 'axis',
                formatter: (items) => {
                    const year = Number(items[0]?.axisValue);
                    const row = sortedRows.find((item) => item.year === year);
                    const item = row?.items.find((entry) => entry.label === selectedLabel);
                    if (!row || !item) return `<strong>${selectedLabel}</strong><br>${year}<br>Data belum tersedia`;
                    const percentage = row.total > 0 ? item.value / row.total * 100 : 0;
                    const lines = [
                        `<strong>${selectedLabel}</strong>`,
                        `Tahun ${year}`,
                        `Jumlah: ${formatValue(item.value)} ${item.unit}`,
                    ];
                    if (page.show_total && page.unit !== 'data') {
                        lines.push(`Persentase: ${new Intl.NumberFormat('id-ID', { maximumFractionDigits: 2 }).format(percentage)}%`);
                    }
                    return lines.join('<br>');
                },
            },
            legend: {
                bottom: 0,
                type: 'scroll',
                textStyle: { color: '#26352a' },
            },
            grid: {
                left: 0,
                right: 0,
                top: 30,
                bottom: 72,
                containLabel: true,
            },
            xAxis: {
                type: 'category',
                boundaryGap: false,
                data: sortedRows.map((row) => row.year),
                axisLabel: { color: '#69766d' },
                axisLine: { lineStyle: { color: '#dce5dc' } },
            },
            yAxis: {
                type: 'value',
                min: 0,
                name: selectedUnit,
                axisLabel: { color: '#69766d', formatter: (value) => formatValue(value) },
                splitLine: { lineStyle: { color: '#dce5dc', type: 'dashed' } },
            },
            dataZoom: sortedRows.length > 8
                ? [
                    { type: 'inside', start: 0, end: 100 },
                    { type: 'slider', height: 20, bottom: 34, start: 0, end: 100 },
                ]
                : [],
            series: [{
                name: selectedLabel,
                type: 'line',
                connectNulls: false,
                symbolSize: 8,
                lineStyle: { width: 3 },
                data: sortedRows.map((row) => row.items.find((item) => item.label === selectedLabel)?.value ?? null),
            }],
        }, true);
    };

    const renderHistoryTable = (rows, sort) => {
        if (!historyBody) return;
        const ordered = sort === 'desc' ? [...rows].reverse() : [...rows];
        historyBody.replaceChildren();

        if (!ordered.length) {
            const row = document.createElement('tr');
            const cell = createCell('td', 'Data tahunan belum tersedia pada rentang ini.');
            cell.colSpan = labels.length + (page.show_total ? 3 : 2);
            row.append(cell);
            historyBody.append(row);
            return;
        }

        ordered.forEach((item) => {
            const row = document.createElement('tr');
            const year = createCell('th', String(item.year));
            year.scope = 'row';
            row.append(year);
            labels.forEach((label) => {
                const value = item.items.find((entry) => entry.label === label);
                row.append(createCell('td', value ? `${formatValue(value.value)} ${value.unit}` : '—'));
            });
            if (page.show_total) {
                const total = createCell('td', '');
                const strong = document.createElement('strong');
                strong.textContent = formatValue(item.total);
                total.append(strong);
                row.append(total);
            }
            row.append(createCell('td', item.source));
            historyBody.append(row);
        });
    };

    const applyRange = (fromValue, toValue, sort = sortSelect?.value || 'asc', announce = true) => {
        const from = Number(fromValue);
        const to = Number(toValue);
        if (!Number.isInteger(from) || !Number.isInteger(to) || from > to) {
            showStatus('Tahun awal tidak boleh lebih besar dari tahun akhir.', true);
            return;
        }

        visibleRows = allRows.filter((row) => row.year >= from && row.year <= to);
        renderTrendChart(visibleRows);
        renderHistoryTable(visibleRows, sort);
        if (announce) showStatus(`Menampilkan ${seriesSelect?.value || labels[0]} periode ${from}–${to}.`);
    };

    applyRange(
        fromSelect?.value || payload.defaultRange.from,
        toSelect?.value || payload.defaultRange.to,
        payload.sort || 'asc',
        false,
    );

    root.querySelector('[data-generic-year-filter]')?.addEventListener('submit', (event) => {
        event.preventDefault();
        applyRange(fromSelect?.value, toSelect?.value, sortSelect?.value);
    }, { signal });
    sortSelect?.addEventListener('change', () => {
        applyRange(fromSelect?.value, toSelect?.value, sortSelect.value);
    }, { signal });
    seriesSelect?.addEventListener('change', () => {
        applyRange(fromSelect?.value, toSelect?.value, sortSelect?.value);
    }, { signal });

    bindStatisticsCopyButtons({
        root,
        charts: {
            'current-chart': () => currentChart,
            'trend-chart': () => trendChart,
        },
        tables: {
            'current-table': () => currentTable,
            'history-table': () => historyTable,
        },
        signal,
    });

    const controls = [...root.querySelectorAll('[data-generic-export-toggle]')].map((toggle) => ({
        scope: toggle.dataset.genericExportToggle,
        toggle,
        menu: root.querySelector(`[data-generic-export-menu="${toggle.dataset.genericExportToggle}"]`),
    }));
    const setMenuOpen = (control, open, restoreFocus = false) => {
        if (!control?.menu) return;
        control.menu.hidden = !open;
        control.toggle.setAttribute('aria-expanded', String(open));
        if (open) control.menu.querySelector('[role="menuitem"]')?.focus();
        if (!open && restoreFocus) control.toggle.focus();
    };

    controls.forEach((control) => {
        control.toggle.addEventListener('click', () => {
            const open = control.toggle.getAttribute('aria-expanded') !== 'true';
            controls.forEach((item) => setMenuOpen(item, item === control && open));
        }, { signal });
        const items = [...control.menu.querySelectorAll('[role="menuitem"]')];
        control.menu.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                event.preventDefault();
                setMenuOpen(control, false, true);
                return;
            }
            const index = items.indexOf(document.activeElement);
            if (['ArrowDown', 'ArrowRight'].includes(event.key)) {
                event.preventDefault();
                items[(index + 1) % items.length]?.focus();
            }
            if (['ArrowUp', 'ArrowLeft'].includes(event.key)) {
                event.preventDefault();
                items[(index - 1 + items.length) % items.length]?.focus();
            }
        }, { signal });
    });

    document.addEventListener('click', (event) => {
        controls.forEach((control) => {
            if (!control.menu.hidden && !control.menu.contains(event.target) && !control.toggle.contains(event.target)) {
                setMenuOpen(control, false);
            }
        });
    }, { signal });

    const exportAsset = async (scope, format) => {
        const actual = scope === 'actual';
        const chart = actual ? currentChart : trendChart;
        const container = actual ? currentContainer : trendContainer;
        const table = actual ? currentTable : historyTable;
        if (!chart || !container || !table || container.hidden) {
            throw new Error('Grafik atau tabel belum tersedia untuk diunduh.');
        }

        const range = actual
            ? String(latest.year)
            : `${fromSelect?.value || ''}-${toSelect?.value || ''}`;
        const filename = `${payload.context}-${actual ? 'aktual' : 'tahunan'}-${range}`;
        const title = actual
            ? `${currentTitle} Tahun ${latest.year}`
            : `${currentTitle} Tahun ${fromSelect?.value}–${toSelect?.value}`;
        const date = new Intl.DateTimeFormat('id-ID', { dateStyle: 'long' }).format(new Date());
        const asset = combineChartAndTable(
            chart,
            container,
            table,
            title,
            `Pemerintah Desa Sukomulyo · Diunduh ${date}`,
        );

        if (format === 'svg') {
            downloadBlob(new Blob([asset.svg], { type: 'image/svg+xml;charset=utf-8' }), `${filename}.svg`);
            return;
        }
        const raster = await svgToRaster(asset.svg, asset.width, asset.height, format);
        if (format === 'pdf') {
            await savePdf(
                raster,
                asset.width * 2,
                asset.height * 2,
                `${filename}.pdf`,
                scalePdfLayout(asset, 2),
            );
            return;
        }
        downloadDataUrl(raster, `${filename}.${format}`);
    };

    root.querySelectorAll('[data-generic-export-action]').forEach((button) => {
        button.addEventListener('click', async () => {
            const scope = button.dataset.exportScope;
            const format = button.dataset.exportFormat;
            const control = controls.find((item) => item.scope === scope);
            setMenuOpen(control, false);
            button.disabled = true;
            control?.toggle.setAttribute('aria-busy', 'true');
            try {
                await exportAsset(scope, format);
                showStatus(`Grafik dan tabel format ${format.toUpperCase()} berhasil diunduh.`);
            } catch (error) {
                showStatus(error?.message || 'File tidak dapat dibuat.', true);
            } finally {
                button.disabled = false;
                control?.toggle.removeAttribute('aria-busy');
                control?.toggle.focus();
            }
        }, { signal });
    });

    const resizeObserver = 'ResizeObserver' in window
        ? new ResizeObserver(() => {
            currentChart?.resize();
            trendChart?.resize();
        })
        : null;
    if (currentContainer) resizeObserver?.observe(currentContainer);
    if (trendContainer) resizeObserver?.observe(trendContainer);

    const cleanup = () => {
        currentChart?.dispose();
        trendChart?.dispose();
        resizeObserver?.disconnect();
        controller.abort();
    };
    window.addEventListener('ajax:before-render', cleanup, { once: true, signal });
};
