<?php

namespace App\Services\Officials;

use App\Models\Media;
use App\Models\Official;
use App\Models\Resident;
use App\Services\ImageProcessor;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OfficialDataMapper
{
    public function __construct(private ImageProcessor $images) {}

    public function prepare(Request $request, array $data, ?Official $official = null): array
    {
        $data = $this->syncResidentData($data);
        $data['photo_id'] = $this->storePhoto($request, $data, $official);

        return $this->normalize($data);
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

    private function storePhoto(Request $request, array $data, ?Official $official): ?int
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

    private function normalize(array $data): array
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
}
