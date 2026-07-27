<?php

namespace App\Queries\Officials;

use App\Models\Media;
use App\Models\Official;
use App\Models\Resident;

class OfficialFormQuery
{
    public function data(Official $official): array
    {
        $currentMedia = $official->photo ? collect([$official->photo]) : collect();
        $latestMedia = Media::where('mime_type', 'like', 'image/%')->latest()->limit(100)->get();

        return [
            'official' => $official,
            'residents' => Resident::with('area')->where('status', 'active')->orderBy('name')->get(),
            'superiors' => Official::whereKeyNot($official->id)
                ->when($official->exists, fn ($query) => $query->whereNotIn('id', $this->descendantIds($official)))
                ->orderBy('display_order')->orderBy('name')->get(),
            'positions' => Official::distinct()->orderBy('position')->pluck('position')->filter()->values(),
            'media' => $currentMedia->concat($latestMedia)->unique('id')->values(),
            'social' => $official->social_media ?? [],
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
}
