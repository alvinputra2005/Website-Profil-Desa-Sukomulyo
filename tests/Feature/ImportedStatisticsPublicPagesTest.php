<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\StatisticCategory;
use App\Models\StatisticDataset;
use App\Models\StatisticImport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ImportedStatisticsPublicPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_published_imported_datasets_are_listed_in_public_navigation_and_category_page(): void
    {
        [$category, $dataset] = $this->publishedDataset();
        $this->draftDataset($category);

        $this->get(route('data-desa-statistik'))
            ->assertOk()
            ->assertSee('Dataset Statistik Terpublikasi')
            ->assertSee('Kependudukan')
            ->assertSee(route('data-statistik.detail', ['section' => $category->slug]), false)
            ->assertDontSee('Dataset Draf');

        $this->get(route('data-statistik.detail', ['section' => $category->slug]))
            ->assertOk()
            ->assertSee('Dataset Penduduk Menurut RW')
            ->assertSee('data-generic-statistics', false)
            ->assertDontSee('statistics-dataset-picker', false)
            ->assertDontSee('data-imported-dataset-selector', false)
            ->assertSee('Pilih visualisasi / tampilan data')
            ->assertSee('Grafik Data')
            ->assertSee('Tabel Data')
            ->assertSee('data-sidebar-accordion', false)
            ->assertSee('statistics-sidebar__dataset-link is-active', false)
            ->assertSee('data-generic-current-chart', false)
            ->assertSee('data-generic-trend-chart', false)
            ->assertSee('data-generic-current-table', false)
            ->assertSee('0101')
            ->assertSee('BAKIR RW 01')
            ->assertSee('1.234')
            ->assertSee('42,50%')
            ->assertDontSee('Lihat Tabel Lengkap')
            ->assertSee('Navigasi data statistik')
            ->assertDontSee('Dataset Draf');

        $this->get(route('data-statistik.detail', [
            'section' => $category->slug,
            'dataset' => 'ik-1',
            'display' => 'table',
        ]))
            ->assertOk()
            ->assertSee('statistics-display-option is-active', false)
            ->assertSee('population-summary-table-wrap statistics-view-panel" >', false)
            ->assertSee('population-composition-grid statistics-view-panel"  hidden', false);
    }

    public function test_published_dataset_renders_generic_rows_and_keeps_draft_dataset_private(): void
    {
        [$category, $dataset] = $this->publishedDataset();
        $draft = $this->draftDataset($category);

        $this->get(route('data-statistik.imported.show', [
            'category' => $category->slug,
            'dataset' => $dataset->slug,
        ]))
            ->assertOk()
            ->assertSee('KODE')
            ->assertSee('RW')
            ->assertSee('Jumlah Penduduk')
            ->assertSee('0101')
            ->assertSee('BAKIR RW 01')
            ->assertSee('1.234')
            ->assertSee('42,50%')
            ->assertSee('sensus-uji.json');

        $this->get(route('data-statistik.imported.show', [
            'category' => $category->slug,
            'dataset' => $draft->slug,
        ]))->assertNotFound();
    }

    /**
     * @return array{StatisticCategory, StatisticDataset}
     */
    private function publishedDataset(): array
    {
        $role = Role::query()->create(['name' => 'Admin Data', 'code' => 'admin_data']);
        $user = User::factory()->create(['role_id' => $role->id, 'is_active' => true]);
        $category = StatisticCategory::query()->create([
            'name' => 'Kependudukan',
            'slug' => 'kependudukan',
            'icon' => 'fa-users',
            'display_order' => 1,
            'is_active' => true,
        ]);
        $import = StatisticImport::query()->create([
            'original_filename' => 'sensus-uji.json',
            'file_path' => 'statistics/imports/sensus-uji.json',
            'file_sha256' => str_repeat('a', 64),
            'schema_name' => 'normalized_census_statistics',
            'schema_version' => '2.0',
            'status' => 'completed',
            'total_datasets' => 1,
            'total_rows' => 1,
            'imported_by' => $user->id,
        ]);
        $dataset = StatisticDataset::query()->create([
            'statistic_import_id' => $import->id,
            'statistic_category_id' => $category->id,
            'dataset_key' => 'uji_penduduk_2021',
            'family' => 'IK',
            'table_number' => '1',
            'period' => '2021',
            'category' => $category->slug,
            'title' => 'DATASET PENDUDUK MENURUT RW',
            'short_title' => 'Dataset Penduduk Menurut RW',
            'slug' => 'penduduk-menurut-rw-2021',
            'description' => 'Dataset uji.',
            'year' => 2021,
            'unit' => 'jiwa',
            'visualization_type' => 'table',
            'source' => 'sensus-uji.json',
            'source_metadata_json' => ['primary_file' => 'sensus-uji.json'],
            'columns_json' => [
                ['key' => 'area_code', 'label' => 'KODE', 'type' => 'string'],
                ['key' => 'area_name', 'label' => 'RW', 'type' => 'string'],
                ['key' => 'jumlah', 'label' => 'Jumlah Penduduk', 'type' => 'integer'],
                ['key' => 'persentase', 'label' => 'Persentase', 'type' => 'percentage'],
            ],
            'status' => 'published',
            'display_order' => 1,
            'created_by' => $user->id,
        ]);
        $dataset->rows()->create([
            'area_code' => '0101',
            'area_name' => 'BAKIR RW 01',
            'values_json' => ['jumlah' => 1234, 'persentase' => 42.5],
            'display_order' => 1,
        ]);

        return [$category, $dataset];
    }

    private function draftDataset(StatisticCategory $category): StatisticDataset
    {
        return StatisticDataset::query()->create([
            'statistic_import_id' => StatisticImport::query()->firstOrFail()->id,
            'statistic_category_id' => $category->id,
            'dataset_key' => 'uji_draf_2021',
            'family' => 'IK',
            'table_number' => '2',
            'period' => '2021',
            'category' => $category->slug,
            'title' => 'DATASET DRAF',
            'short_title' => 'Dataset Draf',
            'slug' => 'dataset-draf-2021',
            'description' => 'Dataset draf.',
            'year' => 2021,
            'unit' => 'data',
            'visualization_type' => 'table',
            'status' => 'draft',
            'display_order' => 2,
            'created_by' => User::query()->firstOrFail()->id,
        ]);
    }
}
