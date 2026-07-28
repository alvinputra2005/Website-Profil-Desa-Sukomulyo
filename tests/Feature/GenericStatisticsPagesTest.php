<?php

namespace Tests\Feature;

use App\Models\StatisticDataset;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\VillageStatisticDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenericStatisticsPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_dummy_seeder_creates_five_years_for_every_statistic_context(): void
    {
        $this->seed(DatabaseSeeder::class);

        $contextCount = count(config('statistic_pages.contexts'));
        $datasets = StatisticDataset::query()
            ->where('source', VillageStatisticDemoSeeder::SOURCE)
            ->get();

        $this->assertCount($contextCount * 5, $datasets);
        $this->assertSame($contextCount, $datasets->pluck('category')->unique()->count());
        $this->assertTrue($datasets->every(fn (StatisticDataset $dataset): bool => $dataset->values()->exists()));
    }

    public function test_every_configured_statistic_page_uses_the_contextual_chart_layout(): void
    {
        $this->seed(DatabaseSeeder::class);

        foreach (config('statistic_pages.population_menus') as $menu => $context) {
            $this->get(route('data-statistik.population', ['menu' => $menu]))
                ->assertOk()
                ->assertSee(config("statistic_pages.contexts.{$context}.title"))
                ->assertSee('data-generic-statistics', false)
                ->assertSee('data-generic-series', false)
                ->assertSee('Unduh Data Aktual')
                ->assertSee('Unduh Data Tahunan')
                ->assertDontSee('Statistik Bantuan');
        }

        foreach (config('statistic_pages.section_contexts') as $section => $menus) {
            foreach ($menus as $menu => $context) {
                $parameters = ['section' => $section];
                if ($menu !== '') {
                    $parameters['menu'] = $menu;
                }

                $this->get(route('data-statistik.detail', $parameters))
                    ->assertOk()
                    ->assertSee(config("statistic_pages.contexts.{$context}.title"))
                    ->assertSee('data-generic-statistics', false)
                    ->assertDontSee('Statistik Bantuan');
            }
        }
    }
}
