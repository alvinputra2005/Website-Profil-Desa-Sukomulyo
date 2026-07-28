<?php

namespace App\Console\Commands;

use App\Models\Media;
use App\Services\ImageProcessor;
use Illuminate\Console\Command;
use Throwable;

class BackfillMediaMedium extends Command
{
    protected $signature = 'media:backfill-medium {--force : Buat ulang medium yang sudah ada}';

    protected $description = 'Membuat varian gambar medium 1200 px untuk media yang sudah tersimpan';

    public function handle(ImageProcessor $images): int
    {
        $query = Media::query()
            ->where('mime_type', 'like', 'image/%')
            ->when(! $this->option('force'), fn ($query) => $query->whereNull('medium_path'));

        $total = (clone $query)->count();
        $processed = 0;
        $failed = 0;

        if ($total === 0) {
            $this->info('Tidak ada media yang perlu diproses.');

            return self::SUCCESS;
        }

        $this->info("Memproses {$total} gambar...");

        $query->orderBy('id')->chunkById(50, function ($mediaItems) use ($images, &$processed, &$failed): void {
            foreach ($mediaItems as $media) {
                try {
                    $media->update(['medium_path' => $images->backfillMedium($media)]);
                    $processed++;
                    $this->line("OK #{$media->id} {$media->original_name}");
                } catch (Throwable $exception) {
                    $failed++;
                    $this->error("Gagal #{$media->id} {$media->original_name}: {$exception->getMessage()}");
                }
            }
        });

        $this->newLine();
        $this->info("Selesai: {$processed} berhasil, {$failed} gagal.");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
