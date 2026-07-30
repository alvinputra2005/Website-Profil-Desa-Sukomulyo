<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BudgetHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_history_page_has_aligned_content_download_and_detail_actions(): void
    {
        $response = $this->get(route('transparansi-apbdes'))
            ->assertOk()
            ->assertSee('class="container budget-history-container"', false)
            ->assertSee('data-budget-export-toggle="history"', false)
            ->assertSee('Unduh Riwayat APBDes')
            ->assertDontSee('Tren APBDes 2017–2026')
            ->assertSee('>Aksi<', false)
            ->assertSee('data-export-align="center">Persentase Realisasi</th>', false)
            ->assertSee('<th scope="col" data-budget-export-exclude>Aksi</th>', false)
            ->assertSee('<td data-budget-export-exclude>', false)
            ->assertDontSee('Transparansi Anggaran');

        foreach (range(2026, 2017) as $year) {
            $response->assertSee(route('transparansi-apbdes.show', $year), false);
        }
    }

    public function test_each_budget_year_has_a_complete_detail_page(): void
    {
        foreach (range(2026, 2017) as $year) {
            $this->get(route('transparansi-apbdes.show', $year))
                ->assertOk()
                ->assertSee('Detail APBDes Tahun '.$year)
                ->assertSee('data-budget-allocation-chart', false)
                ->assertSee('data-budget-comparison-chart', false)
                ->assertSee('data-budget-quarter-chart', false)
                ->assertSee('data-budget-export-toggle="detail"', false)
                ->assertSee('data-budget-export-title="Ringkasan APBDes"', false)
                ->assertSee('data-budget-export-title="Rincian Pendapatan Desa"', false)
                ->assertSee('data-budget-export-title="Rincian Program dan Kegiatan"', false)
                ->assertSee('data-budget-export-title="Rincian Realisasi Triwulanan"', false)
                ->assertSee('data-budget-export-title="Pembiayaan Desa"', false)
                ->assertSee('Target Pendapatan')
                ->assertSee('Pendapatan Diterima')
                ->assertDontSee('Pendapatan yang direncanakan')
                ->assertDontSee('% dari target pendapatan')
                ->assertDontSee('Perlu Perhatian')
                ->assertSee('Rincian Belanja per Bidang')
                ->assertSee('Rincian Pendapatan Desa')
                ->assertSee('Rincian Program dan Kegiatan')
                ->assertSee('Perkembangan Realisasi Triwulanan')
                ->assertSee('Pembiayaan Desa')
                ->assertDontSee('Data rinci pada halaman ini merupakan data simulasi');
        }
    }

    public function test_budget_detail_has_expected_apbdes_classifications_and_unknown_year_is_not_found(): void
    {
        $this->get(route('transparansi-apbdes.show', 2026))
            ->assertOk()
            ->assertSee('Penyelenggaraan Pemerintahan Desa')
            ->assertSee('Pelaksanaan Pembangunan Desa')
            ->assertSee('Pemberdayaan Masyarakat')
            ->assertSee('Dana Desa')
            ->assertSee('SILPA Tahun Sebelumnya')
            ->assertSee('Penyertaan Modal BUM Desa')
            ->assertSee('SVG')
            ->assertSee('PDF')
            ->assertSee('JPG')
            ->assertSee('PNG');

        $this->get(route('transparansi-apbdes.show', 2016))->assertNotFound();
    }
}
