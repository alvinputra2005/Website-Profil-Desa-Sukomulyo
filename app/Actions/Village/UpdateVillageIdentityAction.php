<?php

namespace App\Actions\Village;

use App\Http\Requests\Admin\UpdateVillageContentRequest;
use App\Models\VillageIdentity;
use App\Models\VillageProfileSection;
use App\Services\Village\VillageSectionWriter;
use Illuminate\Support\Facades\DB;

class UpdateVillageIdentityAction
{
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
            VillageIdentity::query()->updateOrCreate(['id' => 1], [
                ...collect(VillageIdentity::KEY_MAP)
                    ->values()
                    ->mapWithKeys(fn (string $field): array => [$field => $data[$field] ?? null])
                    ->all(),
                'updated_by' => auth()->id(),
            ]);
            $this->writer->save('profile', 'Profil Desa', $data['profile_content'], $data['status'], 0, $imageId);
        });
    }
}
