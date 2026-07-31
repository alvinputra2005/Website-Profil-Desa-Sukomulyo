<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Statistics\ImportNormalizedStatisticsAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProcessStatisticImportRequest;
use App\Http\Requests\Admin\StoreStatisticImportRequest;
use App\Http\Requests\Admin\StoreStatisticExcelImportRequest;
use App\Models\StatisticCategory;
use App\Models\StatisticDataset;
use App\Models\StatisticImport;
use App\Services\ActivityLogger;
use App\Services\Statistics\NormalizedStatisticsValidator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class StatisticImportController extends Controller
{
    public function create(): View
    {
        $this->authorize('manage-data');

        return view('admin.statistics.import', [
            'categories' => StatisticCategory::query()->where('is_active', true)->orderBy('display_order')->orderBy('name')->get(),
            'recentImports' => StatisticImport::query()
                ->with('importer')
                ->whereNotNull('public_id')
                ->latest()
                ->limit(10)
                ->get(),
        ]);
    }

    public function storeExcel(StoreStatisticExcelImportRequest $request, ActivityLogger $logger): RedirectResponse
    {
        $category = StatisticCategory::query()->findOrFail($request->integer('category_id'));
        $sheet = IOFactory::load($request->file('file')->getRealPath())->getActiveSheet();
        $rawRows = $sheet->toArray(null, true, true, false);
        $rawRows = array_values(array_filter($rawRows, fn (array $row): bool => collect($row)->contains(fn ($value): bool => filled($value))));
        if (count($rawRows) < 2) {
            throw ValidationException::withMessages(['file' => 'File harus memiliki baris judul kolom dan minimal satu baris data.']);
        }

        $labels = array_map(fn ($label, $index): string => trim((string) ($label ?: 'Kolom '.($index + 1))), $rawRows[0], array_keys($rawRows[0]));
        $columns = [];
        $usedKeys = [];
        foreach ($labels as $index => $label) {
            $key = Str::snake(Str::ascii($label));
            if (str_contains($key, 'kode') && (str_contains($key, 'wilayah') || str_contains($key, 'area'))) $key = 'area_code';
            if (str_contains($key, 'nama') && (str_contains($key, 'wilayah') || str_contains($key, 'area') || str_contains($key, 'dusun'))) $key = 'area_name';
            $key = $key ?: 'kolom_'.($index + 1);
            $base = $key; $suffix = 2;
            while (in_array($key, $usedKeys, true)) $key = $base.'_'.($suffix++);
            $usedKeys[] = $key;
            $values = array_column(array_slice($rawRows, 1), $index);
            $filledValues = collect($values)->filter(fn ($value): bool => filled($value));
            $numeric = $filledValues->isNotEmpty() && $filledValues->every(fn ($value): bool => is_numeric(str_replace(',', '.', (string) $value)));
            $decimal = collect($values)->contains(fn ($value): bool => str_contains((string) $value, '.') || str_contains((string) $value, ','));
            $columns[] = ['key' => $key, 'label' => $label, 'type' => $numeric ? ($decimal ? 'percentage' : 'integer') : 'string'];
        }

        $dataset = DB::transaction(function () use ($request, $category, $columns, $rawRows): StatisticDataset {
            $period = trim($request->string('period')->toString());
            $title = trim($request->string('title')->toString());
            $slugBase = Str::slug($title.'-'.$period) ?: 'dataset-statistik';
            $slug = $slugBase; $suffix = 2;
            while (StatisticDataset::withTrashed()->where('slug', $slug)->exists()) $slug = $slugBase.'-'.($suffix++);
            $dataset = StatisticDataset::query()->create([
                'statistic_category_id' => $category->id, 'category' => $category->slug,
                'period' => $period, 'title' => $title,
                'short_title' => trim($request->input('short_title') ?: $title), 'slug' => $slug,
                'description' => null, 'year' => preg_match('/^\d{4}$/', $period) ? (int) $period : now()->year,
                'unit' => trim($request->string('unit')->toString()), 'visualization_type' => 'table',
                'source' => $request->input('source'), 'status' => $request->string('status')->toString(),
                'display_order' => ((int) $category->datasets()->max('display_order')) + 1,
                'created_by' => $request->user()->id, 'columns_json' => $columns,
                'visualization_config_json' => ['type' => 'table'],
                'requires_manual_review' => $request->string('status')->toString() === 'needs_review',
            ]);
            foreach (array_slice($rawRows, 1) as $order => $rawRow) {
                $values = [];
                $areaCode = null; $areaName = null;
                foreach ($columns as $index => $column) {
                    $value = $rawRow[$index] ?? null;
                    $value = filled($value) ? trim((string) $value) : null;
                    if ($column['key'] === 'area_code') $areaCode = $value;
                    elseif ($column['key'] === 'area_name') $areaName = $value;
                    else $values[$column['key']] = $value === null ? null : ($column['type'] === 'integer' ? (int) $value : ($column['type'] === 'percentage' ? (float) str_replace(',', '.', $value) : $value));
                }
                if ($areaCode === null && $areaName === null && ! collect($values)->contains(fn ($value): bool => $value !== null)) continue;
                $dataset->rows()->create(['area_code' => $areaCode, 'area_name' => $areaName, 'values_json' => $values, 'display_order' => $order + 1]);
            }
            return $dataset;
        });
        $logger->log('imported', 'statistics_excel', $dataset, null, ['rows' => $dataset->rows()->count(), 'filename' => $request->file('file')->getClientOriginalName()]);
        return redirect()->route('admin.statistics.categories.show', [$category->slug, 'data' => 'dataset:'.$dataset->id, 'period' => $dataset->period])->with('success', 'Dataset Excel berhasil diimpor.');
    }

    public function store(
        StoreStatisticImportRequest $request,
        NormalizedStatisticsValidator $validator,
    ): RedirectResponse {
        $file = $request->file('statistics_file');
        $publicId = (string) Str::ulid();
        $fileHash = hash_file('sha256', $file->getRealPath());
        $storedPath = $file->storeAs('statistics/imports', "{$publicId}.json", 'local');

        if (! $storedPath) {
            throw ValidationException::withMessages([
                'statistics_file' => 'File JSON gagal disimpan.',
            ]);
        }

        $attributes = [
            'public_id' => $publicId,
            'original_filename' => $file->getClientOriginalName(),
            'file_path' => $storedPath,
            'file_sha256' => $fileHash,
            'status' => 'validating',
            'imported_by' => $request->user()->id,
            'started_at' => now(),
            'total_rows' => 0,
        ];

        if (Schema::hasColumn('statistic_imports', 'disk')) {
            $attributes += [
                'disk' => 'local',
                'file_hash' => $fileHash,
                'successful_rows' => 0,
                'failed_rows' => 0,
                'uploaded_by' => $request->user()->id,
            ];
        }

        $import = StatisticImport::query()->create($attributes);

        $result = $validator->validate(Storage::disk('local')->path($storedPath));
        $update = [
            'schema_name' => $result['schema_name'],
            'schema_version' => $result['schema_version'],
            'status' => $result['is_valid'] ? 'validated' : 'invalid',
            'total_datasets' => $result['total_datasets'],
            'total_rows' => $result['total_rows'],
            'warnings_json' => $result['warnings'],
            'errors_json' => $result['errors'],
            'completed_at' => $result['is_valid'] ? null : now(),
        ];
        if (Schema::hasColumn('statistic_imports', 'finished_at')) {
            $update['finished_at'] = $result['is_valid'] ? null : now();
        }
        $import->update($update);

        return redirect()->route('admin.statistics.import.preview', $import->public_id);
    }

    public function preview(
        StatisticImport $import,
        NormalizedStatisticsValidator $validator,
    ): View {
        $this->authorize('manage-data');

        $result = $validator->validate(
            Storage::disk('local')->path($import->file_path),
        );

        return view('admin.statistics.preview', [
            'import' => $import,
            'validation' => $result,
            'categories' => StatisticCategory::query()
                ->where('is_active', true)
                ->orderBy('display_order')
                ->get(),
        ]);
    }

    public function processImport(
        ProcessStatisticImportRequest $request,
        StatisticImport $import,
        ImportNormalizedStatisticsAction $action,
        ActivityLogger $logger,
    ): RedirectResponse {
        if (! in_array($import->status, ['validated', 'completed', 'failed'], true)) {
            throw ValidationException::withMessages([
                'statistics_file' => 'Import ini belum lolos validasi.',
            ]);
        }

        $result = $action->execute(
            $import,
            $request->validated('datasets'),
            $request->user()->id,
        );

        $logger->log('imported', 'statistik_normalized', $import, null, $result);

        return redirect()
            ->route('admin.statistics.import.create')
            ->with(
                'success',
                "Import selesai: {$result['datasets']} dataset dan {$result['rows']} baris tersimpan."
                .($result['reviewed'] ? " {$result['reviewed']} dataset perlu ditinjau." : ''),
            );
    }
}
