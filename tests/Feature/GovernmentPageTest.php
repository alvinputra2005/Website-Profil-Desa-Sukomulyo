<?php

namespace Tests\Feature;

use Tests\TestCase;

class GovernmentPageTest extends TestCase
{
    public function test_government_page_uses_the_published_organization_image(): void
    {
        $response = $this->get(route('pemerintahan-desa'))
            ->assertOk()
            ->assertSee('government-structure-image', false)
            ->assertSee('assets/gambar-struktur-pemerintahan.png', false)
            ->assertSee('alt="Bagan struktur organisasi Pemerintah Desa Sukomulyo"', false)
            ->assertDontSee('data-org-tree', false)
            ->assertDontSee('data-org-chart', false)
            ->assertDontSee('public-organization-chart', false);

        $this->assertSame(1, substr_count($response->getContent(), 'gambar-struktur-pemerintahan.png'));
    }
}
