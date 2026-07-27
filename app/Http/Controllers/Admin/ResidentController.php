<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\ImportResidentsRequest;
use App\Http\Requests\Admin\SaveResidentRequest;
use App\Models\FamilyCard;
use App\Models\Household;
use App\Models\PopulationArea;
use App\Models\Resident;
use App\Models\ResidentEvent;
use App\Services\ActivityLogger;
use App\Services\ResidentExcelImportService;
use App\Services\SiteCache;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ResidentController extends PopulationController
{
    public function __construct(private ActivityLogger $logger, private SiteCache $cache) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Resident::class);
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
        $this->authorize('create', Resident::class);
        return view('admin.population.residents.form', $this->formData(new Resident));
    }

    public function import(ImportResidentsRequest $request, ResidentExcelImportService $importer): RedirectResponse
    {
        $this->authorize('create', Resident::class);
        $validated = $request->validated();
        $result = $importer->import($validated['file']);

        if ($result['created'] || $result['updated']) {
            $this->cache->invalidatePopulationStatistics();
            $this->logger->log('imported', 'penduduk', null, null, $result);
        }

        $message = "{$result['created']} data ditambahkan, {$result['updated']} diperbarui, {$result['failed']} gagal.";

        return back()
            ->with('success', "Impor selesai. {$message}")
            ->with('import_errors', $result['errors']);
    }

    public function downloadImportTemplate(): StreamedResponse
    {
        $this->authorize('create', Resident::class);
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data Penduduk');
        $sheet->fromArray(ResidentExcelImportService::headers(), null, 'A1');
        $sheet->fromArray([
            null, 'Contoh Penduduk', 'L', 'Sukomulyo', '1990-01-31',
            'Islam', 'Kawin', 'WNI', 'SLTA/Sederajat', 'Petani/Pekebun', 'O',
            'Jl. Desa No. 1', 'Sukomulyo', '01', '02', 'Tetap', 'Aktif',
            now()->toDateString(), '081234567890', 'contoh@example.com', '',
        ], null, 'A2');
        $sheet->setCellValueExplicit('A2', '3300000000000001', DataType::TYPE_STRING);
        $sheet->getStyle('A1:U1')->getFont()->setBold(true);
        $sheet->freezePane('A2');
        foreach (range('A', 'U') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        return response()->streamDownload(function () use ($spreadsheet) {
            (new Xlsx($spreadsheet))->save('php://output');
            $spreadsheet->disconnectWorksheets();
        }, 'template-import-penduduk.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function store(SaveResidentRequest $request): RedirectResponse
    {
        $this->authorize('create', Resident::class);
        $data = $request->validated();
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
        $this->authorize('view', $resident);
        $resident->load(['area', 'family.head', 'household.head', 'events.recorder', 'groupMemberships.group']);

        return view('admin.population.residents.show', compact('resident'));
    }

    public function edit(Resident $resident): View
    {
        $this->authorize('update', $resident);
        return view('admin.population.residents.form', $this->formData($resident));
    }

    public function update(SaveResidentRequest $request, Resident $resident): RedirectResponse
    {
        $this->authorize('update', $resident);
        $data = $request->validated();

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
        $this->authorize('delete', $resident);
        if ($resident->headedFamilies()->exists() || $resident->headedHouseholds()->exists() || $resident->chairedGroups()->exists()) {
            return back()->withErrors(['resident' => 'Penduduk masih tercatat sebagai kepala keluarga, kepala rumah tangga, atau ketua kelompok. Ganti penanggung jawab terlebih dahulu.']);
        }

        $this->logger->log('archived', 'penduduk', $resident);
        $resident->delete();

        return redirect()->route('admin.population.residents.index')->with('success', 'Data penduduk diarsipkan.');
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
