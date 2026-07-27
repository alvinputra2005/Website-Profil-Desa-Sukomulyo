<?php

namespace App\Http\Controllers\Admin\Village;

use App\Actions\Village\UpdateVillageSectionAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateVillageContentRequest;
use App\Queries\Village\VillageContentQuery;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class VillageSectionController extends Controller
{
    public function edit(VillageContentQuery $query, string $page): View
    {
        return $query->edit($page);
    }

    public function update(
        UpdateVillageContentRequest $request,
        UpdateVillageSectionAction $action,
        string $page,
    ): RedirectResponse {
        $action->execute($request, $page);

        return back()->with('success', 'Konten berhasil diperbarui.');
    }
}
