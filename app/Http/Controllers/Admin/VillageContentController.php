<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Media, Setting, VillageProfileSection};
use App\Services\{HtmlSanitizer, ImageProcessor};
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

        return view('admin.village-content.form', compact('page', 'sections', 'settings', 'media'));
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
                'address' => 'nullable|string|max:1000',
                'email' => 'nullable|email|max:255',
                'phone' => 'nullable|string|max:50',
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

        return back()->with('success', 'Konten berhasil diperbarui.');
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
        return ['site.name' => 'site_name', 'site.tagline' => 'tagline', 'site.address' => 'address', 'site.email' => 'email', 'site.phone' => 'phone'];
    }
}
