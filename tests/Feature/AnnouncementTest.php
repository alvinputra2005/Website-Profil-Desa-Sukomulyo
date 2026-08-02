<?php

namespace Tests\Feature;

use App\Models\Media;
use App\Models\Publication;
use App\Models\PublicationAttachment;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AnnouncementTest extends TestCase
{
    use RefreshDatabase;

    private User $author;

    protected function setUp(): void
    {
        parent::setUp();

        $role = Role::create(['name' => 'Super Admin', 'code' => 'super_admin']);
        $this->author = User::factory()->create([
            'role_id' => $role->id,
            'is_active' => true,
        ]);
    }

    public function test_index_only_lists_currently_published_announcements(): void
    {
        $visible = $this->publication([
            'title' => 'Pengumuman Kerja Bakti',
            'slug' => 'pengumuman-kerja-bakti',
        ]);
        $this->publication([
            'title' => 'Pengumuman Draf',
            'slug' => 'pengumuman-draf',
            'status' => 'draft',
        ]);
        $this->publication([
            'title' => 'Pengumuman Masa Depan',
            'slug' => 'pengumuman-masa-depan',
            'published_at' => now()->addDay(),
        ]);
        $this->publication([
            'title' => 'Dokumen Publik Biasa',
            'slug' => 'dokumen-publik-biasa',
            'type' => 'document',
        ]);

        $this->get(route('announcements.index'))
            ->assertOk()
            ->assertSee('<h1>Pengumuman Desa</h1>', false)
            ->assertSeeInOrder([
                'class="page-banner',
                '<div class="announcement-page">',
                '<h1>Pengumuman Desa</h1>',
            ], false)
            ->assertSee($visible->title)
            ->assertDontSee('Pengumuman Draf')
            ->assertDontSee('Pengumuman Masa Depan')
            ->assertDontSee('Dokumen Publik Biasa');
    }

    public function test_search_sort_and_detail_page_work(): void
    {
        $older = $this->publication([
            'title' => 'Musyawarah Dusun',
            'slug' => 'musyawarah-dusun',
            'excerpt' => 'Jadwal musyawarah warga.',
            'published_at' => now()->subDays(2),
        ]);
        $newer = $this->publication([
            'title' => 'Pelayanan Keliling',
            'slug' => 'pelayanan-keliling',
            'excerpt' => 'Pelayanan administrasi untuk masyarakat.',
            'published_at' => now()->subDay(),
        ]);

        $response = $this->get(route('announcements.index', ['q' => 'musyawarah', 'sort' => 'oldest']))
            ->assertOk()
            ->assertSee($older->title)
            ->assertDontSee($newer->title);

        $response->assertSee('value="musyawarah"', false);

        $this->get(route('announcements.show', ['publication' => $older->slug]))
            ->assertOk()
            ->assertSee($older->title)
            ->assertSee('Isi pengumuman resmi.', false);
    }

    public function test_index_allows_the_number_of_displayed_announcements_to_be_changed(): void
    {
        foreach (range(1, 6) as $number) {
            $this->publication([
                'slug' => 'pengumuman-tampilan-'.$number,
                'published_at' => now()->subMinutes($number),
            ]);
        }

        $this->get(route('announcements.index', ['per_page' => 5]))
            ->assertOk()
            ->assertViewHas(
                'announcements',
                fn ($announcements) => $announcements->count() === 5
                    && $announcements->perPage() === 5
                    && $announcements->lastPage() === 2,
            )
            ->assertSee('name="per_page"', false)
            ->assertSee('value="5" selected', false);
    }

    public function test_draft_and_non_announcement_detail_are_not_public(): void
    {
        $draft = $this->publication([
            'slug' => 'draf-rahasia',
            'status' => 'draft',
        ]);
        $document = $this->publication([
            'slug' => 'dokumen-bukan-pengumuman',
            'type' => 'document',
        ]);

        $this->get(route('announcements.show', ['publication' => $draft->slug]))->assertNotFound();
        $this->get(route('announcements.show', ['publication' => $document->slug]))->assertNotFound();
    }

    public function test_pdf_can_be_previewed_and_downloaded_with_a_counter(): void
    {
        Storage::fake('public');
        $announcement = $this->publication(['slug' => 'pengumuman-dengan-pdf']);
        $path = 'dokumen-publik/pengumuman/pengumuman-dengan-pdf/surat.pdf';
        Storage::disk('public')->put($path, '%PDF-1.4 test');
        $media = Media::create([
            'original_name' => 'Surat Resmi.pdf',
            'stored_name' => 'surat.pdf',
            'disk' => 'public',
            'storage_path' => $path,
            'mime_type' => 'application/pdf',
            'extension' => 'pdf',
            'file_size' => Storage::disk('public')->size($path),
            'uploaded_by' => $this->author->id,
        ]);
        $attachment = PublicationAttachment::create([
            'publication_id' => $announcement->id,
            'media_id' => $media->id,
            'title' => 'Surat Pengumuman',
            'display_order' => 1,
        ]);

        $this->get(route('announcements.attachments.preview', [
            'publication' => $announcement->slug,
            'attachment' => $attachment->id,
        ]))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf')
            ->assertHeader('content-disposition', 'inline; filename=surat-resmi.pdf');
        $this->assertSame(0, $attachment->fresh()->download_count);

        $this->get(route('announcements.attachments.download', [
            'publication' => $announcement->slug,
            'attachment' => $attachment->id,
        ]))
            ->assertOk()
            ->assertHeader('content-disposition', 'attachment; filename=surat-resmi.pdf');
        $this->assertSame(1, $attachment->fresh()->download_count);
    }

    public function test_attachment_from_another_announcement_and_missing_file_return_404(): void
    {
        Storage::fake('public');
        $announcement = $this->publication(['slug' => 'pengumuman-satu']);
        $other = $this->publication(['slug' => 'pengumuman-dua']);
        $media = Media::create([
            'original_name' => 'hilang.pdf',
            'stored_name' => 'hilang.pdf',
            'disk' => 'public',
            'storage_path' => 'dokumen-publik/hilang.pdf',
            'mime_type' => 'application/pdf',
            'extension' => 'pdf',
            'file_size' => 100,
            'uploaded_by' => $this->author->id,
        ]);
        $attachment = PublicationAttachment::create([
            'publication_id' => $other->id,
            'media_id' => $media->id,
            'display_order' => 1,
        ]);

        $this->get(route('announcements.attachments.download', [
            'publication' => $announcement->slug,
            'attachment' => $attachment->id,
        ]))->assertNotFound();
        $this->get(route('announcements.attachments.download', [
            'publication' => $other->slug,
            'attachment' => $attachment->id,
        ]))->assertNotFound();
        $this->assertSame(0, $attachment->fresh()->download_count);
    }

    public function test_admin_can_create_an_announcement_with_multiple_pdf_attachments(): void
    {
        Storage::fake('public');

        $response = $this->actingAs($this->author)->post(route('admin.resources.store', 'publications'), [
            'type' => 'announcement',
            'title' => 'Pengumuman Administrasi',
            'slug' => 'pengumuman-administrasi',
            'excerpt' => 'Ringkasan pengumuman.',
            'content' => '<p>Isi <strong>pengumuman</strong>.</p>',
            'status' => 'published',
            'published_at' => now()->format('Y-m-d H:i:s'),
            'attachment_uploads' => [
                UploadedFile::fake()->create('surat-utama.pdf', 100, 'application/pdf'),
                UploadedFile::fake()->create('jadwal.pdf', 80, 'application/pdf'),
            ],
            'attachment_upload_titles' => ['Surat Utama', 'Jadwal Pelaksanaan'],
            'attachment_sequence' => ['new:1', 'new:0'],
        ]);

        $response->assertSessionHasNoErrors()->assertRedirect();
        $announcement = Publication::where('slug', 'pengumuman-administrasi')->firstOrFail();
        $this->assertSame('announcement', $announcement->type);
        $this->assertStringNotContainsString('<script', $announcement->content);
        $this->assertSame(
            ['Jadwal Pelaksanaan', 'Surat Utama'],
            $announcement->attachments()->pluck('title')->all(),
        );
        $this->assertDatabaseCount('media', 2);
        $this->assertDatabaseHas('activity_logs', [
            'module' => 'publication-attachments',
            'action' => 'created',
        ]);
    }

    public function test_admin_can_publish_village_regulation_in_profile_sidebar_only(): void
    {
        Storage::fake('public');

        $this->actingAs($this->author)
            ->get(route('admin.resources.create', ['resource' => 'publications', 'type' => 'regulation']))
            ->assertOk()
            ->assertSee('name="title"', false)
            ->assertSee('name="attachment_uploads[]"', false)
            ->assertDontSee('name="content"', false)
            ->assertDontSee('name="status"', false)
            ->assertDontSee('name="published_at"', false);

        $response = $this->actingAs($this->author)->post(route('admin.resources.store', 'publications'), [
            'type' => 'regulation',
            'title' => 'Peraturan Desa tentang APBDes',
            'attachment_uploads' => [
                UploadedFile::fake()->create('perdes-apbdes.pdf', 100, 'application/pdf'),
            ],
        ]);

        $response->assertSessionHasNoErrors()->assertRedirect();
        $this->assertStringContainsString('/admin/publications/', (string) $response->headers->get('Location'));

        $regulation = Publication::where('title', 'Peraturan Desa tentang APBDes')->firstOrFail();
        $this->assertSame('peraturan-desa-tentang-apbdes', $regulation->slug);
        $attachment = $regulation->attachments()->with('media')->firstOrFail();

        $this->assertSame('regulation', $regulation->type);
        $this->get(route('profile-desa'))
            ->assertOk()
            ->assertSee('Peraturan Desa tentang APBDes')
            ->assertSee('profile-regulations-panel', false)
            ->assertSee($attachment->media->url, false);

        $this->get(route('informasi-publik-desa'))
            ->assertOk()
            ->assertDontSee('Peraturan Desa tentang APBDes');
    }

    public function test_village_regulation_requires_a_pdf_attachment(): void
    {
        $this->actingAs($this->author)
            ->post(route('admin.resources.store', 'publications'), [
                'type' => 'regulation',
                'title' => 'Peraturan Desa Tanpa Lampiran',
            ])
            ->assertSessionHasErrors('attachment_uploads');

        $this->assertDatabaseMissing('publications', [
            'title' => 'Peraturan Desa Tanpa Lampiran',
        ]);
    }

    private function publication(array $attributes = []): Publication
    {
        return Publication::create(array_merge([
            'type' => 'announcement',
            'title' => 'Pengumuman Resmi',
            'slug' => 'pengumuman-'.fake()->unique()->slug(),
            'excerpt' => 'Ringkasan pengumuman desa.',
            'content' => '<p>Isi pengumuman resmi.</p>',
            'status' => 'published',
            'published_at' => now()->subHour(),
            'author_id' => $this->author->id,
        ], $attributes));
    }
}
