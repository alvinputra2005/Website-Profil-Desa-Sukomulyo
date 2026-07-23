<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Models\Official;
use App\Models\Setting;
use App\Models\VillageProfileSection;
use App\Services\HtmlSanitizer;
use App\Services\ImageProcessor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class VillageContentController extends Controller
{
    private const PAGES = ['profile', 'vision-mission', 'history', 'potential'];

    public function __construct(
        private HtmlSanitizer $sanitizer,
        private ImageProcessor $images,
    ) {}

    /**
     * Tampilkan ringkasan identitas desa sebelum pengguna membuka formulir.
     *
     * Halaman ini sengaja dipisahkan dari form agar menu Identitas Desa
     * memiliki alur yang sama dengan halaman /identitas_desa pada OpenSID:
     * pengguna dapat meninjau data terlebih dahulu, kemudian memilih Ubah Data.
     */
    public function showProfile(): View
    {
        $this->authorize('manage-content');

        $sections = VillageProfileSection::where('section_key', 'profile')
            ->with('image')
            ->get()
            ->keyBy('section_key');
        $settings = Setting::whereIn('key', array_keys($this->profileSettings()))
            ->pluck('value', 'key');
        $villageHead = Official::where('position', 'Kepala Desa')
            ->where('is_active', true)
            ->orderBy('display_order')
            ->first();

        return view('admin.village-content.profile', [
            'identityGroups' => $this->identityGroups($settings, $villageHead),
            'profileSection' => $sections->get('profile'),
            'siteName' => (string) ($settings->get('site.name') ?: 'Desa Sukomulyo'),
            'location' => collect([
                $settings->get('district.name'),
                $settings->get('regency.name'),
                $settings->get('province.name'),
            ])->filter()->implode(', '),
        ]);
    }

    /**
     * Buka formulir identitas dari halaman ringkasan.
     */
    public function editProfile(): View
    {
        return $this->edit('profile');
    }

    public function edit(string $page): View
    {
        abort_unless(in_array($page, self::PAGES, true), 404);
        $this->authorize('manage-content');

        $sections = VillageProfileSection::whereIn('section_key', $this->sectionKeys($page))
            ->with('image')->get()->keyBy('section_key');

        $settings = Setting::whereIn('key', array_keys($this->profileSettings()))
            ->pluck('value', 'key');

        $latestMedia = Media::where('mime_type', 'like', 'image/%')->latest()->limit(100)->get();
        $media = $sections->pluck('image')->filter()->concat($latestMedia)->unique('id')->values();
        $villageHead = $page === 'profile'
            ? Official::where('position', 'Kepala Desa')->where('is_active', true)->orderBy('display_order')->first()
            : null;

        return view('admin.village-content.form', compact('page', 'sections', 'settings', 'media', 'villageHead'));
    }

    public function update(Request $request, string $page): RedirectResponse
    {
        abort_unless(in_array($page, self::PAGES, true), 404);
        $this->authorize('manage-content');
        $sections = VillageProfileSection::whereIn('section_key', $this->sectionKeys($page))
            ->get()->keyBy('section_key');

        if ($page === 'profile') {
            $data = $request->validate(array_merge([
                'site_name' => 'required|string|max:255',
                'tagline' => 'nullable|string|max:255',
                'village_code' => ['nullable', 'string', 'max:20', 'regex:/^[0-9.\-\s]+$/'],
                'village_bps_code' => ['nullable', 'string', 'max:20', 'regex:/^[0-9.\-\s]+$/'],
                'postal_code' => ['nullable', 'regex:/^[0-9]{5}$/'],
                'address' => 'nullable|string|max:1000',
                'email' => 'nullable|email|max:255',
                'phone' => ['nullable', 'string', 'max:30', 'regex:/^[0-9+().\-\s]+$/'],
                'mobile' => ['nullable', 'string', 'max:30', 'regex:/^[0-9+().\-\s]+$/'],
                'website' => 'nullable|url:http,https|max:255',
                'district_name' => 'nullable|string|max:100',
                'district_code' => ['nullable', 'string', 'max:15', 'regex:/^[0-9.\-\s]+$/'],
                'district_head_name' => 'nullable|string|max:255',
                'district_head_nip' => 'nullable|string|max:30',
                'regency_name' => 'nullable|string|max:100',
                'regency_code' => ['nullable', 'string', 'max:15', 'regex:/^[0-9.\-\s]+$/'],
                'province_name' => 'nullable|string|max:100',
                'province_code' => ['nullable', 'string', 'max:10', 'regex:/^[0-9.\-\s]+$/'],
                'profile_content' => 'required|string|max:100000',
                'status' => ['required', Rule::in(['draft', 'published'])],
            ], $this->imageRules('profile_image_id')));
            $profileImageId = $this->resolveImageId(
                $request,
                'profile_image_id',
                $sections->get('profile')?->image_id,
                $data['site_name']
            );

            DB::transaction(function () use ($data, $profileImageId) {
                foreach ($this->profileSettings() as $key => $field) {
                    Setting::updateOrCreate(['key' => $key], [
                        'value' => $data[$field] ?? null,
                        'type' => $field === 'address' ? 'text' : 'string',
                        'group' => 'identitas', 'is_public' => true,
                        'updated_by' => auth()->id(),
                    ]);
                }
                $this->saveSection('profile', 'Profil Desa', $data['profile_content'], $data['status'], 0, $profileImageId);
            });
        } elseif ($page === 'vision-mission') {
            $data = $request->validate(array_merge([
                'vision' => 'required|string|max:100000',
                'mission' => 'required|string|max:100000',
                'status' => ['required', Rule::in(['draft', 'published'])],
            ], $this->imageRules('vision_image_id'), $this->imageRules('mission_image_id')));
            $visionImageId = $this->resolveImageId($request, 'vision_image_id', $sections->get('vision')?->image_id, 'visi-desa');
            $missionImageId = $this->resolveImageId($request, 'mission_image_id', $sections->get('mission')?->image_id, 'misi-desa');
            DB::transaction(function () use ($data, $visionImageId, $missionImageId) {
                $this->saveSection('vision', 'Visi Desa', $data['vision'], $data['status'], 10, $visionImageId);
                $this->saveSection('mission', 'Misi Desa', $data['mission'], $data['status'], 20, $missionImageId);
            });
        } else {
            $key = $page === 'history' ? 'history' : 'potential';
            $defaultTitle = $page === 'history' ? 'Sejarah Desa' : 'Potensi Desa';
            $data = $request->validate(array_merge([
                'title' => 'required|string|max:255',
                'content' => 'required|string|max:100000',
                'status' => ['required', Rule::in(['draft', 'published'])],
            ], $this->imageRules('image_id')));
            $imageId = $this->resolveImageId($request, 'image_id', $sections->get($key)?->image_id, $data['title']);
            $this->saveSection($key, $data['title'] ?: $defaultTitle, $data['content'], $data['status'], $page === 'history' ? 5 : 30, $imageId);
        }

        return $page === 'profile'
            ? redirect()->route('admin.village-content.profile')->with('success', 'Identitas Desa berhasil diperbarui.')
            : back()->with('success', 'Konten berhasil diperbarui.');
    }

    private function saveSection(string $key, string $title, string $content, string $status, int $order, ?int $imageId = null): void
    {
        VillageProfileSection::updateOrCreate(['section_key' => $key], [
            'title' => $title, 'content' => $this->sanitizer->clean($content), 'image_id' => $imageId,
            'status' => $status, 'display_order' => $order, 'updated_by' => auth()->id(),
        ]);
    }

    private function imageRules(string $name): array
    {
        $base = $this->imageUploadBase($name);

        return [
            $name => 'nullable|integer|exists:media,id',
            $base.'_upload' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            $base.'_alt' => 'nullable|string|max:255',
            'remove_'.$base => 'nullable|boolean',
        ];
    }

    private function resolveImageId(Request $request, string $name, ?int $currentId, string $folderName): ?int
    {
        $base = $this->imageUploadBase($name);
        if ($request->boolean('remove_'.$base)) {
            return null;
        }

        if ($request->hasFile($base.'_upload')) {
            $file = $request->file($base.'_upload');
            $disk = config('filesystems.media_disk', 'public');
            $folder = Str::slug($folderName) ?: 'umum';
            $media = Media::create(array_merge(
                $this->images->store($file, 'profil/'.$folder, $disk),
                [
                    'original_name' => $file->getClientOriginalName(),
                    'disk' => $disk,
                    'alt_text' => $request->input($base.'_alt') ?: $folderName,
                    'uploaded_by' => auth()->id(),
                ]
            ));

            return $media->id;
        }

        $imageId = $request->exists($name) ? ($request->input($name) ?: null) : $currentId;
        if ($imageId && $request->exists($base.'_alt')) {
            Media::whereKey($imageId)->update(['alt_text' => $request->input($base.'_alt')]);
        }

        return $imageId ? (int) $imageId : null;
    }

    private function imageUploadBase(string $name): string
    {
        return str_ends_with($name, '_id') ? substr($name, 0, -3) : $name;
    }

    private function sectionKeys(string $page): array
    {
        return match ($page) {
            'vision-mission' => ['vision', 'mission'],
            'profile' => ['profile'],
            'history' => ['history'],
            default => ['potential'],
        };
    }

    private function profileSettings(): array
    {
        return [
            'site.name' => 'site_name',
            'site.tagline' => 'tagline',
            'village.code' => 'village_code',
            'village.bps_code' => 'village_bps_code',
            'village.postal_code' => 'postal_code',
            'site.address' => 'address',
            'site.email' => 'email',
            'site.phone' => 'phone',
            'village.mobile' => 'mobile',
            'site.url' => 'website',
            'district.name' => 'district_name',
            'district.code' => 'district_code',
            'district.head_name' => 'district_head_name',
            'district.head_nip' => 'district_head_nip',
            'regency.name' => 'regency_name',
            'regency.code' => 'regency_code',
            'province.name' => 'province_name',
            'province.code' => 'province_code',
        ];
    }

    private function identityGroups($settings, ?Official $villageHead): array
    {
        $value = fn (string $key): string => (string) ($settings->get($key) ?: '');

        return [
            [
                'title' => 'Desa',
                'rows' => [
                    ['label' => 'Nama Desa', 'value' => $value('site.name') ?: 'Desa Sukomulyo'],
                    ['label' => 'Kode Desa', 'value' => $value('village.code')],
                    ['label' => 'Kode BPS Desa', 'value' => $value('village.bps_code')],
                    ['label' => 'Kode Pos Desa', 'value' => $value('village.postal_code')],
                    ['label' => 'Nama Kepala Desa', 'value' => $villageHead?->full_name ?? ''],
                    ['label' => 'NIP Kepala Desa', 'value' => $villageHead?->nip ?? ''],
                    ['label' => 'Alamat Kantor Desa', 'value' => $value('site.address') ?: 'Kantor Desa Sukomulyo, Indonesia'],
                    ['label' => 'E-Mail Desa', 'value' => $value('site.email'), 'type' => 'email'],
                    ['label' => 'Nomor Telepon Desa', 'value' => $value('site.phone')],
                    ['label' => 'Nomor Ponsel Desa', 'value' => $value('village.mobile')],
                    ['label' => 'Website Desa', 'value' => $value('site.url'), 'type' => 'url'],
                ],
            ],
            [
                'title' => 'Kecamatan',
                'rows' => [
                    ['label' => 'Nama Kecamatan', 'value' => $value('district.name')],
                    ['label' => 'Kode Kecamatan', 'value' => $value('district.code')],
                    ['label' => 'Nama Camat', 'value' => $value('district.head_name')],
                    ['label' => 'NIP Camat', 'value' => $value('district.head_nip')],
                ],
            ],
            [
                'title' => 'Kabupaten',
                'rows' => [
                    ['label' => 'Nama Kabupaten', 'value' => $value('regency.name')],
                    ['label' => 'Kode Kabupaten', 'value' => $value('regency.code')],
                ],
            ],
            [
                'title' => 'Provinsi',
                'rows' => [
                    ['label' => 'Nama Provinsi', 'value' => $value('province.name')],
                    ['label' => 'Kode Provinsi', 'value' => $value('province.code')],
                ],
            ],
        ];
    }
}
