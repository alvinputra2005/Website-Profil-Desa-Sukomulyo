import * as echarts from 'echarts/core';
import { BarChart, LineChart, PieChart } from 'echarts/charts';
import {
    GridComponent,
    LegendComponent,
    TitleComponent,
    TooltipComponent,
} from 'echarts/components';
import { CanvasRenderer, SVGRenderer } from 'echarts/renderers';
import {
    combineChartAndTable,
    combineExportSections,
    downloadBlob,
    downloadDataUrl,
    savePdf,
    scalePdfLayout,
    svgToRaster,
} from './population-statistics';

echarts.use([
    BarChart,
    LineChart,
    PieChart,
    GridComponent,
    LegendComponent,
    TitleComponent,
    TooltipComponent,
    CanvasRenderer,
    SVGRenderer,
]);

const COLORS = {
    income: '#2f6b45',
    spending: '#d49a2a',
    realization: '#2f6fb5',
    remaining: '#cbd5c5',
    grid: '#dfe6dc',
    text: '#26352a',
    muted: '#69766d',
    allocation: ['#2f6b45', '#4f875e', '#d49a2a', '#2f6fb5', '#9c5b35'],
};

const currencyFormatter = new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
});

const compactCurrency = (value) => {
    const number = Number(value) || 0;
    if (Math.abs(number) >= 1_000_000_000) {
        return `Rp${new Intl.NumberFormat('id-ID', { maximumFractionDigits: 2 }).format(number / 1_000_000_000)} M`;
    }
    if (Math.abs(number) >= 1_000_000) {
        return `Rp${new Intl.NumberFormat('id-ID', { maximumFractionDigits: 1 }).format(number / 1_000_000)} Jt`;
    }
    return currencyFormatter.format(number);
};

const parsePayload = (root) => {
    try {
        return JSON.parse(root.querySelector('[data-budget-payload]')?.textContent || '');
    } catch {
        return null;
    }
};

const baseAxis = {
    axisLine: { lineStyle: { color: '#bdc9ba' } },
    axisLabel: { color: COLORS.muted, fontSize: 11 },
    axisTick: { show: false },
};

const createHistoryChart = (container, history) => {
    if (!container) return null;

    const rows = [...history].sort((left, right) => Number(left.year) - Number(right.year));
    const chart = echarts.init(container, null, { renderer: 'canvas' });
    chart.setOption({
        animationDuration: 550,
        color: [COLORS.income, COLORS.spending, COLORS.realization],
        tooltip: {
            trigger: 'axis',
            valueFormatter: (value) => currencyFormatter.format(Number(value) || 0),
        },
        legend: {
            top: 0,
            textStyle: { color: COLORS.text, fontSize: 11 },
        },
        grid: { top: 50, right: 16, bottom: 32, left: 12, containLabel: true },
        xAxis: {
            ...baseAxis,
            type: 'category',
            boundaryGap: false,
            data: rows.map((row) => String(row.year)),
        },
        yAxis: {
            ...baseAxis,
            type: 'value',
            axisLabel: {
                ...baseAxis.axisLabel,
                inside: false,
                padding: [0, 8, 0, 0],
                formatter: compactCurrency,
            },
            splitLine: { lineStyle: { color: COLORS.grid, type: 'dashed' } },
        },
        series: [
            {
                name: 'Pendapatan',
                type: 'line',
                smooth: true,
                symbolSize: 7,
                lineStyle: { width: 3 },
                areaStyle: { opacity: .06 },
                data: rows.map((row) => Number(row.income)),
            },
            {
                name: 'Belanja',
                type: 'line',
                smooth: true,
                symbolSize: 7,
                lineStyle: { width: 3 },
                data: rows.map((row) => Number(row.spending)),
            },
            {
                name: 'Realisasi Belanja',
                type: 'line',
                smooth: true,
                symbolSize: 7,
                lineStyle: { width: 3 },
                data: rows.map((row) => Number(row.realization)),
            },
        ],
    });

    return chart;
};

const createAllocationChart = (container, spending) => {
    if (!container) return null;

    const chart = echarts.init(container, null, { renderer: 'canvas' });
    chart.setOption({
        animationDuration: 550,
        color: COLORS.allocation,
        tooltip: {
            trigger: 'item',
            formatter: ({ name, value, percent }) => `${name}<br>${currencyFormatter.format(value)} (${percent}%)`,
        },
        legend: {
            type: 'scroll',
            bottom: 0,
            textStyle: { color: COLORS.text, fontSize: 10 },
        },
        series: [{
            name: 'Alokasi Belanja',
            type: 'pie',
            radius: ['43%', '69%'],
            center: ['50%', '43%'],
            avoidLabelOverlap: true,
            itemStyle: { borderColor: '#fff', borderWidth: 3 },
            label: {
                color: COLORS.text,
                fontSize: 10,
                formatter: ({ percent }) => `${percent}%`,
            },
            data: spending.map((item) => ({
                name: item.short_name,
                value: Number(item.budget),
            })),
        }],
    });

    return chart;
};

