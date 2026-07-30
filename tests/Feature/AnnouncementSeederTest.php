<?php

namespace Tests\Feature;

use App\Models\Publication;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AnnouncementSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_published_announcements_with_downloadable_pdf_attachments(): void
    {
        Storage::fake('public');

        $this->seed(DatabaseSeeder::class);
        $this->seed(DatabaseSeeder::class);

        $this->assertSame(13, Publication::query()->announcements()->count());

        $this->get(route('announcements.index'))
            ->assertOk()
            ->assertViewHas(
                'announcements',
                fn ($announcements) => $announcements->count() === 10
                    && $announcements->total() === 13
                    && $announcements->lastPage() === 2,
            );

        $this->get(route('announcements.index', ['page' => 2]))
            ->assertOk()
            ->assertViewHas(
                'announcements',
                fn ($announcements) => $announcements->count() === 3
                    && $announcements->currentPage() === 2,
            );

        $announcement = Publication::query()
            ->where('slug', 'pemberitahuan-kerja-bakti-desa-juli-2026')
            ->with('attachments.media')
            ->firstOrFail();
        $attachment = $announcement->attachments->firstOrFail();

        $this->assertSame(29, $attachment->download_count);
        Storage::disk('public')->assertExists($attachment->media->storage_path);

        $this->get(route('announcements.attachments.download', [
            'publication' => $announcement->slug,
            'attachment' => $attachment->id,
        ]))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');

        $this->assertSame(30, $attachment->fresh()->download_count);
    }
}
