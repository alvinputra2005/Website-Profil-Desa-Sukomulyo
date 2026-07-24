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
            route('data-desa-statistik') => 'Data Desa/Statistik',
            route('informasi-publik-desa') => 'Informasi Publik Desa',
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

    public function test_homepage_shows_four_news_four_gallery_items_and_village_map(): void
    {
        $response = $this->get(route('beranda'))
            ->assertOk()
            ->assertSee('Berita Terbaru')
            ->assertSee('home-news-categories', false)
            ->assertSee('data-news-filter="pemerintahan"', false)
            ->assertSee('data-news-category="pemerintahan"', false)
            ->assertSee('Galeri Desa')
            ->assertSee('Peta Desa Sukomulyo')
            ->assertSee(route('berita-desa.index'), false)
            ->assertSee(route('galeri-desa'), false)
            ->assertSee('https://www.google.com/maps?q=Desa%20Sukomulyo&output=embed', false)
            ->assertDontSee('data-home-location-map', false)
            ->assertDontSee('router.project-osrm.org', false);

        $this->assertSame(4, substr_count($response->getContent(), '<article class="article-card"'));
        $this->assertSame(4, substr_count($response->getContent(), 'data-gallery-item'));
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
