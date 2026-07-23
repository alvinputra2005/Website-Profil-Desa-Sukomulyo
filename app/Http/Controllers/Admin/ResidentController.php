<?php

namespace App\Http\Controllers\Admin;

use App\Models\FamilyCard;
use App\Models\Household;
use App\Models\PopulationArea;
use App\Models\Resident;
use App\Models\ResidentEvent;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ResidentController extends PopulationController
{
    public function __construct(private ActivityLogger $logger) {}

    public function index(Request $request): View
    {
        $query = Resident::with(['family.head', 'household.head', 'area']);
        if ($search = trim((string) $request->query('q'))) {
            $query->where(fn ($builder) => $builder
                ->where('name', 'like', "%{$search}%")
                ->orWhere('nik', 'like', "%{$search}%"));
        }
        foreach (['status', 'sex', 'area_id', 'family_id', 'household_id'] as $filter) {
            if ($request->filled($filter)) {
                $query->where($filter, $request->query($filter));
            }
        }

        return view('admin.population.residents.index', [
            'residents' => $query->orderBy('name')->paginate(20)->withQueryString(),
            'areas' => PopulationArea::orderBy('hamlet')->orderBy('rw')->orderBy('rt')->get(),
            'families' => FamilyCard::orderBy('family_card_number')->get(),
            'households' => Household::orderBy('household_number')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.population.residents.form', $this->formData(new Resident));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateResident($request);
        $resident = DB::transaction(function () use ($data) {
            $area = $this->resolveArea($data);
            $resident = Resident::create(array_merge(
                $this->residentData($data),
                ['area_id' => $area?->id]
            ));
            $this->recordEvent($resident, $data['initial_event_type'], $data['event_date'], $data);
            $this->logger->log('created', 'penduduk', $resident);

            return $resident;
        });

        return redirect()->route('admin.population.residents.show', $resident)
            ->with('success', 'Data penduduk berhasil ditambahkan.');
    }

    public function show(Resident $resident): View
    {
        $resident->load(['area', 'family.head', 'household.head', 'events.recorder', 'groupMemberships.group']);

        return view('admin.population.residents.show', compact('resident'));
    }

    public function edit(Resident $resident): View
    {
        return view('admin.population.residents.form', $this->formData($resident));
    }

    public function update(Request $request, Resident $resident): RedirectResponse
    {
        $data = $this->validateResident($request, $resident);

        DB::transaction(function () use ($data, $resident) {
            $oldStatus = $resident->status;
            $area = $this->resolveArea($data);
            $resident->update(array_merge(
                $this->residentData($data),
                ['area_id' => $area?->id]
            ));
            if ($oldStatus !== $resident->status) {
                $eventType = match ($resident->status) {
                    'moved' => 'departure',
                    'deceased' => 'death',
                    'missing' => 'missing',
                    default => 'reactivated',
                };
                $this->recordEvent($resident, $eventType, $data['event_date'] ?? now()->toDateString(), $data);
            }
            $this->logger->log('updated', 'penduduk', $resident);
        });

        return back()->with('success', 'Data penduduk berhasil diperbarui.');
    }

    public function destroy(Resident $resident): RedirectResponse
    {
        if ($resident->headedFamilies()->exists() || $resident->headedHouseholds()->exists() || $resident->chairedGroups()->exists()) {
            return back()->withErrors(['resident' => 'Penduduk masih tercatat sebagai kepala keluarga, kepala rumah tangga, atau ketua kelompok. Ganti penanggung jawab terlebih dahulu.']);
        }

        $this->logger->log('archived', 'penduduk', $resident);
        $resident->delete();

        return redirect()->route('admin.population.residents.index')->with('success', 'Data penduduk diarsipkan.');
    }

    private function validateResident(Request $request, ?Resident $resident = null): array
    {
        return $request->validate(array_merge([
            'nik' => ['required', 'digits:16', Rule::unique('residents')->ignore($resident?->id)],
            'name' => ['required', 'string', 'max:100'],
            'sex' => ['required', Rule::in(['L', 'P'])],
            'birth_place' => ['nullable', 'string', 'max:100'],
            'birth_date' => ['nullable', 'date', 'before_or_equal:today'],
            'religion' => ['nullable', 'string', 'max:30'],
            'marital_status' => ['nullable', 'string', 'max:30'],
            'citizenship' => ['required', Rule::in(['WNI', 'WNA'])],
            'education' => ['nullable', 'string', 'max:100'],
            'occupation' => ['nullable', 'string', 'max:100'],
            'blood_type' => ['nullable', Rule::in(['A', 'B', 'AB', 'O', '-'])],
            'father_nik' => ['nullable', 'digits:16'],
            'father_name' => ['nullable', 'string', 'max:100'],
            'mother_nik' => ['nullable', 'digits:16'],
            'mother_name' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:25'],
            'email' => ['nullable', 'email', 'max:150'],
            'current_address' => ['nullable', 'string', 'max:255'],
            'previous_address' => ['nullable', 'string', 'max:255'],
            'family_id' => ['nullable', 'exists:families,id'],
            'household_id' => ['nullable', 'exists:households,id'],
            'family_relationship' => ['nullable', 'string', 'max:50'],
            'household_relationship' => ['nullable', 'string', 'max:50'],
            'resident_status' => ['required', Rule::in(['permanent', 'non_permanent'])],
            'status' => ['required', Rule::in(['active', 'moved', 'deceased', 'missing'])],
            'registered_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'initial_event_type' => [$resident ? 'nullable' : 'required', Rule::in(['birth', 'arrival'])],
            'event_date' => [$resident ? 'nullable' : 'required', 'date'],
            'reported_at' => ['nullable', 'date'],
            'destination_address' => ['nullable', 'string', 'max:255'],
            'event_cause' => ['nullable', 'string', 'max:100'],
            'event_notes' => ['nullable', 'string', 'max:2000'],
        ], $this->areaRules()));
    }

    private function residentData(array $data): array
    {
        return collect($data)->only([
            'nik', 'name', 'sex', 'birth_place', 'birth_date', 'religion', 'marital_status',
            'citizenship', 'education', 'occupation', 'blood_type', 'father_nik', 'father_name',
            'mother_nik', 'mother_name', 'phone', 'email', 'current_address', 'previous_address',
            'family_id', 'household_id', 'family_relationship', 'household_relationship',
            'resident_status', 'status', 'registered_at', 'notes',
        ])->all();
    }

    private function recordEvent(Resident $resident, string $type, string $date, array $data): void
    {
        $resident->loadMissing('family');
        ResidentEvent::create([
            'resident_id' => $resident->id,
            'event_type' => $type,
            'event_date' => $date,
            'reported_at' => $data['reported_at'] ?? now()->toDateString(),
            'resident_name' => $resident->name,
            'nik' => $resident->nik,
            'sex' => $resident->sex,
            'family_card_number' => $resident->family?->family_card_number,
            'origin_address' => $resident->previous_address,
            'destination_address' => $data['destination_address'] ?? null,
            'cause' => $data['event_cause'] ?? null,
            'notes' => $data['event_notes'] ?? null,
            'recorded_by' => auth()->id(),
        ]);
    }

    private function formData(Resident $resident): array
    {
        return [
            'resident' => $resident,
            'families' => FamilyCard::with('head')->where('is_active', true)->orderBy('family_card_number')->get(),
            'households' => Household::with('head')->where('is_active', true)->orderBy('household_number')->get(),
        ];
    }
}
