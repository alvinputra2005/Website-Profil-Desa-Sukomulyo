<?php

namespace App\Http\Controllers\Admin\Officials;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BulkDeleteOfficialsRequest;
use App\Models\Official;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class BulkDeleteOfficialController extends Controller
{
    public function __invoke(
        BulkDeleteOfficialsRequest $request,
        ActivityLogger $logger,
    ): RedirectResponse {
        $this->authorize('deleteAny', Official::class);
        $ids = $request->validated('ids');

        DB::transaction(function () use ($ids, $logger): void {
            Official::whereKey($ids)->get()->each(function (Official $official) use ($logger): void {
                $logger->log('deleted', 'perangkat-desa', $official, $official->toArray());
                $official->delete();
            });
        });

        return back()->with('success', count($ids).' data perangkat desa berhasil dihapus.');
    }
}
