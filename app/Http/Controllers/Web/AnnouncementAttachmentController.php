<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Publication;
use App\Models\PublicationAttachment;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AnnouncementAttachmentController extends Controller
{
    public function preview(
        Publication $publication,
        PublicationAttachment $attachment,
    ): StreamedResponse {
        return $this->stream($publication, $attachment, false);
    }

    public function download(
        Publication $publication,
        PublicationAttachment $attachment,
    ): StreamedResponse {
        return $this->stream($publication, $attachment, true);
    }

    private function stream(
        Publication $publication,
        PublicationAttachment $attachment,
        bool $download,
    ): StreamedResponse {
        abort_unless(
            $publication->type === 'announcement'
                && $publication->status === 'published'
                && ($publication->published_at === null || $publication->published_at->isPast()),
            404,
        );
        abort_unless($attachment->publication_id === $publication->id, 404);

        $media = $attachment->media;
        abort_unless($media && $media->mime_type === 'application/pdf', 404);

        $disk = Storage::disk($media->disk);
        abort_unless($disk->exists($media->storage_path), 404);

        $stream = $disk->readStream($media->storage_path);
        abort_if($stream === false, 404);

        if ($download) {
            $attachment->increment('download_count');
        }

        $fallbackName = Str::slug(pathinfo($media->original_name, PATHINFO_FILENAME))
            ?: 'lampiran-pengumuman';
        $fileName = $fallbackName.'.pdf';
        $disposition = HeaderUtils::makeDisposition(
            $download ? HeaderUtils::DISPOSITION_ATTACHMENT : HeaderUtils::DISPOSITION_INLINE,
            $fileName,
        );

        return response()->stream(function () use ($stream): void {
            fpassthru($stream);
            fclose($stream);
        }, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Length' => (string) $media->file_size,
            'Content-Disposition' => $disposition,
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
