<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Statistics\ImportNormalizedStatisticsAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProcessStatisticImportRequest;
use App\Http\Requests\Admin\StoreStatisticImportRequest;
use App\Models\StatisticCategory;
use App\Models\StatisticImport;
use App\Services\ActivityLogger;
use App\Services\Statistics\NormalizedStatisticsValidator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class StatisticImportController extends Controller
{
    public function create(): View
    {
        $this->authorize('manage-data');

        return view('admin.statistics.import', [
            'recentImports' => StatisticImport::query()
                ->with('importer')
                ->whereNotNull('public_id')
                ->latest()
                ->limit(10)
                ->get(),
        ]);
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
