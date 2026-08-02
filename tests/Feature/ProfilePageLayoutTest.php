<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfilePageLayoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_article_profile_pages_share_the_sidebar_and_comment_layout(): void
    {
        $pages = [
            route('profile-desa') => ['printable' => true, 'sidebar' => true],
            route('profile-desa.detail', 'sejarah') => ['printable' => true, 'sidebar' => true],
            route('profile-desa.detail', 'visi-misi') => ['printable' => true, 'sidebar' => true],
            route('peta-desa') => ['printable' => true, 'sidebar' => true],
            route('potensi-desa') => ['printable' => false, 'sidebar' => false],
        ];

        foreach ($pages as $url => $expectations) {
            $response = $this->get($url)->assertOk();
            $html = $response->getContent();

            $response->assertSee('Lihat Komentar')->assertSee('Kirim Komentar');

            if ($expectations['sidebar']) {
                $response
                    ->assertSee('Profil Pimpinan')
                    ->assertSee('Peraturan Desa')
                    ->assertSee('Kantor Desa')
                    ->assertSee('Komentar Terbaru');
            } else {
                $response
                    ->assertDontSee('Profil Pimpinan')
                    ->assertDontSee('Peraturan Desa')
                    ->assertDontSee('Komentar Terbaru');
            }

            $this->assertSame($expectations['sidebar'] ? 4 : 0, substr_count($html, 'data-profile-widget-toggle'));
            $this->assertStringNotContainsString('class="profile-comments-link"', $html);

            if ($expectations['printable']) {
                $this->assertStringContainsString('data-print-article', $html);
            } else {
                $this->assertStringNotContainsString('data-print-article', $html);
            }
        }
    }

    public function test_government_page_focuses_on_the_chart_without_profile_sidebar(): void
    {
        $response = $this->get(route('pemerintahan-desa'))
            ->assertOk()
            ->assertSee('data-government-organization-root', false)
            ->assertDontSee('government-structure-image', false)
            ->assertSee('Kirim Komentar')
            ->assertDontSee('Profil Pimpinan')
            ->assertDontSee('Peraturan Desa')
            ->assertDontSee('Komentar Terbaru')
            ->assertDontSee('data-share-native', false);

        $this->assertSame(0, substr_count($response->getContent(), 'data-profile-widget-toggle'));
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
            ->assertSee('Tulis Komentar')
            ->assertSee('<strong>1</strong>', false);

        $this->get(route('profile-desa.section-comments', 'potensi-desa'))
            ->assertOk()
            ->assertSee('Komentar Potensi Desa')
            ->assertSee('Warga Potensi');

        $this->get(route('profile-desa.section-comments', 'sejarah'))
            ->assertOk()
            ->assertDontSee('Warga Potensi');
    }

    public function test_potential_page_lists_two_alternating_tourism_sections_with_static_images_and_location_links(): void
    {
        $response = $this->get(route('potensi-desa'))
            ->assertOk()
            ->assertSee('Taman Merak Pujon')
            ->assertSee('Coban Manan')
            ->assertSee('Dusun Bakir, Desa Sukomulyo')
            ->assertSee('Dusun Talasan, Desa Sukomulyo')
            ->assertSee('village-tourism-section--reversed', false)
            ->assertSee('potensi-taman-merak-gambar', false)
            ->assertSee('potensi-coban-manan-gambar', false)
            ->assertSee('village-tourism-static-toggle', false)
            ->assertSee('village-tourism-location-below', false)
            ->assertSee('https://www.google.com/maps/search/', false);

        $response->assertDontSee('data-share-native', false);
        $this->assertSame(2, substr_count($response->getContent(), 'class="village-tourism-section '));
        $this->assertSame(2, substr_count($response->getContent(), 'village-tourism-static-toggle'));
        $this->assertStringNotContainsString('data-sidebar-accordion-toggle', $response->getContent());
    }
}
