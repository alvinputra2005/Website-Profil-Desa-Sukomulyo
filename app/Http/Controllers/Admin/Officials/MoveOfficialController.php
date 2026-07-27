<?php

namespace App\Http\Controllers\Admin\Officials;

use App\Actions\Officials\MoveOfficialAction;
use App\Http\Controllers\Controller;
use App\Models\Official;
use Illuminate\Http\RedirectResponse;

class MoveOfficialController extends Controller
{
    public function __invoke(
        Official $official,
        string $direction,
        MoveOfficialAction $action,
    ): RedirectResponse {
        $this->authorize('update', $official);
        abort_unless(in_array($direction, ['up', 'down'], true), 404);
        $action->execute($official, $direction);

        return back()->with('success', 'Urutan perangkat desa berhasil diperbarui.');
    }
}
