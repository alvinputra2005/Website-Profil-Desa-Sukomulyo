<?php

namespace Tests\Feature;

use App\Models\News;
use App\Models\NewsCategory;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_page_has_complete_primary_and_social_metadata(): void
    {
        $response = $this->get(route('beranda'))->assertOk();

        $response
            ->assertSee('<meta name="description"', false)
            ->assertSee('<meta name="robots" content="index, follow">', false)
            ->assertSee('<link rel="canonical" href="'.route('beranda').'">', false)
            ->assertSee('<meta property="og:title"', false)
            ->assertSee('<meta property="og:description"', false)
            ->assertSee('<meta property="og:image"', false)
            ->assertSee('<meta property="og:url" content="'.route('beranda').'">', false)
            ->assertSee('<meta name="twitter:card" content="summary_large_image">', false);
    }

    public function test_news_structured_data_is_valid_json_and_matches_the_canonical_url(): void
    {
        $this->createNews('published', 'berita-seo', now()->subMinute());

        $response = $this->get(route('berita-desa.show', 'berita-seo'))->assertOk();
        $html = $response->getContent();

        preg_match(
            '~<script type="application/ld\+json">(.*?)</script>~s',
            $html,
            $matches
        );

        $this->assertArrayHasKey(1, $matches);
        $structuredData = json_decode($matches[1], true, flags: JSON_THROW_ON_ERROR);
        $canonical = route('berita-desa.show', 'berita-seo');

        $this->assertSame('NewsArticle', $structuredData['@type']);
        $this->assertSame($canonical, $structuredData['mainEntityOfPage']);
        $this->assertStringContainsString(
            '<link rel="canonical" href="'.$canonical.'">',
            $html
        );
    }

    public function test_draft_and_future_news_are_not_public_or_in_the_sitemap(): void
    {
        $this->createNews('published', 'berita-terbit', now()->subMinute());
        $this->createNews('draft', 'berita-draf', null);
        $this->createNews('published', 'berita-terjadwal', now()->addDay());

        cache()->flush();

        $sitemap = $this->get(route('sitemap'))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertSee(route('berita-desa.show', 'berita-terbit'), false)
            ->assertDontSee(route('berita-desa.show', 'berita-draf'), false)
            ->assertDontSee(route('berita-desa.show', 'berita-terjadwal'), false);

        $xml = simplexml_load_string($sitemap->getContent());
        $this->assertNotFalse($xml);

        $this->get(route('berita-desa.show', 'berita-draf'))->assertNotFound();
        $this->get(route('berita-desa.show', 'berita-terjadwal'))->assertNotFound();
    }

    public function test_admin_and_search_pages_are_noindex_and_legacy_urls_redirect_permanently(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('<meta name="robots" content="noindex, nofollow">', false)
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow');

        $this->get(route('berita-desa.search'))
            ->assertOk()
            ->assertSee('<meta name="robots" content="noindex, nofollow">', false);

        $this->get('/berita')->assertRedirect('/berita-desa', 301);
        $this->get('/profil-desa')->assertRedirect('/profile-desa', 301);
    }

    private function createNews(string $status, string $slug, mixed $publishedAt): News
    {
        $role = Role::firstOrCreate(
            ['code' => 'admin_konten'],
            ['name' => 'Admin Konten']
        );
        $author = User::firstOrCreate(
            ['email' => 'seo-test@sukomulyo.desa.id'],
            [
                'role_id' => $role->id,
                'name' => 'Penguji SEO',
                'password' => 'KataSandiAman123',
                'is_active' => true,
            ]
        );
        $category = NewsCategory::firstOrCreate(
            ['slug' => 'pengujian'],
            ['name' => 'Pengujian', 'description' => 'Kategori pengujian']
        );

        return News::create([
            'category_id' => $category->id,
            'title' => 'Berita Pengujian SEO',
            'slug' => $slug,
            'excerpt' => 'Ringkasan berita untuk pengujian metadata SEO.',
            'content' => '<p>Konten berita pengujian.</p>',
            'status' => $status,
            'published_at' => $publishedAt,
            'author_id' => $author->id,
            'seo_title' => 'Judul SEO Pengujian',
            'seo_description' => 'Deskripsi SEO pengujian.',
        ]);
    }
}
