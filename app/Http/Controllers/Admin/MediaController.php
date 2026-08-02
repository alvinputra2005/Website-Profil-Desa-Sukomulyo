<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\EditorMediaUploadRequest;
use App\Http\Requests\Admin\StoreMediaRequest;
use App\Models\Media;
use App\Models\News;
use App\Services\ActivityLogger;
use App\Services\ImageProcessor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class MediaController extends Controller
{
    public function __construct(private ActivityLogger $logger, private ImageProcessor $images) {}

    public function index(Request $r)
    {
        $this->authorize('viewAny', Media::class);
        $q = Media::query();
        if ($s = $r->query('q')) {
            $q->where('original_name', 'like', "%$s%");
        }

return view('admin.media.index', ['items' => $q->latest()->paginate(24)->withQueryString()]);
    }

    public function store(StoreMediaRequest $r)
    {
        $data = $r->validated();
        $file = $data['file'];
        $disk = $this->mediaDisk();
        $isImage = str_starts_with((string) $file->getMimeType(), 'image/');
        $category = $data['category'] ?? ($isImage ? 'news' : 'documents');
        if (! $isImage && $category !== 'documents') {
            return back()->withErrors(['category' => 'File dokumen harus disimpan pada kategori Dokumen publik.'])->withInput();
        }$folderName = $data['folder_name'] ?? pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $directory = $this->mediaDirectory($category, $folderName);
        $attributes = $isImage ? $this->images->store($file, $directory, $disk) : $this->storeDocument($file, $directory, $disk);
        $media = Media::create(array_merge($attributes, ['original_name' => $file->getClientOriginalName(), 'disk' => $disk, 'alt_text' => $data['alt_text'] ?? null, 'caption' => $data['caption'] ?? null, 'uploaded_by' => auth()->id()]));
        $this->logger->log('uploaded', 'media', $media);

        return back()->with('success', 'Media berhasil diunggah ke folder '.$directory.'.');
    }

    public function editorUpload(EditorMediaUploadRequest $r)
    {
        $data = $r->validated();
        $file = $data['image'];
        $disk = $this->mediaDisk();
        $directory = $this->mediaDirectory('news', $data['folder_name'] ?? 'berita-baru');
        $media = Media::create(array_merge($this->images->store($file, $directory, $disk), ['original_name' => $file->getClientOriginalName(), 'disk' => $disk, 'alt_text' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME), 'uploaded_by' => auth()->id()]));
        $this->logger->log('uploaded', 'media', $media);

        return response()->json(['location' => $media->url, 'url' => $media->url, 'alt' => $media->alt_text]);
    }

    public function destroy(Media $media)
    {
        $this->authorize('delete', $media);
        if ($this->isUsed($media)) {
            return back()->withErrors(['media' => 'Media masih digunakan dan tidak dapat dihapus.']);
        }
        $this->deleteMedia($media);

        return back()->with('success', 'Media dihapus.');
    }

    public function bulkDestroy(Request $request)
    {
        $this->authorize('viewAny', Media::class);

        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'distinct', 'exists:media,id'],
        ]);

        $items = Media::whereKey($data['ids'])->get();
        $blocked = $items->first(fn (Media $media): bool => $this->isUsed($media));

        if ($blocked) {
            throw ValidationException::withMessages(['ids' => 'Ada media terpilih yang masih digunakan dan tidak dapat dihapus.']);
        }

        DB::transaction(function () use ($items): void {
            $items->each(function (Media $media): void {
                $this->authorize('delete', $media);
                $this->deleteMedia($media);
            });
        });

        return back()->with('success', $items->count().' media dihapus.');
    }

    private function mediaDisk(): string
    {
        return config('filesystems.media_disk', 'public');
    }

    private function mediaDirectory(string $category, string $name): string
    {
        $root = match ($category) {
            'gallery' => 'galeri','officials' => 'perangkat-desa','banners' => 'banner','documents' => 'dokumen-publik',default => 'berita'
        };
        $slug = Str::slug($name) ?: 'umum';

        return $root.'/'.$slug;
    }

    private function storeDocument($file, string $directory, string $disk): array
    {
        $path = $file->store($directory, $disk);

        return ['stored_name' => basename($path), 'storage_path' => $path, 'mime_type' => $file->getMimeType(), 'extension' => strtolower($file->extension()), 'file_size' => $file->getSize(), 'width' => null, 'height' => null];
    }

    private function isUsed(Media $media): bool
    {
        $used = collect(['village_profile_sections.image_id', 'officials.photo_id', 'news.featured_image_id', 'publications.featured_image_id', 'publication_attachments.media_id', 'map_features.photo_id', 'galleries.cover_media_id', 'gallery_items.media_id'])->contains(function ($ref) use ($media) {
            [$table, $column] = explode('.', $ref);

            return DB::table($table)->where($column, $media->id)->exists();
        });
        $inlineUsed = News::withTrashed()->where('content', 'like', '%/storage/'.addcslashes($media->storage_path, '%_\\').'%')->exists();

        return $used || $inlineUsed;
    }

    private function deleteMedia(Media $media): void
    {
        Storage::disk($media->disk)->delete(array_filter([$media->storage_path, $media->medium_path, $media->thumbnail_path]));
        $this->logger->log('deleted', 'media', $media);
        $media->delete();
    }
}