const createComparisonChart = (container, spending) => {
    if (!container) return null;

    const chart = echarts.init(container, null, { renderer: 'canvas' });
    chart.setOption({
        animationDuration: 550,
        color: [COLORS.spending, COLORS.realization],
        tooltip: {
            trigger: 'axis',
            axisPointer: { type: 'shadow' },
            valueFormatter: (value) => currencyFormatter.format(Number(value) || 0),
        },
        legend: {
            top: 0,
            textStyle: { color: COLORS.text, fontSize: 11 },
        },
        grid: { top: 48, right: 20, bottom: 48, left: 72 },
        xAxis: {
            ...baseAxis,
            type: 'category',
            data: spending.map((item) => item.short_name),
            axisLabel: {
                ...baseAxis.axisLabel,
                interval: 0,
                rotate: 18,
            },
        },
        yAxis: {
            ...baseAxis,
            type: 'value',
            axisLabel: { ...baseAxis.axisLabel, formatter: compactCurrency },
            splitLine: { lineStyle: { color: COLORS.grid, type: 'dashed' } },
        },
        series: [
            {
                name: 'Anggaran',
                type: 'bar',
                barMaxWidth: 30,
                data: spending.map((item) => Number(item.budget)),
            },
            {
                name: 'Realisasi',
                type: 'bar',
                barMaxWidth: 30,
                data: spending.map((item) => Number(item.realization)),
            },
        ],
    });

    return chart;
};

const createQuarterChart = (container, quarters) => {
    if (!container) return null;

    const chart = echarts.init(container, null, { renderer: 'canvas' });
    chart.setOption({
        animationDuration: 550,
        color: [COLORS.realization],
        tooltip: {
            trigger: 'axis',
            formatter: (items) => {
                const item = items[0];
                const quarter = quarters[item.dataIndex];
                return `${quarter.quarter}<br>${currencyFormatter.format(quarter.cumulative)} (${quarter.percentage}%)`;
            },
        },
        grid: { top: 30, right: 24, bottom: 38, left: 75 },
        xAxis: {
            ...baseAxis,
            type: 'category',
            boundaryGap: false,
            data: quarters.map((quarter) => quarter.quarter),
        },
        yAxis: {
            ...baseAxis,
            type: 'value',
            axisLabel: { ...baseAxis.axisLabel, formatter: compactCurrency },
            splitLine: { lineStyle: { color: COLORS.grid, type: 'dashed' } },
        },
        series: [{
            name: 'Realisasi Kumulatif',
            type: 'line',
            smooth: true,
            symbolSize: 8,
            lineStyle: { width: 3 },
            areaStyle: { opacity: .1 },
            data: quarters.map((quarter) => Number(quarter.cumulative)),
        }],
    });

    return chart;
};

