<?php

namespace App\Queries\Village;

use App\Actions\Village\UpdateVillageIdentityAction;
use App\Models\Media;
use App\Models\Official;
use App\Models\Setting;
use App\Models\VillageProfileSection;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class VillageContentQuery
{
    private const PAGES = ['profile', 'vision-mission', 'history', 'potential'];

    /**
     * Tampilkan ringkasan identitas desa sebelum pengguna membuka formulir.
     *
     * Halaman ini sengaja dipisahkan dari form agar menu Identitas Desa
     * memiliki alur yang sama dengan halaman /identitas_desa pada OpenSID:
     * pengguna dapat meninjau data terlebih dahulu, kemudian memilih Ubah Data.
     */
    public function showProfile(): View
    {
        Gate::authorize('manage-content');

        $sections = VillageProfileSection::where('section_key', 'profile')
            ->with('image')
            ->get()
            ->keyBy('section_key');
        $settings = Setting::whereIn('key', array_keys(UpdateVillageIdentityAction::SETTINGS))
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
        Gate::authorize('manage-content');

        $sections = VillageProfileSection::whereIn('section_key', $this->sectionKeys($page))
            ->with('image')->get()->keyBy('section_key');

        $settings = Setting::whereIn('key', array_keys(UpdateVillageIdentityAction::SETTINGS))
            ->pluck('value', 'key');

        $latestMedia = Media::where('mime_type', 'like', 'image/%')->latest()->limit(100)->get();
        $media = $sections->pluck('image')->filter()->concat($latestMedia)->unique('id')->values();
        $villageHead = $page === 'profile'
            ? Official::where('position', 'Kepala Desa')->where('is_active', true)->orderBy('display_order')->first()
            : null;

        return view('admin.village-content.form', compact('page', 'sections', 'settings', 'media', 'villageHead'));
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
