<?php

namespace App\Http\Controllers\Admin;

use App\Models\Household;
use App\Models\Resident;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class HouseholdController extends PopulationController
{
    public function __construct(private ActivityLogger $logger) {}

    public function index(Request $request): View
    {
        $query = Household::with(['head', 'area', 'members' => fn ($builder) => $builder->where('status', 'active')->select(['id', 'household_id', 'family_id'])])
            ->withCount(['members' => fn ($builder) => $builder->where('status', 'active')]);
        if ($search = trim((string) $request->query('q'))) {
            $query->where(fn ($builder) => $builder
                ->where('household_number', 'like', "%{$search}%")
                ->orWhereHas('head', fn ($head) => $head->where('name', 'like', "%{$search}%")->orWhere('nik', 'like', "%{$search}%")));
        }

        return view('admin.population.households.index', ['households' => $query->latest('id')->paginate(20)->withQueryString()]);
    }

    public function create(): View
    {
        return view('admin.population.households.form', $this->formData(new Household));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $household = DB::transaction(function () use ($data) {
            $area = $this->resolveArea($data);
            $household = Household::create(array_merge($this->householdData($data), ['area_id' => $area?->id]));
            $this->syncHead($household, null);
            $this->logger->log('created', 'rumah_tangga', $household);

            return $household;
        });

        return redirect()->route('admin.population.households.edit', $household)->with('success', 'Rumah tangga berhasil ditambahkan.');
    }

    public function edit(Household $household): View
    {
        $household->load(['area', 'members']);

        return view('admin.population.households.form', $this->formData($household));
    }

    public function update(Request $request, Household $household): RedirectResponse
    {
        $data = $this->validated($request, $household);
        DB::transaction(function () use ($data, $household) {
            $oldHead = $household->head_resident_id;
            $area = $this->resolveArea($data);
            $household->update(array_merge($this->householdData($data), ['area_id' => $area?->id]));
            $this->syncHead($household, $oldHead);
            $this->logger->log('updated', 'rumah_tangga', $household);
        });

        return back()->with('success', 'Rumah tangga berhasil diperbarui.');
    }

    public function destroy(Household $household): RedirectResponse
    {
        if ($household->members()->exists()) {
            return back()->withErrors(['household' => 'Rumah tangga masih memiliki anggota. Pindahkan anggota terlebih dahulu.']);
        }
        $this->logger->log('archived', 'rumah_tangga', $household);
        $household->delete();

        return back()->with('success', 'Rumah tangga diarsipkan.');
    }

    private function validated(Request $request, ?Household $household = null): array
    {
        return $request->validate(array_merge([
            'household_number' => ['required', 'regex:/^(?=.*\d)[A-Za-z0-9-]{1,30}$/', Rule::unique('households')->ignore($household?->id)],
            'head_resident_id' => ['required', 'exists:residents,id', Rule::unique('households')->ignore($household?->id)],
            'address' => ['nullable', 'string', 'max:255'],
            'social_class' => ['nullable', 'string', 'max:50'],
            'is_dtks_registered' => ['nullable', 'boolean'],
            'dtks_reference' => ['nullable', 'string', 'max:30'],
            'registered_at' => ['nullable', 'date'],
            'is_active' => ['nullable', 'boolean'],
        ], $this->areaRules()));
    }

    private function householdData(array $data): array
    {
        return collect($data)->only([
            'household_number', 'head_resident_id', 'address', 'social_class', 'dtks_reference', 'registered_at',
        ])->merge([
            'is_dtks_registered' => (bool) ($data['is_dtks_registered'] ?? false),
            'is_active' => (bool) ($data['is_active'] ?? false),
        ])->all();
    }

    private function syncHead(Household $household, ?int $oldHead): void
    {
        if ($oldHead && $oldHead !== $household->head_resident_id) {
            Resident::whereKey($oldHead)->where('household_id', $household->id)->update(['household_relationship' => 'Anggota Rumah Tangga']);
        }
        Resident::whereKey($household->head_resident_id)->update([
            'household_id' => $household->id,
            'area_id' => $household->area_id,
            'household_relationship' => 'Kepala Rumah Tangga',
        ]);
    }

    private function formData(Household $household): array
    {
        return [
            'household' => $household,
            'residents' => Resident::where('status', 'active')->orderBy('name')->get(),
        ];
    }
}
