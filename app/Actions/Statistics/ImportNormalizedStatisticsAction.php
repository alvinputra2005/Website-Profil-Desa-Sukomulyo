<?php

namespace App\Actions\Statistics;

use App\Models\StatisticCategory;
use App\Models\StatisticDataset;
use App\Models\StatisticImport;
use App\Services\Statistics\NormalizedStatisticsValidator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

final class ImportNormalizedStatisticsAction
{
    public function __construct(private NormalizedStatisticsValidator $validator) {}

    /**
     * @param  array<int, array<string, mixed>>  $selections
     * @return array{datasets: int, rows: int, reviewed: int}
     */
    public function execute(StatisticImport $import, array $selections, int $userId): array
    {
        $import->update([
            'status' => 'processing',
            'started_at' => now(),
            'completed_at' => null,
        ]);

        try {
            $validation = $this->validator->validate(
                Storage::disk('local')->path($import->file_path),
            );

            if (! $validation['is_valid']) {
                throw ValidationException::withMessages([
                    'statistics_file' => collect($validation['errors'])->pluck('message')->all(),
                ]);
            }

            $selected = collect($selections)
                ->filter(fn (array $selection): bool => (bool) ($selection['selected'] ?? false))
                ->keyBy(fn (array $selection): string => (string) $selection['dataset_id']);

            if ($selected->isEmpty()) {
                throw ValidationException::withMessages([
                    'datasets' => 'Pilih minimal satu dataset untuk diimpor.',
                ]);
            }

            $categories = StatisticCategory::query()
                ->where('is_active', true)
                ->get()
                ->keyBy('slug');

            $warningsByDataset = collect($validation['warnings'])->groupBy('dataset_id');

            return DB::transaction(function () use (
                $import,
                $userId,
                $validation,
                $selected,
                $categories,
                $warningsByDataset,
            ): array {
                $datasetCount = 0;
                $rowCount = 0;
                $reviewCount = 0;

                foreach ($validation['data']['datasets'] as $order => $datasetData) {
                    $datasetId = (string) $datasetData['dataset_id'];
                    $selection = $selected->get($datasetId);

                    if (! $selection) {
                        continue;
                    }

                    $categorySlug = (string) ($selection['category_slug'] ?? '');
                    $category = $categories->get($categorySlug);

                    if (! $category) {
                        throw ValidationException::withMessages([
                            'datasets' => "Kategori untuk dataset {$datasetId} tidak valid.",
                        ]);
                    }

                    $datasetWarnings = $warningsByDataset->get($datasetId, collect())->values()->all();
                    $requiresReview = $datasetWarnings !== [];
                    $status = $requiresReview
                        ? 'needs_review'
                        : (string) ($selection['status'] ?? 'draft');
                    $title = trim((string) $datasetData['title']);
                    $shortTitle = trim((string) ($selection['short_title'] ?? '')) ?: $title;
                    $period = (string) $datasetData['period'];

                    $dataset = StatisticDataset::withTrashed()
                        ->firstOrNew(['dataset_key' => $datasetId]);

                    if ($dataset->trashed()) {
                        $dataset->restore();
                    }

                    if (! $dataset->exists) {
                        $dataset->created_by = $userId;
                        $dataset->slug = $this->uniqueSlug($shortTitle, $period, $datasetId);
                    }

                    $dataset->fill([
                        'statistic_import_id' => $import->id,
                        'statistic_category_id' => $category->id,
                        'category' => $category->slug,
                        'family' => (string) $datasetData['family'],
                        'table_number' => (string) $datasetData['table_number'],
                        'period' => $period,
                        'title' => $title,
                        'short_title' => Str::limit($shortTitle, 255, ''),
                        'description' => "Dataset statistik {$category->name} periode {$period}.",
                        'year' => preg_match('/^\d{4}$/', $period) ? (int) $period : now()->year,
                        'unit' => 'data',
                        'visualization_type' => 'table',
                        'region_type' => $datasetData['region_type'] ?? null,
                        'columns_json' => $datasetData['columns'],
                        'totals_json' => $datasetData['totals'] ?? null,
                        'source' => data_get($datasetData, 'source.primary_file', $import->original_filename),
                        'source_metadata_json' => $datasetData['source'],
                        'visualization_config_json' => ['type' => 'table'],
                        'status' => $status,
                        'requires_manual_review' => $requiresReview,
                        'display_order' => $order + 1,
                    ]);
                    $dataset->save();

                    $dataset->rows()->delete();

                    foreach ($datasetData['rows'] as $rowOrder => $row) {
                        $dataset->rows()->create([
                            'area_code' => isset($row['area_code']) ? (string) $row['area_code'] : null,
                            'area_name' => isset($row['area_name']) ? (string) $row['area_name'] : null,
                            'values_json' => collect($row)
                                ->except(['area_code', 'area_name'])
                                ->all(),
                            'display_order' => $rowOrder + 1,
                        ]);
                        $rowCount++;
                    }

                    $datasetCount++;
                    $reviewCount += $requiresReview ? 1 : 0;
                }

                $import->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                    'errors_json' => [],
                ]);

                return [
                    'datasets' => $datasetCount,
                    'rows' => $rowCount,
                    'reviewed' => $reviewCount,
                ];
            });
        } catch (Throwable $exception) {
            $messages = $exception instanceof ValidationException
                ? collect($exception->errors())->flatten()->values()->all()
                : [$exception->getMessage()];

            $import->update([
                'status' => 'failed',
                'errors_json' => collect($messages)
                    ->map(fn (string $message): array => [
                        'dataset_id' => null,
                        'type' => 'import_failed',
                        'message' => $message,
                    ])
                    ->all(),
                'completed_at' => now(),
            ]);

            throw $exception;
        }
    }

    private function uniqueSlug(string $shortTitle, string $period, string $datasetId): string
    {
        $base = Str::slug($shortTitle.'-'.$period) ?: Str::slug($datasetId);
        $slug = $base;
        $suffix = 2;

        while (StatisticDataset::withTrashed()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }
}
