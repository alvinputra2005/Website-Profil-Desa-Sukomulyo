<?php

namespace App\Http\Controllers\Admin;

use App\Models\FamilyCard;
use App\Models\Resident;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class FamilyController extends PopulationController
{
    public function __construct(private ActivityLogger $logger) {}

    public function index(Request $request): View
    {
        $query = FamilyCard::with(['head', 'area'])->withCount(['members' => fn ($builder) => $builder->where('status', 'active')]);
        if ($search = trim((string) $request->query('q'))) {
            $query->where(fn ($builder) => $builder
                ->where('family_card_number', 'like', "%{$search}%")
                ->orWhereHas('head', fn ($head) => $head->where('name', 'like', "%{$search}%")->orWhere('nik', 'like', "%{$search}%")));
        }
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        return view('admin.population.families.index', ['families' => $query->latest('id')->paginate(20)->withQueryString()]);
    }

    public function create(): View
    {
        return view('admin.population.families.form', $this->formData(new FamilyCard));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $family = DB::transaction(function () use ($data) {
            $area = $this->resolveArea($data);
            $family = FamilyCard::create(array_merge($this->familyData($data), ['area_id' => $area?->id]));
            $this->syncHead($family, null);
            $this->logger->log('created', 'keluarga', $family);

            return $family;
        });

        return redirect()->route('admin.population.families.edit', $family)->with('success', 'Data keluarga berhasil ditambahkan.');
    }

    public function edit(FamilyCard $family): View
    {
        $family->load(['area', 'members']);

        return view('admin.population.families.form', $this->formData($family));
    }

    public function update(Request $request, FamilyCard $family): RedirectResponse
    {
        $data = $this->validated($request, $family);
        DB::transaction(function () use ($data, $family) {
            $oldHead = $family->head_resident_id;
            $area = $this->resolveArea($data);
            $family->update(array_merge($this->familyData($data), ['area_id' => $area?->id]));
            $this->syncHead($family, $oldHead);
            $this->logger->log('updated', 'keluarga', $family);
        });

        return back()->with('success', 'Data keluarga berhasil diperbarui.');
    }

    public function destroy(FamilyCard $family): RedirectResponse
    {
        if ($family->members()->exists()) {
            return back()->withErrors(['family' => 'Keluarga masih memiliki anggota. Pindahkan anggota terlebih dahulu.']);
        }
        $this->logger->log('archived', 'keluarga', $family);
        $family->delete();

        return back()->with('success', 'Data keluarga diarsipkan.');
    }

    private function validated(Request $request, ?FamilyCard $family = null): array
    {
        return $request->validate(array_merge([
            'family_card_number' => ['required', 'digits:16', Rule::unique('families')->ignore($family?->id)],
            'head_resident_id' => ['required', 'exists:residents,id', Rule::unique('families')->ignore($family?->id)],
            'address' => ['nullable', 'string', 'max:255'],
            'social_class' => ['nullable', 'string', 'max:50'],
            'registered_at' => ['nullable', 'date'],
            'issued_at' => ['nullable', 'date'],
            'is_active' => ['nullable', 'boolean'],
        ], $this->areaRules()));
    }

    private function familyData(array $data): array
    {
        return collect($data)->only([
            'family_card_number', 'head_resident_id', 'address', 'social_class', 'registered_at', 'issued_at',
        ])->merge(['is_active' => (bool) ($data['is_active'] ?? false)])->all();
    }

    private function syncHead(FamilyCard $family, ?int $oldHead): void
    {
        if ($oldHead && $oldHead !== $family->head_resident_id) {
            Resident::whereKey($oldHead)->where('family_id', $family->id)->update(['family_relationship' => 'Anggota Keluarga']);
        }
        Resident::whereKey($family->head_resident_id)->update([
            'family_id' => $family->id,
            'area_id' => $family->area_id,
            'family_relationship' => 'Kepala Keluarga',
        ]);
    }

    private function formData(FamilyCard $family): array
    {
        return [
            'family' => $family,
            'residents' => Resident::where('status', 'active')->orderBy('name')->get(),
        ];
    }
}
