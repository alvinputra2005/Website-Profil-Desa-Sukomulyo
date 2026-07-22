<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class ImageProcessor
{
    public function store(UploadedFile $file, string $directory): array
    {
        [$source, $width, $height] = $this->open($file);
        $source = $this->orient($source, $file, $width, $height);
        $width = imagesx($source);
        $height = imagesy($source);

        $main = $this->resize($source, $width, $height, 1920);
        $thumb = $this->resize($source, $width, $height, 600);
        imagedestroy($source);

        $name = Str::uuid().'.webp';
        $path = trim($directory, '/').'/'.$name;
        $thumbnailPath = trim($directory, '/').'/thumbnails/'.$name;
        Storage::disk('public')->put($path, $this->encode($main, 82));
        Storage::disk('public')->put($thumbnailPath, $this->encode($thumb, 76));
        $result = [
            'stored_name' => $name,
            'storage_path' => $path,
            'thumbnail_path' => $thumbnailPath,
            'mime_type' => 'image/webp',
            'extension' => 'webp',
            'file_size' => Storage::disk('public')->size($path),
            'width' => imagesx($main),
            'height' => imagesy($main),
        ];
        imagedestroy($main);
        imagedestroy($thumb);

        return $result;
    }

    private function open(UploadedFile $file): array
    {
        $contents = file_get_contents($file->getRealPath());
        $image = $contents === false ? false : @imagecreatefromstring($contents);
        if ($image === false) throw new RuntimeException('Gambar tidak dapat diproses.');
        return [$image, imagesx($image), imagesy($image)];
    }

    private function orient($image, UploadedFile $file, int $width, int $height)
    {
        if (!function_exists('exif_read_data') || !in_array($file->getMimeType(), ['image/jpeg', 'image/jpg'], true)) return $image;
        $orientation = @exif_read_data($file->getRealPath())['Orientation'] ?? 1;
        $rotated = match ($orientation) { 3 => imagerotate($image, 180, 0), 6 => imagerotate($image, -90, 0), 8 => imagerotate($image, 90, 0), default => $image };
        if ($rotated !== $image) imagedestroy($image);
        return $rotated;
    }

    private function resize($source, int $width, int $height, int $max)
    {
        $scale = min(1, $max / max($width, $height));
        $newWidth = max(1, (int) round($width * $scale));
        $newHeight = max(1, (int) round($height * $scale));
        $target = imagecreatetruecolor($newWidth, $newHeight);
        imagealphablending($target, false);
        imagesavealpha($target, true);
        imagecopyresampled($target, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        return $target;
    }

    private function encode($image, int $quality): string
    {
        ob_start();
        imagewebp($image, null, $quality);
        return (string) ob_get_clean();
    }
}
