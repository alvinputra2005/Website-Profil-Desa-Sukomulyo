<?php

namespace App\Services;

use App\Models\StatisticDataset;

class StatisticPageData
{
    public function voters2024(PopulationStatistics $populationStatistics): array
    {
        $summary = $populationStatistics->summary();
        $latest = [
            'year' => 2024,
            'source' => 'Administrasi Kependudukan Desa',
            'updated_at' => $populationStatistics->residents()->max('updated_at'),
            'items' => [
                ['label' => 'Laki-laki', 'value' => (float) $summary['male'], 'unit' => 'pemilih'],
                ['label' => 'Perempuan', 'value' => (float) $summary['female'], 'unit' => 'pemilih'],
            ],
            'total' => (float) ($summary['male'] + $summary['female']),
        ];

        return [
            'context' => 'jumlah-pemilih-2024',
            'page' => [
                'title' => 'Statistik Kependudukan',
                'current_title' => 'Jumlah Pemilih',
                'trend_title' => 'Jumlah Pemilih',
                'description' => 'Jumlah pemilih Desa Sukomulyo tahun 2024 berdasarkan data administrasi kependudukan desa.',
                'chart' => 'pie',
                'unit' => 'pemilih',
                'decimals' => 0,
                'show_total' => true,
            ],
            'latest' => $latest,
            'history' => [$latest],
            'allHistory' => [$latest],
            'availableYears' => [2024],
            'defaultRange' => ['from' => 2024, 'to' => 2024],
            'tableSort' => 'asc',
            'selectedDataset' => 'jumlah-pemilih-2024',
            'categoryName' => 'Kependudukan',
        ];
    }

    public function populationContext(?string $menu): ?string
    {
        return config('statistic_pages.population_menus.'.($menu ?? ''));
    }

    public function sectionContext(string $section, ?string $menu): ?string
    {
        $contexts = config("statistic_pages.section_contexts.{$section}", []);
        $menu = (string) ($menu ?? '');

        return $contexts[$menu] ?? ($menu === '' ? ($contexts[''] ?? null) : null);
    }

    public function build(string $context, array $filters = []): array
    {
        $definition = config("statistic_pages.contexts.{$context}");
        abort_unless(is_array($definition), 404);

        $datasets = StatisticDataset::query()
            ->with('values')
            ->where('category', $context)
            ->where('status', 'published')
            ->orderBy('year')
            ->orderBy('display_order')
            ->get()
            ->unique('year')
            ->values();

        $years = $datasets->pluck('year')->map(fn ($year): int => (int) $year)->all();
        $latest = $datasets->last();
        $minimumYear = $years[0] ?? (int) config('village.population_year', now()->year);
        $maximumYear = $years !== [] ? (int) end($years) : $minimumYear;
        $defaultFrom = max($minimumYear, $maximumYear - 4);
        $requestedFrom = isset($filters['from_year']) ? (int) $filters['from_year'] : null;
        $requestedTo = isset($filters['to_year']) ? (int) $filters['to_year'] : null;
        $from = in_array($requestedFrom, $years, true) ? $requestedFrom : $defaultFrom;
        $to = in_array($requestedTo, $years, true) ? $requestedTo : $maximumYear;

        if ($from > $to) {
            [$from, $to] = [$defaultFrom, $maximumYear];
        }

        $history = $datasets
            ->filter(fn (StatisticDataset $dataset): bool => $dataset->year >= $from && $dataset->year <= $to)
            ->map(fn (StatisticDataset $dataset): array => $this->datasetRow($dataset, $definition))
            ->values();
        $allHistory = $datasets
            ->map(fn (StatisticDataset $dataset): array => $this->datasetRow($dataset, $definition))
            ->values()
            ->all();

        if (($filters['sort'] ?? 'asc') === 'desc') {
            $history = $history->reverse()->values();
        }

        return [
            'context' => $context,
            'page' => [
                'title' => $definition['title'],
                'trend_title' => $definition['trend_title'] ?? preg_replace('/^Statistik /', '', $definition['title']),
                'description' => $definition['description'],
                'chart' => $definition['chart'] ?? 'bar',
                'unit' => $definition['unit'] ?? 'data',
                'decimals' => (int) ($definition['decimals'] ?? 0),
                'show_total' => (bool) ($definition['show_total'] ?? true),
            ],
            'latest' => $latest
                ? $this->datasetRow($latest, $definition)
                : [
                    'year' => $maximumYear,
                    'source' => 'Data belum tersedia',
                    'updated_at' => null,
                    'items' => [],
                    'total' => 0,
                ],
            'history' => $history->all(),
            'allHistory' => $allHistory,
            'availableYears' => $years,
            'defaultRange' => ['from' => $from, 'to' => $to],
            'tableSort' => $filters['sort'] ?? 'asc',
        ];
    }

    private function datasetRow(StatisticDataset $dataset, array $definition): array
    {
        $defaultUnit = $definition['unit'] ?? $dataset->unit;
        $items = $dataset->values->map(fn ($value): array => [
            'label' => $value->label,
            'value' => (float) $value->value,
            'unit' => $value->metadata_json['unit'] ?? $defaultUnit,
        ])->values();

        return [
            'year' => (int) $dataset->year,
            'source' => $dataset->source ?: 'Pemerintah Desa Sukomulyo',
            'updated_at' => $dataset->updated_at?->toAtomString(),
            'items' => $items->all(),
            'total' => $items->sum('value'),
        ];
    }
}
