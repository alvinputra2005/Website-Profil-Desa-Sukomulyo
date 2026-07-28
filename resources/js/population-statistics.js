import * as echarts from 'echarts/core';
import { LineChart, PieChart } from 'echarts/charts';
import {
    DataZoomComponent,
    DatasetComponent,
    GridComponent,
    LegendComponent,
    TitleComponent,
    ToolboxComponent,
    TooltipComponent,
} from 'echarts/components';
import { CanvasRenderer } from 'echarts/renderers';

echarts.use([
    PieChart,
    LineChart,
    DatasetComponent,
    GridComponent,
    LegendComponent,
    TitleComponent,
    ToolboxComponent,
    TooltipComponent,
    DataZoomComponent,
    CanvasRenderer,
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
            title: {
                text: 'TOTAL',
                subtext: `${formatInteger(summary.total)} jiwa`,
                left: 'center',
                top: '39%',
                textStyle: { color: COLORS.muted, fontSize: 12, fontWeight: 700 },
                subtextStyle: { color: COLORS.text, fontSize: 19, fontWeight: 700, lineHeight: 28 },
            },
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
                radius: ['51%', '74%'],
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
                top: 4,
                textStyle: { color: COLORS.text },
            },
            toolbox: {
                right: 8,
                feature: {
                    saveAsImage: {
                        title: 'Unduh grafik',
                        name: `pertumbuhan-penduduk-${fromYear}-${toYear}`,
                        pixelRatio: 2,
                    },
                },
            },
            grid: {
                left: 18,
                right: 24,
                top: 72,
                bottom: years.length > 8 ? 76 : 42,
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
                    { type: 'slider', height: 22, bottom: 12, start: 0, end: 100 },
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

    const menuToggle = root.querySelector('[data-population-analytics-toggle]');
    const menu = root.querySelector('[data-population-analytics-menu]');
    const menuItems = [...(menu?.querySelectorAll('[role="menuitem"]') || [])];
    const setMenuOpen = (open, restoreFocus = false) => {
        if (!menu || !menuToggle) return;

        menu.hidden = !open;
        menuToggle.setAttribute('aria-expanded', String(open));
        if (open) menuItems[0]?.focus();
        if (!open && restoreFocus) menuToggle.focus();
    };

    menuToggle?.addEventListener('click', () => {
        setMenuOpen(menuToggle.getAttribute('aria-expanded') !== 'true');
    }, { signal });

    menu?.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            event.preventDefault();
            setMenuOpen(false, true);
            return;
        }

        const index = menuItems.indexOf(document.activeElement);
        if (event.key === 'ArrowDown') {
            event.preventDefault();
            menuItems[(index + 1) % menuItems.length]?.focus();
        }
        if (event.key === 'ArrowUp') {
            event.preventDefault();
            menuItems[(index - 1 + menuItems.length) % menuItems.length]?.focus();
        }
    }, { signal });

    document.addEventListener('click', (event) => {
        if (!menu?.hidden && !menu.contains(event.target) && !menuToggle?.contains(event.target)) {
            setMenuOpen(false);
        }
    }, { signal });

    menuItems.forEach((item) => {
        item.addEventListener('click', () => {
            const action = item.dataset.populationAction;
            setMenuOpen(false, true);

            if (action === 'download-pie') {
                if (!pieChart) {
                    showStatus('Grafik komposisi belum tersedia untuk diunduh.', true);
                    return;
                }

                const link = document.createElement('a');
                link.download = `komposisi-penduduk-${summary.year}.png`;
                link.href = pieChart.getDataURL({ type: 'png', pixelRatio: 2, backgroundColor: '#ffffff' });
                link.click();
                return;
            }

            applyPreset(action);
            scrollToTrend(true);
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
