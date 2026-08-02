<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateStatisticDatasetRequest;
use App\Http\Requests\Admin\StoreStatisticDatasetRequest;
use App\Models\StatisticCategory;
use App\Models\StatisticDataset;
use App\Models\StatisticRow;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Services\Statistics\PopulationStatisticIndicatorService;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\View\View;

class StatisticDatasetController extends Controller
{
    public function __construct(private ActivityLogger $logger) {}

    public function index(PopulationStatisticIndicatorService $indicators): View
    {
        $this->authorize('viewAny', StatisticDataset::class);

        $categories = StatisticCategory::query()
            ->where('is_active', true)
            ->whereHas('datasets', fn ($query) => $query->whereHas('rows'))
            ->orderBy('display_order')
            ->orderBy('name')
            ->get();
        $datasets = StatisticDataset::query()->whereHas('rows');

        return view('admin.statistics.index', [
            'populationIndicatorCount' => $indicators->availableIndicators()->count(),
            'categoryCount' => $categories->count(),
            'datasetCount' => (clone $datasets)->count(),
            'publishedCount' => (clone $datasets)->where('status', 'published')->count(),
        ]);
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

    public function exportCsv(StatisticCategory $category, StatisticDataset $dataset): StreamedResponse
    {
        $this->ensureDatasetBelongsToCategory($category, $dataset);
        $this->authorize('view', $dataset);
        $dataset->load('rows');
        $columns = collect($dataset->columns_json ?? []);
        $filename = Str::slug($dataset->short_title ?: $dataset->title).'-'.($dataset->period ?: $dataset->year).'.csv';

        return response()->streamDownload(function () use ($dataset, $columns): void {
            $output = fopen('php://output', 'wb');
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, $columns->pluck('label')->all(), ';');

            foreach ($dataset->rows as $row) {
                fputcsv($output, $columns->map(function (array $column) use ($row): mixed {
                    return match ($column['key'] ?? '') {
                        'area_code' => $row->area_code,
                        'area_name' => $row->area_name,
                        default => data_get($row->values_json, $column['key'] ?? ''),
                    };
                })->all(), ';');
            }

            if ($dataset->totals_json) {
                fputcsv($output, $columns->map(function (array $column) use ($dataset): mixed {
                    return match ($column['key'] ?? '') {
                        'area_code' => 'JUMLAH TOTAL',
                        'area_name' => '',
                        default => data_get($dataset->totals_json, $column['key'] ?? ''),
                    };
                })->all(), ';');
            }

            fclose($output);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function create(Request $request, StatisticCategory $category): View
    {
        $this->authorize('create', StatisticDataset::class);
        $template = $category->datasets()
            ->with('rows')
            ->find($request->integer('template'));

        $dataset = new StatisticDataset([
            'title' => $template?->title,
            'short_title' => $template?->short_title,
            'description' => $template?->description,
            'period' => '',
            'year' => now()->year,
            'unit' => $template?->unit ?? 'data',
            'source' => $template?->source,
            'status' => 'draft',
            'visualization_type' => $template?->visualization_type ?? 'table',
            'columns_json' => $template?->columns_json ?? [],
            'totals_json' => $template?->totals_json ?? [],
        ]);
        $dataset->setRelation('rows', $template
            ? $template->rows->map(fn (StatisticRow $row): StatisticRow => new StatisticRow([
                'area_code' => $row->area_code,
                'area_name' => $row->area_name,
                'values_json' => $row->values_json ?? [],
            ]))
            : collect());

        return view('admin.statistics.edit', [
            'category' => $category,
            'dataset' => $dataset,
            'isCreate' => true,
            'templateId' => $template?->id,
        ]);
    }

    public function store(StoreStatisticDatasetRequest $request, StatisticCategory $category): RedirectResponse
    {
        $template = $request->template();
        abort_if($template && $template->statistic_category_id !== $category->id, 404);
        $validated = $request->validated();
        $columnDefinitions = $template
            ? ($template->columns_json ?? [])
            : collect($validated['columns'] ?? [])->map(fn (array $column): array => [
                'key' => $column['key'],
                'label' => trim($column['label']),
                'type' => $column['type'],
            ])->values()->all();
        $columns = collect($columnDefinitions)->keyBy('key');

        $dataset = DB::transaction(function () use ($validated, $category, $template, $columns, $columnDefinitions): StatisticDataset {
            $period = trim((string) $validated['period']);
            $dataset = StatisticDataset::query()->create([
                'statistic_category_id' => $category->id,
                'category' => $category->slug,
                'family' => $template?->family,
                'table_number' => $template?->table_number,
                'period' => $period,
                'title' => trim((string) $validated['title']),
                'short_title' => filled($validated['short_title'] ?? null) ? trim((string) $validated['short_title']) : trim((string) $validated['title']),
                'slug' => $this->uniqueSlug((string) $validated['title'], $period),
                'description' => $validated['description'] ?? null,
                'year' => preg_match('/^\d{4}$/', $period) ? (int) $period : now()->year,
                'unit' => trim((string) $validated['unit']),
                'visualization_type' => $template?->visualization_type ?? 'table',
                'source' => $validated['source'] ?? null,
                'status' => $validated['status'],
                'display_order' => $template?->display_order
                    ?? ((int) $category->datasets()->max('display_order')) + 1,
                'created_by' => auth()->id(),
                'columns_json' => $columnDefinitions,
                'totals_json' => $this->normalizedValues($validated['totals'] ?? [], $columns, true),
                'source_metadata_json' => $template?->source_metadata_json,
                'visualization_config_json' => $template?->visualization_config_json ?? ['type' => 'table'],
                'requires_manual_review' => $validated['status'] === 'needs_review',
            ]);

            foreach ($validated['rows'] ?? [] as $order => $rowData) {
                $values = $this->normalizedValues($rowData, $columns);
                $dataset->rows()->create([
                    'area_code' => $this->nullableString($rowData['area_code'] ?? null),
                    'area_name' => $this->nullableString($rowData['area_name'] ?? null),
                    'values_json' => collect($values)->except(['area_code', 'area_name'])->all(),
                    'display_order' => $order + 1,
                ]);
            }

            return $dataset;
        });

        $this->logger->log('created', 'statistics', $dataset, null, $dataset->fresh()->toArray());

        return redirect()
            ->route('admin.statistics.categories.edit', ['category' => $category->slug, 'dataset' => $dataset->slug])
            ->with('success', 'Periode statistik baru berhasil ditambahkan.');
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
                'totals_json' => $this->normalizedValues($validated['totals'] ?? [], $columns, true),
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
    private function normalizedValues(array $input, Collection $columns, bool $skipAreaIdentifiers = false): array
    {
        if ($skipAreaIdentifiers) {
            $columns = $columns->except(['area_code', 'area_name']);
        }

        return $columns
            ->mapWithKeys(function (array $column, string $key) use ($input): array {
                $value = $input[$key] ?? null;

                if ($value === '' || $value === null) {
                    return [$key => null];
                }

                return [$key => match ($column['type'] ?? 'string') {
                    'integer' => (int) $value,
                    'percentage' => (float) $value,
                    default => trim((string) $value),
                }];
            })
            ->all();
    }

    private function nullableString(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function uniqueSlug(string $title, string $period): string
    {
        $base = Str::slug($title.'-'.$period) ?: 'dataset-statistik';
        $slug = $base;
        $suffix = 2;
        while (StatisticDataset::withTrashed()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }
}
