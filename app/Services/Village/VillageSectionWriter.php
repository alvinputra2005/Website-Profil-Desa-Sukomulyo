<?php

namespace App\Services\Village;

use App\Models\Media;
use App\Models\VillageProfileSection;
use App\Services\HtmlSanitizer;
use App\Services\ImageProcessor;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class VillageSectionWriter
{
    public function __construct(
        private HtmlSanitizer $sanitizer,
        private ImageProcessor $images,
    ) {}

    public function save(
        string $key,
        string $title,
        string $content,
        string $status,
        int $order,
        ?int $imageId = null,
    ): VillageProfileSection {
        return VillageProfileSection::updateOrCreate(['section_key' => $key], [
            'title' => $title,
            'content' => $this->sanitizer->clean($content),
            'image_id' => $imageId,
            'status' => $status,
            'display_order' => $order,
            'updated_by' => auth()->id(),
        ]);
    }

    public function resolveImage(
        Request $request,
        string $name,
        ?int $currentId,
        string $folderName,
    ): ?int {
        $base = str_ends_with($name, '_id') ? substr($name, 0, -3) : $name;
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
                ],
            ));

            return $media->id;
        }

        $imageId = $request->exists($name) ? ($request->input($name) ?: null) : $currentId;
        if ($imageId && $request->exists($base.'_alt')) {
            Media::find($imageId)?->update(['alt_text' => $request->input($base.'_alt')]);
        }

        return $imageId ? (int) $imageId : null;
    }
}
