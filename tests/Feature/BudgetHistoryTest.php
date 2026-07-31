<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BudgetHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_history_page_uses_json_data_until_2025(): void
    {
        $response = $this->get(route('transparansi-apbdes'))
            ->assertOk()
            ->assertSee('Riwayat APBDes 2019–2025')
            ->assertSee('Unduh Riwayat APBDes')
            ->assertDontSee('>Status<', false)
            ->assertDontSee('Dengan catatan')
            ->assertSee('data-budget-export-toggle="history"', false)
            ->assertDontSee('Riwayat APBDes 10 Tahun Terakhir')
            ->assertDontSee(route('transparansi-apbdes.show', 2026), false);

        foreach (range(2025, 2019) as $year) {
            $response->assertSee(route('transparansi-apbdes.show', $year), false);
        }
    }

    public function test_each_public_budget_year_has_official_detail_without_simulated_sections(): void
    {
        foreach (range(2025, 2019) as $year) {
            $this->get(route('transparansi-apbdes.show', $year))
                ->assertOk()
                ->assertSee('Detail APBDes Tahun '.$year)
                ->assertSee('data-budget-allocation-chart', false)
                ->assertSee('data-budget-comparison-chart', false)
                ->assertSee('data-statistics-copy="allocation"', false)
                ->assertSee('data-statistics-copy="comparison"', false)
                ->assertDontSee('data-budget-quarter-chart', false)
                ->assertSee('data-budget-export-toggle="detail"', false)
                ->assertSee('data-budget-export-title="Ringkasan APBDes"', false)
                ->assertSee('data-budget-export-title="Rincian Pendapatan Desa"', false)
                ->assertDontSee('data-budget-export-title="Rincian Program dan Kegiatan"', false)
                ->assertDontSee('data-budget-export-title="Rincian Realisasi Triwulanan"', false)
                ->assertSee('Pembiayaan Desa')
                ->assertDontSee('Catatan Kualitas Data')
                ->assertDontSee('Catatan Pelaksanaan dan Sumber LPPD')
                ->assertDontSee('Sumber data')
                ->assertDontSee('Data program tidak dibuat secara estimasi')
                ->assertDontSee('data triwulanan tidak dibuat secara estimasi')
                ->assertDontSee('data simulasi');
        }
    }

    public function test_missing_values_are_not_rendered_as_zero(): void
    {
        $this->get(route('transparansi-apbdes.show', 2022))
            ->assertOk()
            ->assertSee('Belum tersedia')
            ->assertSee('Rincian Pendapatan Desa');
    }

    public function test_latest_detail_uses_2025_and_non_public_years_are_not_found(): void
    {
        $this->get(route('transparansi-apbdes.show', 2025))
            ->assertOk()
            ->assertSee('Penyelenggaraan Pemerintahan Desa')
            ->assertSee('Pelaksanaan Pembangunan Desa')
            ->assertSee('Pemberdayaan Masyarakat Desa')
            ->assertSee('Dana Desa')
            ->assertSee('Penerimaan Pembiayaan')
            ->assertSee('SVG')
            ->assertSee('PDF')
            ->assertSee('JPG')
            ->assertSee('PNG');

        $this->get(route('transparansi-apbdes.show', 2018))->assertNotFound();
        $this->get(route('transparansi-apbdes.show', 2026))->assertNotFound();
    }
}
