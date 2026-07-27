<?php

namespace App\Http\Controllers\Admin\Officials;

use App\Http\Controllers\Controller;
use App\Models\Official;
use App\Services\Officials\OfficialCsvExporter;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OfficialExportController extends Controller
{
    public function print(Request $request): View
    {
        $this->authorize('viewAny', Official::class);
        $query = Official::with('photo')->orderBy('display_order')->orderBy('name');
        if ($request->query('status') !== 'all') {
            $query->where('is_active', true);
        }

        return view('admin.officials.print', ['officials' => $query->get()]);
    }

    public function csv(OfficialCsvExporter $exporter): StreamedResponse
    {
        $this->authorize('viewAny', Official::class);

        return $exporter->download();
    }
}
