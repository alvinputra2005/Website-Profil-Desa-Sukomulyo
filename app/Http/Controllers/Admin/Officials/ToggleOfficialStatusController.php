<?php

namespace App\Http\Controllers\Admin\Officials;

use App\Http\Controllers\Controller;
use App\Models\Official;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;

class ToggleOfficialStatusController extends Controller
{
    public function __invoke(Official $official, ActivityLogger $logger): RedirectResponse
    {
        $this->authorize('update', $official);
        $old = $official->toArray();
        $official->update(['is_active' => ! $official->is_active]);
        $logger->log('status_changed', 'perangkat-desa', $official, $old, $official->fresh()->toArray());

        return back()->with('success', 'Status perangkat desa berhasil diubah.');
    }
}
