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
