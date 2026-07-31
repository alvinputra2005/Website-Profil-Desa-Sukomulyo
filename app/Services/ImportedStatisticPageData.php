<?php

namespace App\Services;

use App\Models\StatisticCategory;
use App\Models\StatisticDataset;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ImportedStatisticPageData
{
    public function build(StatisticCategory $category, array $filters = []): array
    {
        $datasets = StatisticDataset::query()
            ->with('rows')
            ->where('statistic_category_id', $category->id)
            ->whereNotNull('statistic_import_id')
            ->where('status', 'published')
            ->orderBy('year')
            ->orderBy('display_order')
            ->get();

        abort_if($datasets->isEmpty(), 404);

        $groups = $datasets->groupBy(fn (StatisticDataset $dataset): string => $this->groupKey($dataset));
        $datasetOptions = $groups
            ->map(fn (Collection $items, string $value): array => [
                'value' => $value,
                'label' => $this->datasetLabel($items->first()),
                'years' => $items->pluck('year')->map(fn ($year): int => (int) $year)->unique()->sort()->values()->all(),
            ])
            ->values();

        $requestedDataset = (string) ($filters['dataset'] ?? '');
        $selectedDataset = $groups->has($requestedDataset)
            ? $requestedDataset
            : (string) $groups->keys()->first();
        $selectedGroup = $groups->get($selectedDataset)
            ->sortBy([['year', 'asc'], ['display_order', 'asc']])
            ->groupBy(fn (StatisticDataset $dataset): int => (int) $dataset->year)
            ->map(fn (Collection $yearlyDatasets): StatisticDataset => $yearlyDatasets->last())
            ->values();

        $allHistory = $selectedGroup
            ->map(fn (StatisticDataset $dataset): array => $this->datasetRow($category, $dataset))
            ->values();
        $latest = $allHistory->last();
        $years = $allHistory->pluck('year')->map(fn ($year): int => (int) $year)->all();
        $minimumYear = $years[0];
        $maximumYear = (int) end($years);
        $defaultFrom = max($minimumYear, $maximumYear - 4);
        $requestedFrom = isset($filters['from_year']) ? (int) $filters['from_year'] : null;
        $requestedTo = isset($filters['to_year']) ? (int) $filters['to_year'] : null;
        $from = in_array($requestedFrom, $years, true) ? $requestedFrom : $defaultFrom;
        $to = in_array($requestedTo, $years, true) ? $requestedTo : $maximumYear;

        if ($from > $to) {
            [$from, $to] = [$defaultFrom, $maximumYear];
        }

        $history = $allHistory
            ->filter(fn (array $row): bool => $row['year'] >= $from && $row['year'] <= $to)
            ->values();

        if (($filters['sort'] ?? 'asc') === 'desc') {
            $history = $history->reverse()->values();
        }

        $latestDataset = $selectedGroup->last();
        $itemCount = count($latest['items']);
        $usesPercentage = collect($latest['items'])->contains(fn (array $item): bool => $item['unit'] === '%');
        $chart = $itemCount >= 2 && $itemCount <= 8 ? 'pie' : 'bar';
        $units = collect($latest['items'])->pluck('unit')->unique()->values();
        $unit = $units->count() === 1 ? (string) $units->first() : 'data';
        $indicatorLabel = $this->datasetLabel($latestDataset);

        return [
            'context' => 'imported-'.$category->slug.'-'.$selectedDataset,
            'page' => [
                'title' => 'Statistik '.$category->name,
                'current_title' => $indicatorLabel,
                'trend_title' => $indicatorLabel,
                'description' => $latestDataset->description
                    ?: 'Komposisi dan perkembangan '.$category->name.' berdasarkan data yang telah dipublikasikan.',
                'chart' => $chart,
                'unit' => $unit,
                'decimals' => $usesPercentage ? 2 : 0,
                'show_total' => $chart === 'pie' && ! $usesPercentage && $unit !== 'data',
            ],
            'latest' => $latest,
            'history' => $history->all(),
            'allHistory' => $allHistory->all(),
            'availableYears' => $years,
            'defaultRange' => ['from' => $from, 'to' => $to],
            'tableSort' => $filters['sort'] ?? 'asc',
            'datasetOptions' => $datasetOptions->all(),
            'selectedDataset' => $selectedDataset,
            'latestDataset' => $latestDataset,
            'categoryName' => $category->name,
        ];
    }

    private function groupKey(StatisticDataset $dataset): string
    {
        $identity = $dataset->family && $dataset->table_number
            ? $dataset->family.'-'.$dataset->table_number
            : (string) ($dataset->short_title ?: $dataset->title);

        return Str::slug($identity) ?: 'dataset-'.$dataset->id;
    }

    private function datasetLabel(StatisticDataset $dataset): string
    {
        $label = trim((string) ($dataset->short_title ?: $dataset->title));

        return mb_strtoupper($label) === $label
            ? Str::title(mb_strtolower($label))
            : $label;
    }

    /**
     * @return array{year: int, source: string, updated_at: ?string, items: array<int, array<string, mixed>>, total: float}
     */
    private function datasetRow(StatisticCategory $category, StatisticDataset $dataset): array
    {
        $columns = collect($dataset->columns_json ?? []);
        $integerColumns = $columns->where('type', 'integer')->values();
        $numericColumns = $integerColumns->isNotEmpty()
            ? $integerColumns
            : $columns->where('type', 'percentage')->values();

        $items = $numericColumns->count() === 1 && $dataset->rows->isNotEmpty()
            ? $this->rowItems($category, $dataset, $numericColumns->first())
            : $this->columnItems($category, $dataset, $numericColumns);

        if ($items->isEmpty()) {
            $items = collect([[
                'label' => 'Jumlah Baris Data',
                'value' => (float) $dataset->rows->count(),
                'unit' => 'baris',
            ]]);
        }

        if ($items->count() >= 3 && $this->isGrandTotal($items->first(), $items->slice(1))) {
            $items = $items->slice(1)->values();
        }

        return [
            'year' => (int) $dataset->year,
            'source' => data_get(
                $dataset->source_metadata_json,
                'primary_file',
                $dataset->source ?: 'Pemerintah Desa Sukomulyo',
            ),
            'updated_at' => $dataset->updated_at?->toAtomString(),
            'items' => $items->all(),
            'total' => (float) $items->sum('value'),
        ];
    }

    private function rowItems(
        StatisticCategory $category,
        StatisticDataset $dataset,
        array $column,
    ): Collection {
        $key = (string) $column['key'];
        $unit = $this->unit($category, $dataset, $column);

        return $dataset->rows
            ->filter(fn ($row): bool => is_numeric(data_get($row->values_json, $key)))
            ->map(fn ($row): array => [
                'label' => $row->area_name ?: $row->area_code ?: 'Wilayah '.($row->display_order + 1),
                'value' => (float) data_get($row->values_json, $key),
                'unit' => $unit,
            ])
            ->values();
    }

    private function columnItems(
        StatisticCategory $category,
        StatisticDataset $dataset,
        Collection $columns,
    ): Collection {
        $usedLabels = [];

        return $columns->map(function (array $column) use ($category, $dataset, &$usedLabels): array {
            $key = (string) $column['key'];
            $value = data_get($dataset->totals_json, $key);

            if (! is_numeric($value)) {
                $value = $dataset->rows->sum(
                    fn ($row): float => is_numeric(data_get($row->values_json, $key))
                        ? (float) data_get($row->values_json, $key)
                        : 0,
                );
            }

            $label = $this->columnLabel($column);
            if (in_array($label, $usedLabels, true)) {
                $label = $this->readableLabel((string) ($column['label'] ?? $key));
            }
            if (in_array($label, $usedLabels, true)) {
                $label .= ' '.(count($usedLabels) + 1);
            }
            $usedLabels[] = $label;

            return [
                'label' => $label,
                'value' => (float) $value,
                'unit' => $this->unit($category, $dataset, $column),
            ];
        })->values();
    }

    private function columnLabel(array $column): string
    {
        $path = collect($column['header_path'] ?? [])
            ->map(fn ($part): string => trim((string) $part))
            ->filter()
            ->values();

        while ($path->isNotEmpty() && in_array(mb_strtoupper((string) $path->last()), ['JUMLAH', '%', 'PERSEN', 'PERSENTASE'], true)) {
            $path->pop();
        }

        return $this->readableLabel((string) ($path->last() ?: ($column['label'] ?? $column['key'] ?? 'Data')));
    }

    private function readableLabel(string $label): string
    {
        $label = trim($label);

        return mb_strtoupper($label) === $label
            ? Str::title(mb_strtolower($label))
            : $label;
    }

    private function unit(StatisticCategory $category, StatisticDataset $dataset, array $column): string
    {
        if (($column['type'] ?? null) === 'percentage' || ($column['unit'] ?? null) === 'percent') {
            return '%';
        }

        if (! empty($column['unit'])) {
            return (string) $column['unit'];
        }

        if ($dataset->unit && $dataset->unit !== 'data') {
            return $dataset->unit;
        }

        return match ($category->slug) {
            'keluarga' => 'KK',
            'keluarga-berencana' => 'PUS',
            'kependudukan', 'pendidikan', 'pekerjaan', 'kesehatan', 'perlindungan-sosial', 'lainnya' => 'jiwa',
            default => 'data',
        };
    }

    private function isGrandTotal(array $first, Collection $remaining): bool
    {
        if (! str_starts_with(mb_strtoupper((string) $first['label']), 'JUMLAH ')) {
            return false;
        }

        $total = (float) $first['value'];
        $parts = (float) $remaining->sum('value');
        $tolerance = max(1, abs($total) * .01);

        return abs($total - $parts) <= $tolerance;
    }
}
