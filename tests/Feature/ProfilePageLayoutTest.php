<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfilePageLayoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_profile_pages_share_the_sidebar_and_comment_layout(): void
    {
        $pages = [
            route('profile-desa') => true,
            route('profile-desa.detail', 'sejarah') => true,
            route('profile-desa.detail', 'visi-misi') => true,
            route('pemerintahan-desa') => false,
            route('peta-desa') => true,
            route('potensi-desa') => true,
        ];

        foreach ($pages as $url => $isPrintable) {
            $response = $this->get($url)->assertOk();
            $html = $response->getContent();

            $response
                ->assertSee('Profil Pimpinan')
                ->assertSee('Peraturan Desa')
                ->assertSee('Kantor Desa')
                ->assertSee('Komentar Terbaru')
                ->assertSee('Lihat Komentar')
                ->assertSee('Kirim Komentar');

            $this->assertSame(4, substr_count($html, 'data-profile-widget-toggle'));
            $this->assertStringNotContainsString('class="profile-comments-link"', $html);

            if ($isPrintable) {
                $this->assertStringContainsString('data-print-article', $html);
            } else {
                $this->assertStringNotContainsString('data-print-article', $html);
            }
        }
    }

    public function test_comments_are_counted_and_listed_per_profile_page(): void
    {
        $this->post(route('profile-desa.comment'), [
            'page_key' => 'potensi-desa',
            'name' => 'Warga Potensi',
            'address' => 'Dusun Sukomulyo',
            'phone' => '081234567890',
            'comment' => 'Mohon potensi UMKM terus diperbarui.',
            'website' => '',
        ])->assertRedirect(route('potensi-desa').'#komentar');

        $this->assertDatabaseHas('village_comments', [
            'page_key' => 'potensi-desa',
            'name' => 'Warga Potensi',
        ]);

        $this->get(route('potensi-desa'))
            ->assertOk()
            ->assertSee('Warga Potensi')
            ->assertSee('Mohon potensi UMKM terus diperbarui.')
            ->assertSee('<strong>1</strong>', false);

        $this->get(route('profile-desa.section-comments', 'potensi-desa'))
            ->assertOk()
            ->assertSee('Komentar Potensi Desa')
            ->assertSee('Warga Potensi');

        $this->get(route('profile-desa.section-comments', 'sejarah'))
            ->assertOk()
            ->assertDontSee('Warga Potensi');
    }
}
