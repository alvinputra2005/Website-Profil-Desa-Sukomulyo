<?php

namespace App\Services\Statistics;

use App\Models\PopulationStatisticIndicator;
use App\Services\PopulationStatistics;
use Carbon\Carbon;
use InvalidArgumentException;

final class PopulationStatisticAggregator
{
    public function __construct(
        private readonly PopulationStatisticIndicatorService $indicators,
        private readonly PopulationStatistics $populationStatistics,
    ) {}

    public function aggregate(PopulationStatisticIndicator $indicator): array
    {
        if (! $this->indicators->isAvailable($indicator)) {
            throw new InvalidArgumentException('Indikator statistik tidak tersedia.');
        }

        if ($indicator->key === 'gender') {
            $census = $this->populationStatistics->censusGenderSummary();
            if ($census) {
                return [
                    'indicator' => [
                        'key' => $indicator->key,
                        'label' => $indicator->label,
                        'unit' => $indicator->unit,
                        'chart_type' => $indicator->chart_type,
                    ],
                    'total' => $census['total'],
                    'classified' => $census['total'],
                    'items' => [
                        ['key' => 'L', 'label' => 'Laki-laki', 'value' => $census['male'], 'percentage' => $census['male_percentage']],
                        ['key' => 'P', 'label' => 'Perempuan', 'value' => $census['female'], 'percentage' => $census['female_percentage']],
                    ],
                ];
            }
        }

        $items = match ($indicator->aggregation_type) {
            'categorical', 'boolean' => $this->categorical($indicator),
            'age_range' => $this->ageRange($indicator),
            default => throw new InvalidArgumentException('Jenis agregasi statistik tidak didukung.'),
        };
        $total = collect($items)->sum('value');

        $items = collect($items)->map(function (array $item) use ($total): array {
            $item['percentage'] = $total > 0 ? round($item['value'] / $total * 100, 2) : 0.0;

            return $item;
        })->values()->all();

        return [
            'indicator' => [
                'key' => $indicator->key,
                'label' => $indicator->label,
                'unit' => $indicator->unit,
                'chart_type' => $indicator->chart_type,
            ],
            'total' => $total,
            'classified' => $total,
            'items' => $items,
        ];
    }

    private function categorical(PopulationStatisticIndicator $indicator): array
    {
        $column = $indicator->source_column;
        $labels = data_get($indicator->configuration_json, 'labels', []);
        $includeUnknown = (bool) data_get($indicator->configuration_json, 'include_unknown', false);
        $rows = $this->indicators->sourceQuery($indicator)
            ->selectRaw("{$column} as category, COUNT(*) as total")
            ->whereNotNull($column)
            ->where($column, '<>', '')
            ->groupBy($column)
            ->orderByDesc('total')
            ->get();

        return $rows
            ->filter(fn ($row): bool => $includeUnknown || array_key_exists((string) $row->category, $labels))
            ->map(fn ($row): array => [
                'key' => (string) $row->category,
                'label' => $labels[(string) $row->category] ?? (string) $row->category,
                'value' => (int) $row->total,
            ])
            ->values()
            ->all();
    }

    private function ageRange(PopulationStatisticIndicator $indicator): array
    {
        $column = $indicator->source_column;
        $mode = (string) data_get($indicator->configuration_json, 'source_mode', 'birth_date');
        $ranges = collect(data_get($indicator->configuration_json, 'ranges', []))
            ->filter(fn ($range): bool => is_array($range) && isset($range['key'], $range['label'], $range['min']))
            ->values();
        $counts = $ranges->mapWithKeys(fn (array $range): array => [(string) $range['key'] => 0]);

        $this->indicators->sourceQuery($indicator)
            ->whereNotNull($column)
            ->where($column, '<>', '')
            ->orderBy($column)
            ->pluck($column)
            ->each(function ($value) use ($mode, $ranges, $counts): void {
                try {
                    $age = $mode === 'age_value'
                        ? filter_var($value, FILTER_VALIDATE_INT)
                        : Carbon::parse($value)->age;
                } catch (\Throwable) {
                    return;
                }

                if ($age === false || $age < 0 || $age > 130) {
                    return;
                }

                $range = $ranges->first(fn (array $range): bool => $age >= (int) $range['min']
                    && (($range['max'] ?? null) === null || $age <= (int) $range['max']));
                if ($range) {
                    $counts->put((string) $range['key'], $counts->get((string) $range['key'], 0) + 1);
                }
            });

        return $ranges->map(fn (array $range): array => [
            'key' => (string) $range['key'],
            'label' => (string) $range['label'],
            'value' => (int) $counts->get((string) $range['key'], 0),
        ])->all();
    }
}
