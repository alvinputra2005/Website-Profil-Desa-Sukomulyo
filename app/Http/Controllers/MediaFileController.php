<?php

namespace App\Http\Controllers;

use App\Models\Media;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MediaFileController extends Controller
{
    public function __invoke(Media $media, string $variant = 'original'): StreamedResponse
    {
        abort_unless(in_array($variant, ['original', 'medium', 'thumbnail'], true), 404);

        $path = match ($variant) {
            'medium' => $media->medium_path ?: $media->storage_path,
            'thumbnail' => $media->thumbnail_path ?: $media->storage_path,
            default => $media->storage_path,
        };
        $disk = Storage::disk($media->disk);

        abort_unless($disk->exists($path), 404);

        $stream = $disk->readStream($path);
        abort_if($stream === false, 404);

        return response()->stream(function () use ($stream): void {
            fpassthru($stream);
            fclose($stream);
        }, 200, [
            'Content-Type' => $variant === 'original' ? $media->mime_type : 'image/webp',
            'Content-Disposition' => 'inline; filename="'.addslashes($media->stored_name).'"',
            'Cache-Control' => 'public, max-age=86400',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
