<?php

namespace App\Http\Controllers\Admin\Population\Residents;

use App\Actions\Population\CreateResidentAction;
use App\Actions\Population\DeleteResidentAction;
use App\Actions\Population\UpdateResidentAction;
use App\Http\Controllers\Admin\PopulationController;
use App\Http\Requests\Admin\SaveResidentRequest;
use App\Models\FamilyCard;
use App\Models\PopulationArea;
use App\Models\Resident;
use App\Queries\Population\ResidentFormQuery;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ResidentController extends PopulationController
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Resident::class);
        $query = Resident::with(['family.head', 'area']);
        if ($search = trim((string) $request->query('q'))) {
            $query->where(fn ($builder) => $builder
                ->where('name', 'like', "%{$search}%")
                ->orWhere('nik', 'like', "%{$search}%"));
        }
        foreach (['status', 'sex', 'area_id', 'family_id'] as $filter) {
            if ($request->filled($filter)) {
                $query->where($filter, $request->query($filter));
            }
        }

        return view('admin.population.residents.index', [
            'residents' => $query->orderBy('name')->paginate(20)->withQueryString(),
            'areas' => PopulationArea::orderBy('hamlet')->orderBy('rw')->orderBy('rt')->get(),
            'families' => FamilyCard::orderBy('family_card_number')->get(),
        ]);
    }

    public function create(ResidentFormQuery $form): View
    {
        $this->authorize('create', Resident::class);

        return view('admin.population.residents.form', $form->data(new Resident));
    }

    public function store(SaveResidentRequest $request, CreateResidentAction $action): RedirectResponse
    {
        $this->authorize('create', Resident::class);
        $resident = $action->execute($request->validated());

        return redirect()->route('admin.population.residents.show', $resident)
            ->with('success', 'Data penduduk berhasil ditambahkan.');
    }

    public function show(Resident $resident): View
    {
        $this->authorize('view', $resident);
        $resident->load(['area', 'family.head', 'events.recorder', 'groupMemberships.group']);

        return view('admin.population.residents.show', compact('resident'));
    }

    public function edit(Resident $resident, ResidentFormQuery $form): View
    {
        $this->authorize('update', $resident);

        return view('admin.population.residents.form', $form->data($resident));
    }

    public function update(SaveResidentRequest $request, Resident $resident, UpdateResidentAction $action): RedirectResponse
    {
        $this->authorize('update', $resident);
        $action->execute($resident, $request->validated());

        return back()->with('success', 'Data penduduk berhasil diperbarui.');
    }

    public function destroy(Resident $resident, DeleteResidentAction $action): RedirectResponse
    {
        $this->authorize('delete', $resident);
        $action->execute($resident);

        return redirect()->route('admin.population.residents.index')->with('success', 'Data penduduk diarsipkan.');
    }
}
