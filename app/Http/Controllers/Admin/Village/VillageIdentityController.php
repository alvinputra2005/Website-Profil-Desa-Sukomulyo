<?php

namespace App\Http\Controllers\Admin\Village;

use App\Actions\Village\UpdateVillageIdentityAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateVillageContentRequest;
use App\Queries\Village\VillageContentQuery;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class VillageIdentityController extends Controller
{
    public function show(VillageContentQuery $query): View
    {
        return $query->showProfile();
    }

    public function edit(VillageContentQuery $query): View
    {
        return $query->editProfile();
    }

    public function update(
        UpdateVillageContentRequest $request,
        UpdateVillageIdentityAction $action,
    ): RedirectResponse {
        $action->execute($request);

        return redirect()->route('admin.village-content.profile')
            ->with('success', 'Identitas Desa berhasil diperbarui.');
    }
}
