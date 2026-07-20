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
            route('home') => 'Desa Sukomulyo',
            route('profile') => 'Profil Desa',
            route('government') => 'Pemerintahan Desa',
            route('potentials') => 'Potensi Desa',
            route('news.index') => 'Berita Desa',
            route('news.category', 'pemerintahan') => 'Kategori: Pemerintahan',
            route('news.archive', '2026') => 'Arsip Berita 2026',
            route('news.show', 'musyawarah-desa-penyusunan-program-kerja') => 'Musyawarah Desa Penyusunan Program Kerja',
            route('search', ['q' => 'UMKM']) => 'Pelatihan Pemasaran Digital untuk UMKM',
            route('gallery') => 'Galeri Desa',
            route('contact.index') => 'Hubungi Pemerintah Desa',
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

        $this->get('/berita/artikel-tidak-ada')
            ->assertNotFound()
            ->assertSee('Halaman Tidak Ditemukan');
    }

    public function test_contact_form_validates_and_accepts_a_message(): void
    {
        $this->post(route('contact.store'), [
            'name' => 'Warga Sukomulyo',
            'email' => 'warga@example.com',
            'phone' => '08123456789',
            'message' => 'Saya ingin meminta informasi layanan desa.',
        ])->assertRedirect()->assertSessionHas('success');

        $this->assertDatabaseHas('contact_messages', [
            'email' => 'warga@example.com',
            'message' => 'Saya ingin meminta informasi layanan desa.',
        ]);

        $this->from(route('contact.index'))->post(route('contact.store'), [])
            ->assertRedirect(route('contact.index'))
            ->assertSessionHasErrors(['name', 'email', 'message']);
    }
}
