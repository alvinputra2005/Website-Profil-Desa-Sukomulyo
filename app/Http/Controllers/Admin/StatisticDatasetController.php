<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateStatisticDatasetRequest;
use App\Models\StatisticCategory;
use App\Models\StatisticDataset;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class StatisticDatasetController extends Controller
{
    public function __construct(private ActivityLogger $logger) {}

    public function index(): RedirectResponse
    {
        $this->authorize('viewAny', StatisticDataset::class);

        $category = StatisticCategory::query()
            ->where('is_active', true)
            ->orderBy('display_order')
            ->orderBy('name')
            ->first();

        if (! $category) {
            return redirect()
                ->route('admin.statistics.import.create')
                ->with('warning', 'Belum ada kategori statistik aktif. Jalankan seeder kategori terlebih dahulu.');
        }

        return redirect()->route('admin.statistics.categories.show', $category->slug);
    }

    public function show(Request $request, StatisticCategory $category): View
    {
        $this->authorize('viewAny', StatisticDataset::class);

        $datasets = $category->datasets()
            ->withCount('rows')
            ->orderBy('display_order')
            ->orderBy('title')
            ->orderByDesc('period')
            ->get();

        $types = $datasets
            ->groupBy(fn (StatisticDataset $dataset): string => $this->typeKey($dataset))
            ->map(function (Collection $items, string $key): array {
                /** @var StatisticDataset $dataset */
                $dataset = $items->sortByDesc('period')->first();

                return [
                    'key' => $key,
                    'label' => $dataset->short_title ?: $dataset->title,
                    'count' => $items->count(),
                ];
            })
            ->values();

        $selectedType = (string) $request->query('data', '');
        if (! $types->contains('key', $selectedType)) {
            $selectedType = (string) data_get($types->first(), 'key', '');
        }

        $typeDatasets = $datasets
            ->filter(fn (StatisticDataset $dataset): bool => $this->typeKey($dataset) === $selectedType);
        $periods = $typeDatasets
            ->pluck('period')
            ->filter(fn ($period): bool => filled($period))
            ->unique()
            ->sortDesc()
            ->values();

        $selectedPeriod = (string) $request->query('period', '');
        if (! $periods->contains($selectedPeriod)) {
            $selectedPeriod = (string) ($periods->first() ?? '');
        }

        $allowedStatuses = ['all', 'draft', 'published', 'needs_review', 'archived'];
        $status = (string) $request->query('status', 'all');
        if (! in_array($status, $allowedStatuses, true)) {
            $status = 'all';
        }

        $dataset = $typeDatasets
            ->when($selectedPeriod !== '', fn (Collection $items): Collection => $items->where('period', $selectedPeriod))
            ->when($status !== 'all', fn (Collection $items): Collection => $items->where('status', $status))
            ->sortBy('display_order')
            ->first();

        $dataset?->load('rows');

        return view('admin.statistics.category', [
            'category' => $category,
            'datasets' => $datasets,
            'types' => $types,
            'selectedType' => $selectedType,
            'periods' => $periods,
            'selectedPeriod' => $selectedPeriod,
            'status' => $status,
            'dataset' => $dataset,
            'summary' => [
                'datasets' => $datasets->count(),
                'rows' => $datasets->sum('rows_count'),
                'periods' => $datasets->pluck('period')->filter()->unique()->count(),
                'published' => $datasets->where('status', 'published')->count(),
            ],
        ]);
    }

    public function edit(StatisticCategory $category, StatisticDataset $dataset): View
    {
        $this->ensureDatasetBelongsToCategory($category, $dataset);
        $this->authorize('update', $dataset);
        $dataset->load('rows');

        return view('admin.statistics.edit', compact('category', 'dataset'));
    }

    public function update(
        UpdateStatisticDatasetRequest $request,
        StatisticCategory $category,
        StatisticDataset $dataset,
    ): RedirectResponse {
        $this->ensureDatasetBelongsToCategory($category, $dataset);
        $validated = $request->validated();
        $columns = collect($dataset->columns_json ?? [])->keyBy('key');
        $old = $dataset->toArray();

        DB::transaction(function () use ($dataset, $validated, $columns): void {
            $period = trim((string) $validated['period']);
            $status = (string) $validated['status'];

            $dataset->update([
                'title' => trim((string) $validated['title']),
                'short_title' => filled($validated['short_title'] ?? null)
                    ? trim((string) $validated['short_title'])
                    : trim((string) $validated['title']),
                'description' => $validated['description'] ?? null,
                'period' => $period,
                'year' => preg_match('/^\d{4}$/', $period) ? (int) $period : $dataset->year,
                'unit' => trim((string) $validated['unit']),
                'source' => $validated['source'] ?? null,
                'status' => $status,
                'requires_manual_review' => $status === 'needs_review',
                'totals_json' => $this->normalizedValues($validated['totals'] ?? [], $columns),
            ]);

            foreach ($validated['rows'] as $order => $rowData) {
                $row = $dataset->rows()->findOrFail((int) $rowData['id']);
                $values = $this->normalizedValues($rowData, $columns);

                $row->update([
                    'area_code' => array_key_exists('area_code', $rowData)
                        ? $this->nullableString($rowData['area_code'])
                        : $row->area_code,
                    'area_name' => array_key_exists('area_name', $rowData)
                        ? $this->nullableString($rowData['area_name'])
                        : $row->area_name,
                    'values_json' => collect($values)
                        ->except(['area_code', 'area_name'])
                        ->all(),
                    'display_order' => $order + 1,
                ]);
            }
        });

        $this->logger->log(
            'updated',
            'statistics',
            $dataset,
            $old,
            $dataset->fresh()->toArray(),
        );

        return redirect()
            ->route('admin.statistics.categories.show', [
                'category' => $category->slug,
                'data' => $this->typeKey($dataset),
                'period' => $dataset->period,
            ])
            ->with('success', 'Data statistik berhasil diperbarui.');
    }

    private function ensureDatasetBelongsToCategory(
        StatisticCategory $category,
        StatisticDataset $dataset,
    ): void {
        abort_unless($dataset->statistic_category_id === $category->id, 404);
    }

    private function typeKey(StatisticDataset $dataset): string
    {
        if (filled($dataset->family) && filled($dataset->table_number)) {
            return $dataset->family.':'.$dataset->table_number;
        }

        return 'dataset:'.$dataset->id;
    }

    /**
     * @param  Collection<string, array<string, mixed>>  $columns
     * @return array<string, mixed>
     */
    private function normalizedValues(array $input, Collection $columns): array
    {
        return $columns->mapWithKeys(function (array $column, string $key) use ($input): array {
            $value = $input[$key] ?? null;

            if ($value === '' || $value === null) {
                return [$key => null];
            }

            return [$key => match ($column['type'] ?? 'string') {
                'integer' => (int) $value,
                'percentage' => (float) $value,
                default => trim((string) $value),
            }];
        })->all();
    }

    private function nullableString(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
