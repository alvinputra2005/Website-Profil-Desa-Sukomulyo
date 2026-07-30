<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BulkDeleteStatisticDatasetsRequest;
use App\Models\StatisticDataset;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class BulkDeleteStatisticDatasetController extends Controller
{
    public function __invoke(
        BulkDeleteStatisticDatasetsRequest $request,
        ActivityLogger $logger,
    ): RedirectResponse {
        $datasets = StatisticDataset::query()
            ->whereKey($request->validated('ids'))
            ->get();

        foreach ($datasets as $dataset) {
            $this->authorize('delete', $dataset);
        }

        DB::transaction(function () use ($datasets, $logger): void {
            $datasets->each(function (StatisticDataset $dataset) use ($logger): void {
                $logger->log('deleted', 'statistics', $dataset, $dataset->toArray());
                $dataset->delete();
            });
        });

        return redirect()
            ->route('admin.resources.index', 'statistics')
            ->with('success', $datasets->count().' dataset statistik berhasil dihapus.');
    }
}
