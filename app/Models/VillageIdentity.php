<?php

namespace App\Models;

use Illuminate\Support\Collection;

class VillageIdentity extends CmsModel
{
    public const KEY_MAP = [
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

    public function keyedValues(): Collection
    {
        return collect(self::KEY_MAP)->mapWithKeys(
            fn (string $column, string $key): array => [$key => $this->getAttribute($column)]
        );
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
