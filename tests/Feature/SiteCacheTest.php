<?php

namespace Tests\Feature;

use App\Models\{Media, News};
use App\Models\NewsCategory;
use App\Models\Setting;
use App\Models\User;
use App\Services\SiteCache;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SiteCacheTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_home_data_is_cached_and_warm_request_does_not_repeat_content_queries(): void
    {
        Cache::flush();

        $this->get(route('beranda'))->assertOk();

        foreach ([
            SiteCache::SETTINGS,
            SiteCache::PUBLIC_LAYOUT,
            SiteCache::HOME_STATISTICS,
            SiteCache::LATEST_NEWS,
            SiteCache::NEWS_CATEGORIES,
            SiteCache::NEWS_ARCHIVES,
        ] as $key) {
            $this->assertTrue(Cache::has($key), "Cache [{$key}] was not created.");
        }

        $queries = [];
        DB::listen(function ($query) use (&$queries): void {
            $queries[] = $query->sql;
        });

        $this->get(route('beranda'))->assertOk();

        $contentQueries = collect($queries)->filter(
            fn (string $sql) => preg_match('/\b(settings|news|news_categories|residents|families|households|population_areas)\b/i', $sql)
        );

        $this->assertCount(0, $contentQueries, $contentQueries->implode(PHP_EOL));
    }

    public function test_navigation_is_not_stale_when_the_layout_cache_contains_an_old_menu(): void
    {
        Cache::flush();
        $this->get(route('beranda'))->assertOk();

        $layout = Cache::get(SiteCache::PUBLIC_LAYOUT);
        $layout['navigation'] = [['label' => 'Peta Desa']];
        Cache::put(SiteCache::PUBLIC_LAYOUT, $layout, SiteCache::TEN_MINUTES);

        $this->get(route('beranda'))
            ->assertOk()
            ->assertSee('Sejarah Desa')
            ->assertSee('Statistik Pendidikan')
            ->assertSee('Pengumuman Desa');
    }

    public function test_setting_change_invalidates_public_settings_and_profile_cache(): void
    {
        $user = User::factory()->create();
        $setting = Setting::create([
            'key' => 'site.name',
            'value' => 'Desa Lama',
            'type' => 'string',
            'group' => 'identitas',
            'is_public' => true,
            'updated_by' => $user->id,
        ]);

        Cache::flush();
        Cache::put(SiteCache::PROFILE, ['stale' => true], SiteCache::ONE_HOUR);

        $this->get(route('beranda'))->assertOk()->assertSee('Desa Lama');
        $this->assertTrue(Cache::has(SiteCache::SETTINGS));

        $setting->update(['value' => 'Desa Baru']);

        $this->assertFalse(Cache::has(SiteCache::SETTINGS));
        $this->assertFalse(Cache::has(SiteCache::PROFILE));
        $this->get(route('beranda'))->assertOk()->assertSee('Desa Baru');
    }

    public function test_news_change_invalidates_lists_and_versioned_detail_cache(): void
    {
        $user = User::factory()->create();
        $category = NewsCategory::create(['name' => 'Pemerintahan', 'slug' => 'pemerintahan']);
        $news = News::create([
            'category_id' => $category->id,
            'title' => 'Judul Lama',
            'slug' => 'judul-lama',
            'excerpt' => 'Ringkasan berita.',
            'content' => '<p>Isi berita.</p>',
            'status' => 'published',
            'published_at' => now(),
            'author_id' => $user->id,
        ]);

        Cache::flush();

        $this->get(route('berita-desa.index'))->assertOk()->assertSee('Judul Lama');
        $this->get(route('berita-desa.show', $news->slug))->assertOk()->assertSee('Judul Lama');

        $cache = app(SiteCache::class);
        $oldDetailKey = $cache->newsDetailKey($news->slug);

        $this->assertTrue(Cache::has(SiteCache::NEWS_LIST));
        $this->assertTrue(Cache::has(SiteCache::LATEST_NEWS));
        $this->assertTrue(Cache::has($oldDetailKey));

        $news->update(['title' => 'Judul Baru']);

        $this->assertFalse(Cache::has(SiteCache::NEWS_LIST));
        $this->assertFalse(Cache::has(SiteCache::LATEST_NEWS));
        $this->assertNotSame($oldDetailKey, $cache->newsDetailKey($news->slug));

        $this->get(route('berita-desa.show', $news->slug))
            ->assertOk()
            ->assertSee('Judul Baru')
            ->assertDontSee('Judul Lama');
    }

    public function test_featured_news_image_is_rendered_before_article_content(): void
    {
        $user = User::factory()->create();
        $category = NewsCategory::create(['name' => 'Kegiatan', 'slug' => 'kegiatan']);
        $media = Media::create([
            'original_name' => 'gambar-utama.webp',
            'stored_name' => 'gambar-utama.webp',
            'disk' => 'public',
            'storage_path' => 'berita/gambar-utama/gambar-utama.webp',
            'mime_type' => 'image/webp',
            'extension' => 'webp',
            'file_size' => 1234,
            'width' => 1200,
            'height' => 800,
            'alt_text' => 'Dokumentasi kegiatan desa',
            'uploaded_by' => $user->id,
        ]);
        $news = News::create([
            'category_id' => $category->id,
            'title' => 'Berita Dengan Gambar Utama',
            'slug' => 'berita-dengan-gambar-utama',
            'content' => '<p>Isi berita berada setelah gambar utama.</p>',
            'featured_image_id' => $media->id,
            'status' => 'published',
            'published_at' => now(),
            'author_id' => $user->id,
        ]);

        $this->get(route('berita-desa.show', $news->slug))
            ->assertOk()
            ->assertSee('class="entry-content"', false)
            ->assertSee('src="/storage/berita/gambar-utama/gambar-utama.webp"', false)
            ->assertSee('alt="Dokumentasi kegiatan desa"', false)
            ->assertSeeInOrder([
                'class="news-detail-hero"',
                '<p>Isi berita berada setelah gambar utama.</p>',
            ], false);
    }

}
