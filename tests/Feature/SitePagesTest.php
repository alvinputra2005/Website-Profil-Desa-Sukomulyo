<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SitePagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_public_pages_can_be_opened(): void
    {
        $pages = [
            route('beranda') => 'Desa Sukomulyo',
            route('profile-desa') => 'Identitas Desa',
            route('profile-desa.comments') => 'Komentar Identitas Desa',
            route('profile-desa.detail', ['section' => 'sejarah']) => 'Sejarah Desa',
            route('profile-desa.detail', ['section' => 'visi-misi']) => 'Visi dan Misi',
            route('pemerintahan-desa') => 'Pemerintahan Desa',
            route('potensi-desa') => 'Potensi Desa',
            route('data-desa-statistik') => 'Data Desa',
            route('data-statistik.detail', ['section' => 'penduduk']) => 'Statistik Penduduk',
            route('data-statistik.detail', ['section' => 'keluarga']) => 'Statistik Keluarga',
            route('data-statistik.detail', ['section' => 'pendidikan']) => 'Statistik Pendidikan',
            route('data-statistik.detail', ['section' => 'pekerjaan']) => 'Statistik Pekerjaan',
            route('data-statistik.detail', ['section' => 'ekonomi']) => 'Statistik Ekonomi',
            route('data-statistik.detail', ['section' => 'idm']) => 'IDM (Indeks Desa Membangun)',
            route('data-statistik.detail', ['section' => 'visualisasi']) => 'Visualisasi Data',
            route('informasi-publik-desa') => 'Informasi Publik Desa',
            route('informasi-desa.detail', ['section' => 'pengumuman']) => 'Pengumuman Desa',
            route('informasi-desa.detail', ['section' => 'layanan-administrasi']) => 'Layanan Administrasi',
            route('informasi-desa.detail', ['section' => 'agenda']) => 'Agenda Desa',
            route('informasi-desa.detail', ['section' => 'bantuan-sosial']) => 'Informasi Bantuan Sosial',
            route('informasi-desa.detail', ['section' => 'informasi-publik']) => 'Informasi Publik',
            route('peta-desa') => 'Peta Desa',
            route('galeri-desa') => 'Galeri Desa',
            route('berita-desa.index') => 'Berita Desa',
            route('berita-desa.category', 'pemerintahan') => 'Kategori: Pemerintahan',
            route('berita-desa.archive', '2026') => 'Arsip Berita 2026',
            route('berita-desa.show', 'musyawarah-desa-penyusunan-program-kerja') => 'Musyawarah Desa Penyusunan Program Kerja',
            route('berita-desa.search', ['q' => 'UMKM']) => 'Pelatihan Pemasaran Digital untuk UMKM',
            route('kontak.index') => 'Hubungi Pemerintah Desa',
        ];

        foreach ($pages as $url => $content) {
            $this->get($url)->assertOk()->assertSee($content);
        }
    }

    public function test_navigation_uses_the_requested_dropdown_groups(): void
    {
        $response = $this->get(route('beranda'))->assertOk();
        $html = $response->getContent();
        preg_match('/<nav class="header-navigation".*?<\/nav>/s', $html, $matches);
        $navigation = $matches[0] ?? '';

        $this->assertSame(3, substr_count($navigation, '<ul class="sub-menu">'));
        $this->assertSame(3, substr_count($navigation, 'class="nav-dropdown-toggle"'));
        foreach (['Profil Desa', 'Identitas Desa', 'Data Statistik', 'Informasi Desa', 'Berita Desa', 'Galeri Desa', 'Statistik Keluarga', 'Layanan Administrasi', 'APBDes'] as $label) {
            $this->assertStringContainsString($label, $navigation);
        }
        foreach (['data-desa-statistik', 'informasi-publik-desa'] as $route) {
            $this->assertDoesNotMatchRegularExpression('/<a href="'.preg_quote(route($route), '/').'"/', $navigation);
        }
        $this->assertStringNotContainsString('Visualisasi Data', $navigation);
        $this->assertStringNotContainsString('Kependudukan', $navigation);
        $this->assertStringNotContainsString('Statistik Pendidikan', $navigation);
        $this->assertStringNotContainsString('Statistik Pekerjaan', $navigation);
        $this->assertMatchesRegularExpression('/<a href="'.preg_quote(route('profile-desa'), '/').'".*?>\s*<span>Identitas Desa<\/span>/s', $navigation);
        $this->assertStringNotContainsString('>Peta Desa</a>', $navigation);
        $this->assertStringNotContainsString('#', $navigation);
        $this->assertGreaterThan(strpos($navigation, 'Berita Desa'), strpos($navigation, 'Galeri Desa'));
        $this->assertStringNotContainsString('Asal-usul dan perkembangan', $navigation);
        $this->assertStringNotContainsString('Jumlah penduduk berdasarkan jenjang pendidikan', $navigation);
        $this->assertStringContainsString(route('pemerintahan-desa'), $navigation);
        $this->assertStringContainsString(route('potensi-desa'), $navigation);
    }

    public function test_unknown_pages_use_the_converted_404_page(): void
    {
        $this->get('/halaman-yang-tidak-ada')
            ->assertNotFound()
            ->assertSee('Halaman Tidak Ditemukan');

        $this->get('/berita-desa/artikel-tidak-ada')
            ->assertNotFound()
            ->assertSee('Halaman Tidak Ditemukan');
    }

    public function test_removed_population_pages_are_not_accessible_or_listed_in_navigation(): void
    {
        foreach ([
            '/kependudukan',
            '/kependudukan/ringkasan',
            '/kependudukan/jenis-kelamin',
            '/kependudukan/kelompok-umur',
            '/kependudukan/pendidikan',
            '/kependudukan/pekerjaan',
            '/kependudukan/agama',
            '/kependudukan/status-perkawinan',
            '/laporan-penduduk',
        ] as $url) {
            $this->get($url)->assertNotFound();
        }

        $html = $this->get(route('beranda'))->assertOk()->getContent();
        preg_match('/<nav class="header-navigation".*?<\/nav>/s', $html, $matches);

        $this->assertStringNotContainsString('Kependudukan', $matches[0] ?? '');
    }

    public function test_homepage_shows_budget_transparency_section(): void
    {
        $this->get(route('beranda'))
            ->assertOk()
            ->assertSee('Laki-laki')
            ->assertSee('Perempuan')
            ->assertSee('Transparansi APBDes')
            ->assertSee('Pendapatan APBDes 2026')
            ->assertSee('Belanja APBDes 2026')
            ->assertSee('Realisasi APBDes 2026')
            ->assertSee('budget-progress-fill', false)
            ->assertSee('budget-progress-percent', false)
            ->assertSee(route('transparansi-apbdes'), false)
            ->assertSee('Total Pendapatan APBDes 2026')
            ->assertSee('Total Penggunaan Belanja APBDes 2026')
            ->assertSee('Total Realisasi APBDes 2026');
    }

    public function test_homepage_identity_link_opens_the_village_identity_page(): void
    {
        $this->get(route('beranda'))
            ->assertOk()
            ->assertSee('Lihat Identitas Desa')
            ->assertSee('href="'.route('profile-desa').'"', false);

        $this->get(route('profile-desa'))
            ->assertOk()
            ->assertDontSee('page-banner-heading', false)
            ->assertSee('Gambaran Umum Desa')
            ->assertDontSee('Data Identitas Desa');
    }

    public function test_village_identity_uses_article_layout_and_dedicated_sidebar(): void
    {
        $response = $this->get(route('profile-desa'))
            ->assertOk()
            ->assertSee('news-detail-layout', false)
            ->assertSee('Identitas Desa Sukomulyo')
            ->assertSee('Cetak Artikel')
            ->assertSee('data-print-article', false)
            ->assertSee('Profil Pimpinan')
            ->assertSee('Peraturan Desa')
            ->assertSee('Kantor Desa')
            ->assertSee('Street View 360 derajat Kantor Desa Sukomulyo')
            ->assertSee('Lihat Street View &amp; Rute', false)
            ->assertSee('Komentar Terbaru')
            ->assertSee('Kirim Komentar')
            ->assertSee('data-share-native', false)
            ->assertDontSee('Cari berita')
            ->assertDontSee('Berita Populer');

        $this->assertSame(3, substr_count($response->getContent(), 'class="regulation-card'));
        $this->assertStringContainsString('data-profile-accordion', $response->getContent());
        $this->assertSame(4, substr_count($response->getContent(), 'data-profile-widget-toggle'));
        $this->assertSame(4, substr_count($response->getContent(), 'class="profile-widget-panel" hidden'));
    }

    public function test_resident_can_submit_a_profile_comment_without_exposing_private_fields(): void
    {
        $this->post(route('profile-desa.comment'), [
            'name' => 'Warga Sukomulyo',
            'address' => 'Dusun Sukomakmur RT 02',
            'phone' => '081234567890',
            'comment' => 'Mohon data kode pos desa diperbarui.',
            'website' => '',
        ])->assertRedirect(route('profile-desa').'#komentar')
            ->assertSessionHas('comment_success');

        $this->assertDatabaseHas('village_comments', [
            'name' => 'Warga Sukomulyo',
            'phone' => '081234567890',
            'comment' => 'Mohon data kode pos desa diperbarui.',
        ]);

        $this->get(route('profile-desa'))
            ->assertOk()
            ->assertSee('Warga Sukomulyo')
            ->assertSee('Mohon data kode pos desa diperbarui.')
            ->assertDontSee('Dusun Sukomakmur RT 02')
            ->assertDontSee('081234567890');
    }

    public function test_profile_comments_can_be_viewed_and_liked(): void
    {
        $this->get(route('profile-desa.comments'))
            ->assertOk()
            ->assertSee('Komentar Identitas Desa')
            ->assertSee('Siti Aminah');

        $comment = \App\Models\VillageComment::query()->where('name', 'Siti Aminah')->firstOrFail();

        $this->post(route('profile-desa.comments.like', $comment));

        $this->assertDatabaseHas('village_comments', [
            'id' => $comment->id,
            'like_count' => 13,
        ]);
    }

    public function test_homepage_shows_four_news_five_gallery_items_and_village_map(): void
    {
        $response = $this->get(route('beranda'))
            ->assertOk()
            ->assertSee('Berita Terbaru')
            ->assertDontSee('data-news-filter', false)
            ->assertDontSee('data-news-category', false)
            ->assertSee('Galeri Desa')
            ->assertSee('data-gallery-carousel', false)
            ->assertSee('data-carousel-position="0"', false)
            ->assertSee('data-gallery-next', false)
            ->assertDontSee('data-gallery-status', false)
            ->assertSee('Peta Desa Sukomulyo')
            ->assertSee(route('berita-desa.index'), false)
            ->assertSee(route('galeri-desa'), false)
            ->assertSee('https://www.google.com/maps?q=Desa%20Sukomulyo&output=embed', false)
            ->assertDontSee('data-home-location-map', false)
            ->assertDontSee('router.project-osrm.org', false);

        $this->assertSame(4, substr_count($response->getContent(), '<article class="article-card"'));
        $this->assertSame(5, substr_count($response->getContent(), 'data-gallery-item'));
    }

    public function test_news_page_shows_featured_layout_categories_and_five_year_archive(): void
    {
        $response = $this->get(route('berita-desa.index'))
            ->assertOk()
            ->assertSee('>Berita Utama<', false)
            ->assertSee('>Berita Terkini<', false)
            ->assertSee('id="featured-news-heading"', false)
            ->assertSee('id="latest-news-heading"', false)
            ->assertSee('<nav class="breadcrumbs"', false)
            ->assertSee('<span class="breadcrumb-separator" aria-hidden="true">/</span>', false)
            ->assertSee('page-banner--no-heading', false)
            ->assertDontSee('class="page-banner-heading"', false)
            ->assertDontSee('Semua Berita')
            ->assertDontSee('Pilihan Redaksi')
            ->assertDontSee('Kabar terbaru dan informasi penting dari Desa Sukomulyo.')
            ->assertDontSee('<p>Informasi terbaru mengenai kegiatan dan perkembangan Desa Sukomulyo.</p>', false)
            ->assertSee('>Kategori<', false)
            ->assertSee('>Arsip<', false)
            ->assertSee('Tahun 2026')
            ->assertSee('Tahun 2022')
            ->assertDontSee('Tahun 2021')
            ->assertDontSee('Filter berdasarkan bulan')
            ->assertDontSee('news-month', false)
            ->assertSee('featured-news-card--main', false)
            ->assertSee('featured-news-meta', false);

        $html = $response->getContent();
        preg_match('/<div class="news-filter-panel">(.*?)<\/aside>/s', $html, $sidebar);
        $sidebarHtml = $sidebar[1] ?? '';
        $this->assertLessThan(strpos($sidebarHtml, '>Kategori<'), strpos($sidebarHtml, 'Cari berita'));
        $this->assertLessThan(strpos($sidebarHtml, '>Arsip<'), strpos($sidebarHtml, '>Kategori<'));
        preg_match('/<section class="widget news-category-widget">(.*?)<\/section>/s', $sidebarHtml, $categoryWidget);
        $this->assertSame(5, substr_count($categoryWidget[1] ?? '', '<li>'));
        preg_match('/<section class="widget news-archive-widget">(.*?)<\/section>/s', $sidebarHtml, $archiveWidget);
        $this->assertSame(5, substr_count($archiveWidget[1] ?? '', '<li>'));
        $this->assertSame(2, substr_count($sidebarHtml, 'data-sidebar-toggle'));
        $this->assertSame(2, substr_count($sidebarHtml, 'data-sidebar-panel'));
        $this->assertSame(2, substr_count($sidebarHtml, 'aria-expanded="false"'));
        $this->assertStringContainsString('id="news-category-list"', $sidebarHtml);
        $this->assertStringContainsString('id="news-archive-list"', $sidebarHtml);

        $this->get(route('berita-desa.category', 'kemasyarakatan'))
            ->assertOk()
            ->assertSee('Kerja Bakti Menjaga Lingkungan Desa')
            ->assertSee('aria-expanded="true"', false);

        $this->get(route('berita-desa.archive', '2026'))
            ->assertOk()
            ->assertSee('aria-controls="news-archive-list"', false)
            ->assertSee('aria-expanded="true"', false);
    }

    public function test_news_pagination_shows_five_articles_per_page(): void
    {
        $this->seed();
        cache()->flush();

        $firstPage = $this->get(route('berita-desa.index'))->assertOk();
        $this->assertSame(5, substr_count($firstPage->getContent(), '<article class="article-card"'));
        $firstPage
            ->assertDontSee('Semua Berita')
            ->assertSee('>Berita Utama<', false)
            ->assertSee('>Berita Terkini<', false)
            ->assertSee('aria-label="Navigasi halaman berita"', false)
            ->assertSee('page=2', false);

        $secondPage = $this->get(route('berita-desa.index', ['page' => 2]))->assertOk();
        $this->assertSame(5, substr_count($secondPage->getContent(), '<article class="article-card"'));
        $secondPage
            ->assertDontSee('featured-news-section', false)
            ->assertDontSee('>Berita Utama<', false)
            ->assertSee('>Berita Terkini<', false)
            ->assertSee('page=1', false);
    }

    public function test_dropdown_pages_show_parent_and_child_breadcrumbs(): void
    {
        $pages = [
            route('profile-desa.detail', 'visi-misi') => ['Profile Desa', 'Visi dan Misi'],
            route('data-statistik.detail', 'pendidikan') => ['Data Statistik', 'Statistik Pendidikan'],
            route('informasi-desa.detail', 'agenda') => ['Informasi Desa', 'Agenda Desa'],
        ];

        foreach ($pages as $url => [$parent, $child]) {
            $response = $this->get($url)->assertOk();
            $html = $response->getContent();
            preg_match('/<header class="page-banner">(.*?)<\/header>/s', $html, $bannerMatches);
            $banner = $bannerMatches[1] ?? '';
            preg_match('/<nav class="breadcrumbs".*?<\/nav>/s', $html, $matches);
            $breadcrumbs = $matches[0] ?? '';

            $this->assertStringContainsString('class="breadcrumb-home"', $breadcrumbs);
            $this->assertStringContainsString('Beranda', $breadcrumbs);
            $this->assertStringContainsString($parent, $breadcrumbs);
            $this->assertStringContainsString('breadcrumb-separator', $breadcrumbs);
            $this->assertStringContainsString('aria-hidden="true">/</span>', $breadcrumbs);
            $this->assertStringNotContainsString('fa-chevron-right', $breadcrumbs);
            $this->assertStringContainsString($child, $breadcrumbs);
            $this->assertStringContainsString('class="page-banner-heading"', $banner);
            $this->assertStringContainsString('<h1>'.$child.'</h1>', $banner);
        }

        $this->get(route('galeri-desa'))
            ->assertOk()
            ->assertDontSee('<nav class="breadcrumbs"', false)
            ->assertSee('page-banner--no-breadcrumbs', false)
            ->assertSee('page-banner--no-divider', false);

        $this->get(route('berita-desa.index'))
            ->assertOk()
            ->assertSee('<nav class="breadcrumbs"', false)
            ->assertSee('page-banner--no-heading', false)
            ->assertDontSee('class="page-banner-heading"', false);

        $this->get(route('berita-desa.show', 'musyawarah-desa-penyusunan-program-kerja'))
            ->assertOk()
            ->assertSee('<nav class="breadcrumbs"', false)
            ->assertSee('class="breadcrumb-home"', false)
            ->assertSee('Musyawarah Desa Penyusunan Program Kerja');
    }

    public function test_budget_history_shows_ten_years_of_dummy_data(): void
    {
        $this->get(route('transparansi-apbdes'))
            ->assertOk()
            ->assertSee('Riwayat APBDes 10 Tahun Terakhir')
            ->assertSee('2017')
            ->assertSee('2026')
            ->assertSee('data dummy');
    }

    public function test_contact_form_validates_and_accepts_a_message(): void
    {
        $this->post(route('kontak.store'), [
            'name' => 'Warga Sukomulyo',
            'email' => 'warga@example.com',
            'phone' => '08123456789',
            'message' => 'Saya ingin meminta informasi layanan desa.',
        ])->assertRedirect()->assertSessionHas('success');

        $this->assertDatabaseHas('contact_messages', [
            'email' => 'warga@example.com',
            'message' => 'Saya ingin meminta informasi layanan desa.',
        ]);

        $this->from(route('kontak.index'))->post(route('kontak.store'), [])
            ->assertRedirect(route('kontak.index'))
            ->assertSessionHasErrors(['name', 'email', 'message']);
    }

    public function test_seo_support_routes_and_search_robots_are_available(): void
    {
        $this->get(route('sitemap'))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertSee('<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">', false)
            ->assertSee(route('beranda'), false);

        $this->get(route('robots'))
            ->assertOk()
            ->assertSee('Disallow: /admin/')
            ->assertSee('Sitemap: '.route('sitemap'));

        $this->get(route('berita-desa.search'))
            ->assertOk()
            ->assertSee('<meta name="robots" content="noindex, nofollow">', false);
    }
}
