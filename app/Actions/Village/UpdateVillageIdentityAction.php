<?php

namespace App\Actions\Village;

use App\Http\Requests\Admin\UpdateVillageContentRequest;
use App\Models\Setting;
use App\Models\VillageProfileSection;
use App\Services\Village\VillageSectionWriter;
use Illuminate\Support\Facades\DB;

class UpdateVillageIdentityAction
{
    public const SETTINGS = [
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

    public function __construct(private VillageSectionWriter $writer) {}

    public function execute(UpdateVillageContentRequest $request): void
    {
        $data = $request->validated();
        $currentImage = VillageProfileSection::where('section_key', 'profile')->value('image_id');
        $imageId = $this->writer->resolveImage(
            $request,
            'profile_image_id',
            $currentImage,
            $data['site_name'],
        );

        DB::transaction(function () use ($data, $imageId): void {
            foreach (self::SETTINGS as $key => $field) {
                Setting::updateOrCreate(['key' => $key], [
                    'value' => $data[$field] ?? null,
                    'type' => $field === 'address' ? 'text' : 'string',
                    'group' => 'identitas',
                    'is_public' => true,
                    'updated_by' => auth()->id(),
                ]);
            }
            $this->writer->save('profile', 'Profil Desa', $data['profile_content'], $data['status'], 0, $imageId);
        });
    }
}
