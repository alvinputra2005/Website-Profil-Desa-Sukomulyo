<?php

namespace App\Services;

use App\Models\Media;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class ImageProcessor
{
    private const MAIN_MAX_DIMENSION = 1920;

    private const MEDIUM_MAX_DIMENSION = 1200;

    private const THUMBNAIL_MAX_DIMENSION = 600;

    public function store(UploadedFile $file, string $directory, ?string $disk = null): array
    {
        if (! extension_loaded('gd')) {
            throw new RuntimeException('Ekstensi PHP GD diperlukan untuk memproses gambar.');
        }

        $startedAt = hrtime(true);
        $disk ??= config('filesystems.media_disk', 'public');
        $timings = [];
        $source = $main = $medium = $thumb = null;

        try {
            $stageStartedAt = hrtime(true);
            [$inputWidth, $inputHeight] = $this->dimensions($file);
            $this->assertDimensions($inputWidth, $inputHeight);
            $timings['inspect_ms'] = $this->elapsedMilliseconds($stageStartedAt);

            $stageStartedAt = hrtime(true);
            [$source] = $this->open($file);
            $timings['decode_ms'] = $this->elapsedMilliseconds($stageStartedAt);

            $stageStartedAt = hrtime(true);
            $source = $this->orient($source, $file);
            $width = imagesx($source);
            $height = imagesy($source);
            $this->assertDimensions($width, $height);
            $timings['orientation_ms'] = $this->elapsedMilliseconds($stageStartedAt);

            $stageStartedAt = hrtime(true);
            $main = $this->resize($source, $width, $height, self::MAIN_MAX_DIMENSION, IMG_BICUBIC_FIXED);
            $medium = $this->resize($main, imagesx($main), imagesy($main), self::MEDIUM_MAX_DIMENSION, IMG_BICUBIC_FIXED);
            // Resampling the thumbnail from the already bounded main image
            // avoids reading a very large source twice.
            $thumb = $this->resize($medium, imagesx($medium), imagesy($medium), self::THUMBNAIL_MAX_DIMENSION, IMG_BILINEAR_FIXED);
            $timings['resize_ms'] = $this->elapsedMilliseconds($stageStartedAt);

            $name = Str::uuid().'.webp';
            $path = trim($directory, '/').'/'.$name;
            $mediumPath = trim($directory, '/').'/medium/'.$name;
            $thumbnailPath = trim($directory, '/').'/thumbnails/'.$name;

            $stageStartedAt = hrtime(true);
            $mainPayload = $this->encode($main, 82);
            $timings['encode_main_ms'] = $this->elapsedMilliseconds($stageStartedAt);

            $stageStartedAt = hrtime(true);
            $mediumPayload = $this->encode($medium, 80);
            $timings['encode_medium_ms'] = $this->elapsedMilliseconds($stageStartedAt);

            $stageStartedAt = hrtime(true);
            $thumbnailPayload = $this->encode($thumb, 76);
            $timings['encode_thumbnail_ms'] = $this->elapsedMilliseconds($stageStartedAt);

            $stageStartedAt = hrtime(true);
            Storage::disk($disk)->put($path, $mainPayload);
            Storage::disk($disk)->put($mediumPath, $mediumPayload);
            Storage::disk($disk)->put($thumbnailPath, $thumbnailPayload);
            $timings['storage_ms'] = $this->elapsedMilliseconds($stageStartedAt);

            $result = [
                'stored_name' => $name,
                'storage_path' => $path,
                'medium_path' => $mediumPath,
                'thumbnail_path' => $thumbnailPath,
                'mime_type' => 'image/webp',
                'extension' => 'webp',
                'file_size' => Storage::disk($disk)->size($path),
                'width' => imagesx($main),
                'height' => imagesy($main),
            ];

            $timings['total_ms'] = $this->elapsedMilliseconds($startedAt);
            $this->logTimings($file, $inputWidth, $inputHeight, $result, $timings);

            return $result;
        } finally {
            $this->destroyImages([$source, $main, $medium, $thumb]);
        }
    }

    public function backfillMedium(Media $media): string
    {
        if (! extension_loaded('gd')) {
            throw new RuntimeException('Ekstensi PHP GD diperlukan untuk memproses gambar.');
        }

        $disk = Storage::disk($media->disk);
        $contents = $disk->get($media->storage_path);
        $source = @imagecreatefromstring($contents);

        if ($source === false) {
            throw new RuntimeException('Gambar tidak dapat diproses.');
        }

        $medium = null;

        try {
            $medium = $this->resize(
                $source,
                imagesx($source),
                imagesy($source),
                self::MEDIUM_MAX_DIMENSION,
                IMG_BICUBIC_FIXED,
            );
            $directory = trim(str_replace('\\', '/', dirname($media->storage_path)), './');
            $mediumPath = ($directory !== '' ? $directory.'/' : '').'medium/'.pathinfo($media->stored_name, PATHINFO_FILENAME).'.webp';
            $disk->put($mediumPath, $this->encode($medium, 80));

            return $mediumPath;
        } finally {
            $this->destroyImages([$source, $medium]);
        }
    }

    private function open(UploadedFile $file): array
    {
        $path = $file->getRealPath();
        $mime = strtolower((string) $file->getMimeType());
        $image = match ($mime) {
            'image/jpeg', 'image/jpg' => function_exists('imagecreatefromjpeg') ? @imagecreatefromjpeg($path) : false,
            'image/png' => function_exists('imagecreatefrompng') ? @imagecreatefrompng($path) : false,
            'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : false,
            default => false,
        };

        // Keep a string-based fallback for hosts where a GD decoder is
        // unavailable even though the MIME type passed validation.
        if ($image === false) {
            $contents = file_get_contents($path);
            $image = $contents === false ? false : @imagecreatefromstring($contents);
        }

        if ($image === false) {
            throw new RuntimeException('Gambar tidak dapat diproses.');
        }

        return [$image, imagesx($image), imagesy($image)];
    }

    private function dimensions(UploadedFile $file): array
    {
        $dimensions = @getimagesize($file->getRealPath());

        if ($dimensions === false) {
            throw new RuntimeException('Dimensi gambar tidak dapat dibaca.');
        }

        return [(int) $dimensions[0], (int) $dimensions[1]];
    }

    private function assertDimensions(int $width, int $height): void
    {
        if ($width > 4000 || $height > 4000) {
            throw new RuntimeException('Ukuran gambar maksimal adalah 4000 x 4000 piksel.');
        }
    }

    private function orient($image, UploadedFile $file)
    {
        if (! function_exists('exif_read_data') || ! in_array($file->getMimeType(), ['image/jpeg', 'image/jpg'], true)) {
            return $image;
        }
        $orientation = @exif_read_data($file->getRealPath())['Orientation'] ?? 1;
        $rotated = match ($orientation) {
            3 => imagerotate($image, 180, 0), 6 => imagerotate($image, -90, 0), 8 => imagerotate($image, 90, 0), default => $image
        };
        if ($rotated !== $image) {
            imagedestroy($image);
        }

        return $rotated;
    }

    private function resize($source, int $width, int $height, int $max, int $mode)
    {
        $scale = min(1, $max / max($width, $height));
        if ($scale >= 1) {
            return $source;
        }

        $newWidth = max(1, (int) round($width * $scale));
        $newHeight = max(1, (int) round($height * $scale));

        if (function_exists('imagescale')) {
            $target = @imagescale($source, $newWidth, $newHeight, $mode);
            if ($target !== false) {
                imagealphablending($target, false);
                imagesavealpha($target, true);

                return $target;
            }
        }

        $target = imagecreatetruecolor($newWidth, $newHeight);
        imagealphablending($target, false);
        imagesavealpha($target, true);
        imagecopyresampled($target, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        return $target;
    }

    private function encode($image, int $quality): string
    {
        ob_start();
        if (! imagewebp($image, null, $quality)) {
            ob_end_clean();
            throw new RuntimeException('Gambar WebP tidak dapat dibuat.');
        }

        return (string) ob_get_clean();
    }

    private function destroyImages(array $images): void
    {
        $destroyed = [];

        foreach ($images as $image) {
            if ($image === null) {
                continue;
            }

            $id = is_object($image) ? spl_object_id($image) : (string) $image;
            if (isset($destroyed[$id])) {
                continue;
            }

            imagedestroy($image);
            $destroyed[$id] = true;
        }
    }

    private function elapsedMilliseconds(int $startedAt): float
    {
        return round((hrtime(true) - $startedAt) / 1e6, 2);
    }

    private function logTimings(UploadedFile $file, int $inputWidth, int $inputHeight, array $result, array $timings): void
    {
        if (! config('app.debug')) {
            return;
        }

        Log::debug('Image processing completed', [
            'original_name' => $file->getClientOriginalName(),
            'input_bytes' => $file->getSize(),
            'input_width' => $inputWidth,
            'input_height' => $inputHeight,
            'output_bytes' => $result['file_size'],
            'output_width' => $result['width'],
            'output_height' => $result['height'],
            'timings_ms' => $timings,
        ]);
    }
}