const initExportControls = (root, payload, chartMap) => {
    const controls = [...root.querySelectorAll('[data-budget-export-toggle]')].map((toggle) => {
        const scope = toggle.dataset.budgetExportToggle;
        return {
            scope,
            toggle,
            menu: root.querySelector(`[data-budget-export-menu="${scope}"]`),
        };
    });
    const status = root.querySelector('[data-budget-export-status]');

    const setOpen = (control, open, restoreFocus = false) => {
        if (!control?.menu) return;
        control.menu.hidden = !open;
        control.toggle.setAttribute('aria-expanded', String(open));
        if (open) control.menu.querySelector('[role="menuitem"]')?.focus();
        if (!open && restoreFocus) control.toggle.focus();
    };

    controls.forEach((control) => {
        control.toggle.addEventListener('click', () => {
            const shouldOpen = control.toggle.getAttribute('aria-expanded') !== 'true';
            controls.forEach((item) => setOpen(item, item === control && shouldOpen));
        });
        control.menu?.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                event.preventDefault();
                setOpen(control, false, true);
            }
        });
    });

    document.addEventListener('click', (event) => {
        controls.forEach((control) => {
            if (!control.menu?.hidden && !control.menu.contains(event.target) && !control.toggle.contains(event.target)) {
                setOpen(control, false);
            }
        });
    });

    root.querySelectorAll('[data-budget-export-action]').forEach((button) => {
        button.addEventListener('click', async () => {
            const scope = button.dataset.exportScope;
            const format = button.dataset.exportFormat;
            const control = controls.find((item) => item.scope === scope);
            const isHistory = scope === 'history';
            const chart = isHistory ? chartMap.history : chartMap.comparison;
            const container = isHistory
                ? root.querySelector('[data-budget-trend-chart]')
                : root.querySelector('[data-budget-comparison-chart]');
            const table = isHistory
                ? root.querySelector('.budget-history-table')
                : root.querySelector('[data-budget-export-table]');
            const year = payload.budget?.summary?.year;
            const historyYears = (payload.history || [])
                .map((item) => Number(item.year))
                .filter(Number.isFinite);
            const firstYear = historyYears.length ? Math.min(...historyYears) : 2019;
            const lastYear = historyYears.length ? Math.max(...historyYears) : 2025;
            const periodLabel = `${firstYear}–${lastYear}`;
            const title = isHistory
                ? `Riwayat APBDes Desa Sukomulyo ${periodLabel}`
                : `Detail APBDes Desa Sukomulyo Tahun ${year}`;
            const filename = isHistory
                ? `riwayat-apbdes-${firstYear}-${lastYear}`
                : `apbdes-sukomulyo-${year}`;

            setOpen(control, false);
            button.disabled = true;
            control?.toggle.setAttribute('aria-busy', 'true');

            try {
                const downloadedAt = new Intl.DateTimeFormat('id-ID', { dateStyle: 'long' }).format(new Date());
                const subtitle = `Pemerintah Desa Sukomulyo · Diunduh ${downloadedAt}`;
                let asset;

                if (isHistory) {
                    if (!chart || !container || !table) throw new Error('Grafik atau tabel belum tersedia untuk diunduh.');
                    const exportTable = table.cloneNode(true);
                    exportTable.querySelectorAll('[data-budget-export-exclude]').forEach((cell) => cell.remove());
                    asset = combineChartAndTable(chart, container, exportTable, title, subtitle);
                } else {
                    const sections = [...root.querySelectorAll('[data-budget-export-section]')].map((element) => {
                        const sectionTitle = element.dataset.budgetExportTitle || 'Data APBDes';
                        const chartKey = element.dataset.budgetExportChart;

                        if (chartKey) {
                            return {
                                title: sectionTitle,
                                chart: chartMap[chartKey],
                                container: element,
                            };
                        }

                        const exportTable = element.cloneNode(true);
                        exportTable.querySelectorAll('[data-budget-export-exclude]').forEach((cell) => cell.remove());
                        return { title: sectionTitle, table: exportTable };
                    }).filter((section) => section.table || (section.chart && section.container));

                    if (sections.length === 0) throw new Error('Data detail APBDes belum tersedia untuk diunduh.');
                    asset = combineExportSections(sections, title, subtitle);
                }

                if (format === 'svg') {
                    downloadBlob(new Blob([asset.svg], { type: 'image/svg+xml;charset=utf-8' }), `${filename}.svg`);
                } else {
                    const raster = await svgToRaster(asset.svg, asset.width, asset.height, format);
                    if (format === 'pdf') {
                        await savePdf(
                            raster,
                            asset.width * 2,
                            asset.height * 2,
                            `${filename}.pdf`,
                            scalePdfLayout(asset, 2),
                        );
                    } else {
                        downloadDataUrl(raster, `${filename}.${format}`);
                    }
                }

                if (status) {
                    status.textContent = isHistory
                        ? `Data APBDes format ${format.toUpperCase()} berhasil diunduh.`
                        : `Detail APBDes lengkap format ${format.toUpperCase()} berhasil diunduh.`;
                }
            } catch (error) {
                if (status) status.textContent = error?.message || 'File APBDes tidak dapat dibuat.';
            } finally {
                button.disabled = false;
                control?.toggle.removeAttribute('aria-busy');
                control?.toggle.focus();
            }
        });
    });
};

export const initBudgetHistory = () => {
    const root = document.querySelector('[data-budget-history]');
    if (!root || root.dataset.bound === 'true') return;

    root.dataset.bound = 'true';
    const payload = parsePayload(root);
    if (!payload) return;

    const charts = [];
    const chartMap = {};

    if (payload.mode === 'history') {
        chartMap.history = createHistoryChart(root.querySelector('[data-budget-trend-chart]'), payload.history || []);
        if (chartMap.history) charts.push(chartMap.history);
    }

    if (payload.mode === 'detail' && payload.budget) {
        const { spending = [], quarters = [] } = payload.budget;
        chartMap.allocation = createAllocationChart(root.querySelector('[data-budget-allocation-chart]'), spending);
        chartMap.comparison = createComparisonChart(root.querySelector('[data-budget-comparison-chart]'), spending);
        if (quarters.length > 0) {
            chartMap.quarter = createQuarterChart(root.querySelector('[data-budget-quarter-chart]'), quarters);
        }
        charts.push(chartMap.allocation, chartMap.comparison, chartMap.quarter);
    }

    initExportControls(root, payload, chartMap);

    if ('ResizeObserver' in window) {
        const observer = new ResizeObserver(() => charts.filter(Boolean).forEach((chart) => chart.resize()));
        observer.observe(root);
    } else {
        window.addEventListener('resize', () => charts.filter(Boolean).forEach((chart) => chart.resize()));
    }
};
