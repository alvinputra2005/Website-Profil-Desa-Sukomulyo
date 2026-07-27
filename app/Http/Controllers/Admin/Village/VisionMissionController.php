<?php

namespace App\Http\Controllers\Admin\Village;

use App\Actions\Village\UpdateVisionMissionAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateVillageContentRequest;
use App\Queries\Village\VillageContentQuery;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class VisionMissionController extends Controller
{
    public function edit(VillageContentQuery $query): View
    {
        return $query->edit('vision-mission');
    }

    public function update(
        UpdateVillageContentRequest $request,
        UpdateVisionMissionAction $action,
    ): RedirectResponse {
        $action->execute($request->validated());

        return back()->with('success', 'Konten berhasil diperbarui.');
    }
}
