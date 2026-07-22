<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Setting, VillageProfileSection};
use App\Services\HtmlSanitizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class VillageContentController extends Controller
{
    private const PAGES = ['profile', 'vision-mission', 'history', 'potential'];

    public function __construct(private HtmlSanitizer $sanitizer) {}

    public function edit(string $page): View
    {
        abort_unless(in_array($page, self::PAGES, true), 404);
        $this->authorize('manage-content');

        $sections = VillageProfileSection::whereIn('section_key', $this->sectionKeys($page))
            ->get()->keyBy('section_key');

        $settings = Setting::whereIn('key', array_keys($this->profileSettings()))
            ->pluck('value', 'key');

        return view('admin.village-content.form', compact('page', 'sections', 'settings'));
    }

    public function update(Request $request, string $page): RedirectResponse
    {
        abort_unless(in_array($page, self::PAGES, true), 404);
        $this->authorize('manage-content');

        if ($page === 'profile') {
            $data = $request->validate([
                'site_name' => 'required|string|max:255',
                'tagline' => 'nullable|string|max:255',
                'address' => 'nullable|string|max:1000',
                'email' => 'nullable|email|max:255',
                'phone' => 'nullable|string|max:50',
                'profile_content' => 'required|string|max:100000',
                'status' => ['required', Rule::in(['draft', 'published'])],
            ]);

            DB::transaction(function () use ($data) {
                foreach ($this->profileSettings() as $key => $field) {
                    Setting::updateOrCreate(['key' => $key], [
                        'value' => $data[$field] ?? null,
                        'type' => $field === 'address' ? 'text' : 'string',
                        'group' => 'identitas', 'is_public' => true,
                        'updated_by' => auth()->id(),
                    ]);
                }
                $this->saveSection('profile', 'Profil Desa', $data['profile_content'], $data['status'], 0);
            });
        } elseif ($page === 'vision-mission') {
            $data = $request->validate([
                'vision' => 'required|string|max:100000',
                'mission' => 'required|string|max:100000',
                'status' => ['required', Rule::in(['draft', 'published'])],
            ]);
            DB::transaction(function () use ($data) {
                $this->saveSection('vision', 'Visi Desa', $data['vision'], $data['status'], 10);
                $this->saveSection('mission', 'Misi Desa', $data['mission'], $data['status'], 20);
            });
        } else {
            $key = $page === 'history' ? 'history' : 'potential';
            $defaultTitle = $page === 'history' ? 'Sejarah Desa' : 'Potensi Desa';
            $data = $request->validate([
                'title' => 'required|string|max:255',
                'content' => 'required|string|max:100000',
                'image_id' => 'nullable|integer|exists:media,id',
                'status' => ['required', Rule::in(['draft', 'published'])],
            ]);
            $this->saveSection($key, $data['title'] ?: $defaultTitle, $data['content'], $data['status'], $page === 'history' ? 5 : 30, $data['image_id'] ?? null);
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
