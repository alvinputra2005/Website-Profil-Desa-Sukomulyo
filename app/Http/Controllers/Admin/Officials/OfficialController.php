<?php

namespace App\Http\Controllers\Admin\Officials;

use App\Actions\Officials\CreateOfficialAction;
use App\Actions\Officials\DeleteOfficialAction;
use App\Actions\Officials\UpdateOfficialAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SaveOfficialRequest;
use App\Models\Official;
use App\Queries\Officials\OfficialFormQuery;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OfficialController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Official::class);
        $query = Official::with(['photo', 'resident', 'superior']);

        if ($search = trim((string) $request->query('q'))) {
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('position', 'like', "%{$search}%")
                    ->orWhere('nik', 'like', "%{$search}%")
                    ->orWhere('nip', 'like', "%{$search}%")
                    ->orWhere('village_employee_number', 'like', "%{$search}%")
                    ->orWhere('id_card_tag', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->query('status') === 'active');
        }
        if ($request->filled('position')) {
            $query->where('position', $request->query('position'));
        }

        return view('admin.officials.index', [
            'officials' => $query->orderBy('display_order')->orderBy('name')->paginate(20)->withQueryString(),
            'positions' => Official::query()->whereNotNull('position')->distinct()->orderBy('position')->pluck('position'),
        ]);
    }

    public function create(OfficialFormQuery $form): View
    {
        $this->authorize('create', Official::class);

        return view('admin.officials.form', $form->data(new Official([
            'display_order' => (int) Official::max('display_order') + 1,
            'is_active' => true,
            'organization_color' => '#526b42',
            'registered_at' => now()->toDateString(),
        ])));
    }

    public function store(SaveOfficialRequest $request, CreateOfficialAction $action): RedirectResponse
    {
        $this->authorize('create', Official::class);
        $data = $request->validated();
        $data['is_active'] = array_key_exists('is_active', $data) ? $data['is_active'] : true;
        $official = $action->execute($request, $data);

        return redirect()->route('admin.officials.edit', $official)
            ->with('success', 'Data perangkat desa berhasil ditambahkan.');
    }

    public function edit(Official $official, OfficialFormQuery $form): View
    {
        $this->authorize('update', $official);
        $official->load(['photo', 'resident']);

        return view('admin.officials.form', $form->data($official));
    }

    public function update(
        SaveOfficialRequest $request,
        Official $official,
        UpdateOfficialAction $action,
    ): RedirectResponse {
        $this->authorize('update', $official);
        $action->execute($request, $official, $request->validated());

        return back()->with('success', 'Data perangkat desa berhasil diperbarui.');
    }

    public function destroy(Official $official, DeleteOfficialAction $action): RedirectResponse
    {
        $this->authorize('delete', $official);
        $action->execute($official);

        return redirect()->route('admin.officials.index')->with('success', 'Data perangkat desa berhasil dihapus.');
    }
}
