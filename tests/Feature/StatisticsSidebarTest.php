<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StatisticsSidebarTest extends TestCase
{
    use RefreshDatabase;

    public function test_statistics_subpages_show_the_scrollable_accordion_sidebar(): void
    {
        $this->get(route('data-statistik.population'))
            ->assertOk()
            ->assertSee('aria-label="Navigasi data statistik"', false)
            ->assertSee('data-no-scroll-reveal', false)
            ->assertSee('data-sidebar-accordion', false)
            ->assertSee('Statistik Penduduk')
            ->assertSee('Statistik Keluarga')
            ->assertSee('Statistik Bantuan')
            ->assertSee('Rentang Umur')
            ->assertSee('Diperbarui 28 Juli 2026')
            ->assertSee('Pemerintah Desa Sukomulyo')
            ->assertSee('data-population-download', false)
            ->assertSee('Unduh Data');

        $this->get(route('data-statistik.detail', ['section' => 'pendidikan']))
            ->assertOk()
            ->assertSee('aria-label="Navigasi data statistik"', false);
    }

    public function test_statistics_landing_page_stays_full_width_without_the_sidebar(): void
    {
        $this->get(route('data-desa-statistik'))
            ->assertOk()
            ->assertDontSee('aria-label="Navigasi data statistik"', false)
            ->assertDontSee('data-sidebar-accordion', false);
    }
}
