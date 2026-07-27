<?php

namespace App\Actions\Village;

use App\Http\Requests\Admin\UpdateVillageContentRequest;
use App\Models\VillageProfileSection;
use App\Services\Village\VillageSectionWriter;

class UpdateVillageSectionAction
{
    public function __construct(private VillageSectionWriter $writer) {}

    public function execute(UpdateVillageContentRequest $request, string $page): void
    {
        $key = $page === 'history' ? 'history' : 'potential';
        $data = $request->validated();
        $currentImage = VillageProfileSection::where('section_key', $key)->value('image_id');
        $imageId = $this->writer->resolveImage($request, 'image_id', $currentImage, $data['title']);
        $this->writer->save(
            $key,
            $data['title'],
            $data['content'],
            $data['status'],
            $page === 'history' ? 5 : 30,
            $imageId,
        );
    }
}
