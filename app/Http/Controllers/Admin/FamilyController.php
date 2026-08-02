<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\SaveFamilyRequest;
use App\Models\FamilyCard;
use App\Models\Resident;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class FamilyController extends PopulationController
{
    public function __construct(private ActivityLogger $logger) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', FamilyCard::class);
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

    public function archive(Request $request): View
    {
        $this->authorize('viewAny', FamilyCard::class);
        $query = FamilyCard::onlyTrashed()->with(['head', 'area'])->withCount(['members' => fn ($builder) => $builder->where('status', 'active')]);
        if ($search = trim((string) $request->query('q'))) $query->where('family_card_number', 'like', "%{$search}%");
        return view('admin.population.families.archive', ['families' => $query->latest('deleted_at')->paginate(20)->withQueryString()]);
    }

    public function restore(FamilyCard $family): RedirectResponse
    {
        $this->authorize('delete', $family);
        $family->restore();
        return back()->with('success', 'Data keluarga berhasil dipulihkan.');
    }

    public function create(): View
    {
        $this->authorize('create', FamilyCard::class);
        return view('admin.population.families.form', $this->formData(new FamilyCard));
    }

    public function store(SaveFamilyRequest $request): RedirectResponse
    {
        $this->authorize('create', FamilyCard::class);
        $data = $request->validated();
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
        $this->authorize('update', $family);
        $family->load(['area', 'members']);

        return view('admin.population.families.form', $this->formData($family));
    }

    public function update(SaveFamilyRequest $request, FamilyCard $family): RedirectResponse
    {
        $this->authorize('update', $family);
        $data = $request->validated();
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
        $this->authorize('delete', $family);
        if ($family->members()->exists()) {
            return back()->withErrors(['family' => 'Keluarga masih memiliki anggota. Pindahkan anggota terlebih dahulu.']);
        }
        $this->logger->log('archived', 'keluarga', $family);
        $family->delete();

        return back()->with('success', 'Data keluarga diarsipkan.');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        $this->authorize('viewAny', FamilyCard::class);

        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'distinct', 'exists:family_cards,id'],
        ]);

        $families = FamilyCard::whereKey($data['ids'])->get();

        DB::transaction(function () use ($families): void {
            foreach ($families as $family) {
                $this->authorize('delete', $family);
                if ($family->members()->exists()) {
                    throw ValidationException::withMessages(['ids' => 'Masih ada keluarga terpilih yang memiliki anggota. Pindahkan anggota terlebih dahulu.']);
                }
                $this->logger->log('archived', 'keluarga', $family);
                $family->delete();
            }
        });

        return redirect()->route('admin.population.families.index')->with('success', $families->count().' data keluarga diarsipkan.');
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
        if (! $family->head_resident_id) {
            return;
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
            'residents' => $family->exists
                ? Resident::where('family_id', $family->id)->orderBy('name')->get()
                : collect(),
        ];
    }
}
