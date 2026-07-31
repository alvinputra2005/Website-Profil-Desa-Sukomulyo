<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Statistics\PopulationStatisticAggregator;
use App\Services\Statistics\PopulationStatisticIndicatorService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PopulationStatisticController extends Controller
{
    public function show(
        Request $request,
        PopulationStatisticIndicatorService $indicatorService,
        PopulationStatisticAggregator $aggregator,
    ): View {
        $this->authorize('manage-data');

        $indicators = $indicatorService->availableIndicators();
        abort_if($indicators->isEmpty(), 404, 'Belum ada indikator statistik warga yang tersedia.');

        $selectedKey = (string) $request->query('indicator', $indicators->first()->key);
        $indicator = $indicators->firstWhere('key', $selectedKey) ?? $indicators->first();

        return view('admin.statistics.population', [
            'indicators' => $indicators,
            'selectedIndicator' => $indicator,
            'result' => $aggregator->aggregate($indicator),
        ]);
    }
}
