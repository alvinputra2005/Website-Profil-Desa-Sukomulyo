<?php

namespace Tests\Feature;

use Tests\TestCase;

class GovernmentPageTest extends TestCase
{
    public function test_government_page_uses_the_interactive_organization_chart(): void
    {
        $response = $this->get(route('pemerintahan-desa'))
            ->assertOk()
            ->assertSee('data-government-organization-root', false)
            ->assertSee('Memuat struktur pemerintahan interaktif...', false)
            ->assertSee('Aktifkan JavaScript untuk melihat struktur pemerintahan interaktif.', false)
            ->assertDontSee('assets/gambar-struktur-pemerintahan.png', false)
            ->assertDontSee('data-org-tree', false)
            ->assertDontSee('data-org-chart', false)
            ->assertDontSee('public-organization-chart', false);

        $this->assertSame(1, substr_count($response->getContent(), 'data-government-organization-root'));
    }

    public function test_interactive_organization_chart_defines_all_thirteen_officials(): void
    {
        $officials = file_get_contents(resource_path('js/data/governmentOfficials.ts'));

        $this->assertNotFalse($officials);
        $this->assertSame(13, preg_match_all("/^\s+id: '[^']+',$/m", $officials));
        $this->assertStringContainsString("name: 'Safiul Anwar, ST'", $officials);
        $this->assertStringContainsString("name: 'Cahyo Utomo'", $officials);
        $this->assertStringContainsString("DEFAULT_OFFICIAL_AVATAR = '/assets/male-resident-avatar.jpg'", $officials);
    }
}
