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
            route('profile-desa') => 'Profil Desa',
            route('profile-desa.detail', ['section' => 'sejarah']) => 'Sejarah Desa',
            route('profile-desa.detail', ['section' => 'visi-misi']) => 'Visi dan Misi',
            route('pemerintahan-desa') => 'Pemerintahan Desa',
            route('potensi-desa') => 'Potensi Desa',
            route('data-desa-statistik') => 'Data Desa',
            route('data-statistik.detail', ['section' => 'penduduk']) => 'Statistik Penduduk',
            route('data-statistik.detail', ['section' => 'pendidikan']) => 'Statistik Pendidikan',
            route('data-statistik.detail', ['section' => 'pekerjaan']) => 'Statistik Pekerjaan',
            route('data-statistik.detail', ['section' => 'ekonomi']) => 'Statistik Ekonomi',
            route('data-statistik.detail', ['section' => 'idm']) => 'IDM (Indeks Desa Membangun)',
            route('data-statistik.detail', ['section' => 'visualisasi']) => 'Visualisasi Data',
            route('kependudukan') => 'Data Desa',
            route('kependudukan.detail', ['section' => 'ringkasan']) => 'Ringkasan Penduduk',
            route('kependudukan.detail', ['section' => 'jenis-kelamin']) => 'Jenis Kelamin',
            route('kependudukan.detail', ['section' => 'kelompok-umur']) => 'Kelompok Umur',
            route('kependudukan.detail', ['section' => 'pendidikan']) => 'Pendidikan',
            route('kependudukan.detail', ['section' => 'pekerjaan']) => 'Pekerjaan',
            route('kependudukan.detail', ['section' => 'agama']) => 'Agama',
            route('kependudukan.detail', ['section' => 'status-perkawinan']) => 'Status Perkawinan',
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

        $this->assertSame(4, substr_count($navigation, '<ul class="sub-menu">'));
        $this->assertSame(4, substr_count($navigation, 'class="nav-dropdown-toggle"'));
        foreach (['Profile Desa', 'Data Statistik', 'Kependudukan', 'Informasi Desa', 'Berita Desa', 'Galeri Desa', 'Statistik Pendidikan', 'Layanan Administrasi', 'APBDes'] as $label) {
            $this->assertStringContainsString($label, $navigation);
        }
        foreach (['profile-desa', 'data-desa-statistik', 'kependudukan', 'informasi-publik-desa'] as $route) {
            $this->assertDoesNotMatchRegularExpression('/<a href="'.preg_quote(route($route), '/').'"/', $navigation);
        }
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

    public function test_news_category_filter_is_not_shown(): void
    {
        $this->get(route('berita-desa.index'))
            ->assertOk()
            ->assertDontSee('data-news-filter', false);
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
}
