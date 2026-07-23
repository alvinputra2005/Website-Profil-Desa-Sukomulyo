<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Models\Official;
use App\Models\Resident;
use App\Services\ActivityLogger;
use App\Services\ImageProcessor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OfficialController extends Controller
{
    public function __construct(
        private ActivityLogger $logger,
        private ImageProcessor $images,
    ) {}

    public function index(Request $request): View
    {
        $this->authorizeModule();
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

    public function create(): View
    {
        $this->authorizeModule();

        return view('admin.officials.form', $this->formData(new Official([
            'display_order' => (int) Official::max('display_order') + 1,
            'is_active' => true,
            'organization_color' => '#526b42',
            'registered_at' => now()->toDateString(),
        ])));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeModule();
        $data = $this->validated($request);
        $data['is_active'] = array_key_exists('is_active', $data) ? $data['is_active'] : true;
        $official = DB::transaction(function () use ($request, $data) {
            $data = $this->syncResidentData($data);
            $data['photo_id'] = $this->storePhoto($request, $data);
            $official = Official::create($this->normalized($data));
            $this->logger->log('created', 'perangkat-desa', $official, null, $official->toArray());

            return $official;
        });

        return redirect()->route('admin.officials.edit', $official)
            ->with('success', 'Data perangkat desa berhasil ditambahkan.');
    }

    public function edit(Official $official): View
    {
        $this->authorizeModule();
        $official->load(['photo', 'resident']);

        return view('admin.officials.form', $this->formData($official));
    }

    public function update(Request $request, Official $official): RedirectResponse
    {
        $this->authorizeModule();
        $data = $this->validated($request, $official);
        DB::transaction(function () use ($request, $data, $official) {
            $old = $official->toArray();
            $data = $this->syncResidentData($data);
            $data['photo_id'] = $this->storePhoto($request, $data, $official);
            $official->update($this->normalized($data));
            $this->logger->log('updated', 'perangkat-desa', $official, $old, $official->fresh()->toArray());
        });

        return back()->with('success', 'Data perangkat desa berhasil diperbarui.');
    }

    public function destroy(Official $official): RedirectResponse
    {
        $this->authorizeModule();
        $this->logger->log('deleted', 'perangkat-desa', $official, $official->toArray());
        $official->delete();

        return redirect()->route('admin.officials.index')->with('success', 'Data perangkat desa berhasil dihapus.');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        $this->authorizeModule();
        $data = $request->validate(['ids' => ['required', 'array', 'min:1'], 'ids.*' => ['integer', 'exists:officials,id']]);
        DB::transaction(function () use ($data) {
            Official::whereKey($data['ids'])->get()->each(function (Official $official) {
                $this->logger->log('deleted', 'perangkat-desa', $official, $official->toArray());
                $official->delete();
            });
        });

        return back()->with('success', count($data['ids']).' data perangkat desa berhasil dihapus.');
    }

    public function toggleStatus(Official $official): RedirectResponse
    {
        $this->authorizeModule();
        $old = $official->toArray();
        $official->update(['is_active' => ! $official->is_active]);
        $this->logger->log('status_changed', 'perangkat-desa', $official, $old, $official->fresh()->toArray());

        return back()->with('success', 'Status perangkat desa berhasil diubah.');
    }

    public function move(Official $official, string $direction): RedirectResponse
    {
        $this->authorizeModule();
        abort_unless(in_array($direction, ['up', 'down'], true), 404);
        $operator = $direction === 'up' ? '<' : '>';
        $order = $direction === 'up' ? 'desc' : 'asc';
        $neighbor = Official::where('display_order', $operator, $official->display_order)
            ->orderBy('display_order', $order)
            ->first();

        if ($neighbor) {
            DB::transaction(function () use ($official, $neighbor) {
                $currentOrder = $official->display_order;
                $official->update(['display_order' => $neighbor->display_order]);
                $neighbor->update(['display_order' => $currentOrder]);
            });
        }

        return back()->with('success', 'Urutan perangkat desa berhasil diperbarui.');
    }

    public function organization(): View
    {
        $this->authorizeModule();
        $officials = Official::with('photo')->where('is_active', true)->orderBy('display_order')->get();

        return view('admin.officials.organization', ['nodes' => $this->organizationTree($officials)]);
    }

    public function print(Request $request): View
    {
        $this->authorizeModule();
        $query = Official::with('photo')->orderBy('display_order')->orderBy('name');
        if ($request->query('status') !== 'all') {
            $query->where('is_active', true);
        }

        return view('admin.officials.print', ['officials' => $query->get()]);
    }

    public function export(): StreamedResponse
    {
        $this->authorizeModule();
        $filename = 'buku-pemerintah-desa-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'wb');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, [
                'No', 'Nama', 'NIK', 'NIPD', 'NIP', 'Tag ID Card', 'Tempat Lahir', 'Tanggal Lahir',
                'Jenis Kelamin', 'Agama', 'Pendidikan', 'Pangkat/Golongan', 'Jabatan',
                'Nomor SK Pengangkatan', 'Tanggal SK Pengangkatan', 'Nomor SK Pemberhentian',
                'Tanggal SK Pemberhentian', 'Masa Jabatan', 'Status',
            ], ';');
            $number = 0;
            Official::orderBy('display_order')->orderBy('name')->each(function (Official $official) use ($handle, &$number) {
                $number++;
                fputcsv($handle, [
                    $number,
                    $official->full_name,
                    $official->nik,
                    $official->village_employee_number,
                    $official->nip,
                    $official->id_card_tag,
                    $official->birth_place,
                    $official->birth_date?->format('Y-m-d'),
                    $official->sex_label,
                    $official->religion,
                    $official->education,
                    $official->rank_grade,
                    $official->position_label,
                    $official->appointment_decree,
                    $official->appointment_date?->format('Y-m-d'),
                    $official->dismissal_decree,
                    $official->dismissal_date?->format('Y-m-d'),
                    $official->term,
                    $official->is_active ? 'Aktif' : 'Tidak Aktif',
                ], ';');
            });
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function authorizeModule(): void
    {
        abort_unless(auth()->user()?->can('manage-content'), 403);
    }

    private function validated(Request $request, ?Official $official = null): array
    {
        if (! $request->has('source')) {
            $request->merge(['source' => 'external']);
        }
        $excludedSuperiors = $official ? array_merge([$official->id], $this->descendantIds($official)) : [];

        return $request->validate([
            'source' => ['required', Rule::in(['resident', 'external'])],
            'resident_id' => ['nullable', 'required_if:source,resident', 'exists:residents,id'],
            'name' => ['required', 'string', 'max:255'],
            'title_prefix' => ['nullable', 'string', 'max:50'],
            'title_suffix' => ['nullable', 'string', 'max:50'],
            'nik' => ['nullable', 'digits:16'],
            'village_employee_number' => ['nullable', 'string', 'max:25'],
            'nip' => ['nullable', 'string', 'max:30'],
            'id_card_tag' => ['nullable', 'string', 'max:50', Rule::unique('officials')->ignore($official?->id)],
            'birth_place' => ['nullable', 'string', 'max:100'],
            'birth_date' => ['nullable', 'date', 'before_or_equal:today'],
            'sex' => ['nullable', Rule::in(['L', 'P'])],
            'education' => ['nullable', 'string', 'max:100'],
            'religion' => ['nullable', 'string', 'max:30'],
            'rank_grade' => ['nullable', 'string', 'max:50'],
            'position' => ['required', 'string', 'max:255'],
            'appointment_decree' => ['nullable', 'string', 'max:100'],
            'appointment_date' => ['nullable', 'date'],
            'dismissal_decree' => ['nullable', 'string', 'max:100'],
            'dismissal_date' => ['nullable', 'date', 'after_or_equal:appointment_date'],
            'term' => ['nullable', 'string', 'max:150'],
            'is_acting' => ['nullable', 'boolean'],
            'superior_id' => ['nullable', 'exists:officials,id', Rule::notIn($excludedSuperiors)],
            'organization_level' => ['nullable', 'integer', 'min:1', 'max:20'],
            'organization_offset' => ['nullable', 'integer', 'between:-100,100'],
            'organization_layout' => ['nullable', Rule::in(['hanging', 'horizontal'])],
            'organization_color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'photo_id' => ['nullable', 'exists:media,id'],
            'photo_upload' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'photo_camera' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'photo_alt' => ['nullable', 'string', 'max:255'],
            'remove_photo' => ['nullable', 'boolean'],
            'biography' => ['nullable', 'string', 'max:5000'],
            'display_order' => ['required', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'can_sign_on_behalf' => ['nullable', 'boolean'],
            'can_sign_for' => ['nullable', 'boolean'],
            'phone' => ['nullable', 'string', 'max:25'],
            'email' => ['nullable', 'email', 'max:150'],
            'facebook' => ['nullable', 'url:http,https', 'max:500'],
            'instagram' => ['nullable', 'url:http,https', 'max:500'],
            'youtube' => ['nullable', 'url:http,https', 'max:500'],
            'x' => ['nullable', 'url:http,https', 'max:500'],
            'registered_at' => ['nullable', 'date'],
        ]);
    }

    private function normalized(array $data): array
    {
        $data['resident_id'] = $data['source'] === 'resident' ? $data['resident_id'] : null;
        $data['social_media'] = array_filter([
            'facebook' => $data['facebook'] ?? null,
            'instagram' => $data['instagram'] ?? null,
            'youtube' => $data['youtube'] ?? null,
            'x' => $data['x'] ?? null,
        ]);
        foreach (['is_acting', 'is_active', 'can_sign_on_behalf', 'can_sign_for'] as $field) {
            $data[$field] = (bool) ($data[$field] ?? false);
        }

        return collect($data)->except([
            'source', 'photo_upload', 'photo_camera', 'photo_alt', 'remove_photo',
            'facebook', 'instagram', 'youtube', 'x',
        ])->all();
    }

    private function syncResidentData(array $data): array
    {
        if ($data['source'] !== 'resident') {
            return $data;
        }

        $resident = Resident::findOrFail($data['resident_id']);

        return array_merge($data, [
            'name' => $resident->name,
            'nik' => $resident->nik,
            'birth_place' => $resident->birth_place,
            'birth_date' => $resident->birth_date?->format('Y-m-d'),
            'sex' => $resident->sex,
            'education' => $resident->education,
            'religion' => $resident->religion,
        ]);
    }

    private function storePhoto(Request $request, array $data, ?Official $official = null): ?int
    {
        if ($request->boolean('remove_photo')) {
            return null;
        }

        $file = $request->file('photo_camera') ?: $request->file('photo_upload');
        if (! $file) {
            return isset($data['photo_id']) ? (int) $data['photo_id'] : $official?->photo_id;
        }

        $disk = config('filesystems.media_disk', 'public');
        $folder = Str::slug($data['name'] ?? 'perangkat-desa') ?: 'umum';
        $media = Media::create(array_merge(
            $this->images->store($file, 'perangkat-desa/'.$folder, $disk),
            [
                'original_name' => $file->getClientOriginalName(),
                'disk' => $disk,
                'alt_text' => $data['photo_alt'] ?: 'Foto '.($data['name'] ?? 'perangkat desa'),
                'uploaded_by' => auth()->id(),
            ],
        ));

        return $media->id;
    }

    private function formData(Official $official): array
    {
        $currentMedia = $official->photo ? collect([$official->photo]) : collect();
        $latestMedia = Media::where('mime_type', 'like', 'image/%')->latest()->limit(100)->get();
        $social = $official->social_media ?? [];

        return [
            'official' => $official,
            'residents' => Resident::with('area')->where('status', 'active')->orderBy('name')->get(),
            'superiors' => Official::whereKeyNot($official->id)
                ->when($official->exists, fn ($query) => $query->whereNotIn('id', $this->descendantIds($official)))
                ->orderBy('display_order')->orderBy('name')->get(),
            'positions' => Official::distinct()->orderBy('position')->pluck('position')->filter()->values(),
            'media' => $currentMedia->concat($latestMedia)->unique('id')->values(),
            'social' => $social,
            'religions' => ['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu', 'Kepercayaan terhadap Tuhan YME'],
            'educationOptions' => ['Tidak/Belum Sekolah', 'Belum Tamat SD/Sederajat', 'Tamat SD/Sederajat', 'SLTP/Sederajat', 'SLTA/Sederajat', 'Diploma I/II', 'Akademi/Diploma III/Sarjana Muda', 'Diploma IV/Strata I', 'Strata II', 'Strata III'],
            'defaultPositions' => ['Kepala Desa', 'Sekretaris Desa', 'Kaur Keuangan', 'Kaur Tata Usaha dan Umum', 'Kaur Perencanaan', 'Kasi Pemerintahan', 'Kasi Kesejahteraan', 'Kasi Pelayanan', 'Kepala Dusun'],
        ];
    }

    private function descendantIds(Official $official): array
    {
        $ids = [];
        $pending = [$official->id];
        while ($pending) {
            $children = Official::whereIn('superior_id', $pending)->pluck('id')->all();
            $children = array_values(array_diff($children, $ids));
            if (! $children) {
                break;
            }
            $ids = array_merge($ids, $children);
            $pending = $children;
        }

        return $ids;
    }

    private function organizationTree(Collection $officials): array
    {
        $byParent = $officials->groupBy(fn (Official $official) => (int) ($official->superior_id ?? 0));
        $visited = [];
        $build = function (int $parentId) use (&$build, &$visited, $byParent): array {
            return collect($byParent->get($parentId, collect()))
                ->reject(fn (Official $official) => isset($visited[$official->id]))
                ->map(function (Official $official) use (&$build, &$visited) {
                    $visited[$official->id] = true;

                    return ['official' => $official, 'children' => $build($official->id)];
                })->all();
        };
        $nodes = $build(0);
        foreach ($officials as $official) {
            if (! isset($visited[$official->id])) {
                $visited[$official->id] = true;
                $nodes[] = ['official' => $official, 'children' => $build($official->id)];
            }
        }

        return $nodes;
    }
}
