<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\StatisticCategory;
use App\Models\StatisticDataset;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminStatisticDatasetManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_filter_a_category_dataset_and_update_its_dynamic_rows(): void
    {
        $admin = $this->adminDataUser();
        $category = StatisticCategory::query()->create([
            'name' => 'Keluarga Berencana',
            'slug' => 'keluarga-berencana',
            'display_order' => 1,
            'is_active' => true,
        ]);
        $dataset = StatisticDataset::query()->create([
            'statistic_category_id' => $category->id,
            'dataset_key' => 'ikb_tabel_1_2021',
            'family' => 'IKB',
            'table_number' => '1',
            'period' => '2021',
            'category' => $category->slug,
            'title' => 'JUMLAH KEPALA KELUARGA PEREMPUAN MENURUT KELOMPOK UMUR',
            'short_title' => 'Kepala Keluarga Perempuan Menurut Umur',
            'slug' => 'kepala-keluarga-perempuan-2021',
            'year' => 2021,
            'unit' => 'keluarga',
            'visualization_type' => 'table',
            'status' => 'draft',
            'display_order' => 1,
            'created_by' => $admin->id,
            'columns_json' => [
                ['key' => 'area_code', 'label' => 'KODE', 'header_path' => ['KODE'], 'type' => 'string'],
                ['key' => 'area_name', 'label' => 'RW', 'header_path' => ['RW'], 'type' => 'string'],
                ['key' => 'under_15', 'label' => 'KELOMPOK UMUR / < 15', 'header_path' => ['KELOMPOK UMUR', '< 15'], 'type' => 'integer'],
                ['key' => 'age_15_19', 'label' => 'KELOMPOK UMUR / 15 - 19', 'header_path' => ['KELOMPOK UMUR', '15 - 19'], 'type' => 'integer'],
            ],
            'totals_json' => ['under_15' => 6, 'age_15_19' => 10],
        ]);
        $row = $dataset->rows()->create([
            'area_code' => '0101',
            'area_name' => 'BAKIR RW 01',
            'values_json' => ['under_15' => 2, 'age_15_19' => 4],
            'display_order' => 1,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.statistics.categories.show', [
                'category' => $category->slug,
                'data' => 'IKB:1',
                'period' => '2021',
            ]))
            ->assertOk()
            ->assertSee('Pilih Data Statistik')
            ->assertSee('KELOMPOK UMUR')
            ->assertSee('&lt; 15', false)
            ->assertSee('15 - 19')
            ->assertSee('Edit Data');

        $this->actingAs($admin)
            ->put(route('admin.statistics.categories.update', [
                'category' => $category->slug,
                'dataset' => $dataset->slug,
            ]), [
                'title' => 'Jumlah Kepala Keluarga Perempuan Menurut Kelompok Umur',
                'short_title' => 'Kepala Keluarga Perempuan Menurut Umur',
                'period' => '2022',
                'unit' => 'keluarga',
                'source' => 'Pembaharuan admin',
                'status' => 'published',
                'rows' => [[
                    'id' => $row->id,
                    'area_code' => '0101',
                    'area_name' => 'BAKIR RW 01',
                    'under_15' => '3',
                    'age_15_19' => '5',
                ]],
                'totals' => ['under_15' => '7', 'age_15_19' => '11'],
            ])
            ->assertRedirect(route('admin.statistics.categories.show', [
                'category' => $category->slug,
                'data' => 'IKB:1',
                'period' => '2022',
            ]));

        $this->assertDatabaseHas('statistic_datasets', [
            'id' => $dataset->id,
            'period' => '2022',
            'year' => 2022,
            'status' => 'published',
        ]);
        $this->assertSame(['under_15' => 3, 'age_15_19' => 5], $row->fresh()->values_json);
        $this->assertSame(['under_15' => 7, 'age_15_19' => 11], $dataset->fresh()->totals_json);

        $this->actingAs($admin)
            ->get(route('admin.statistics.categories.export.csv', [
                'category' => $category->slug,
                'dataset' => $dataset->slug,
            ]))
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $this->actingAs($admin)
            ->get(route('admin.statistics.categories.create', [
                'category' => $category->slug,
                'template' => $dataset->id,
            ]))
            ->assertOk()
            ->assertSee('Tambah Periode Statistik')
            ->assertSee('BAKIR RW 01')
            ->assertSee('nilai statistik, dan total disalin dari periode sebelumnya')
            ->assertSee('name="rows[0][under_15]" value="3"', false)
            ->assertSee('name="rows[0][age_15_19]" value="5"', false)
            ->assertSee('name="totals[under_15]" value="7"', false)
            ->assertSee('name="totals[age_15_19]" value="11"', false);

        $this->actingAs($admin)
            ->post(route('admin.statistics.categories.store', $category->slug), [
                'template_id' => $dataset->id,
                'title' => 'Dataset Baru',
                'short_title' => 'Dataset Baru',
                'period' => '2023',
                'unit' => 'keluarga',
                'status' => 'draft',
                'rows' => [[
                    'area_code' => '0101',
                    'area_name' => 'BAKIR RW 01',
                    'under_15' => '4',
                    'age_15_19' => '6',
                ]],
                'totals' => ['under_15' => '4', 'age_15_19' => '6'],
            ])
            ->assertRedirect(route('admin.statistics.categories.edit', [
                'category' => $category->slug,
                'dataset' => 'dataset-baru-2023',
            ]));

        $created = StatisticDataset::query()->where('slug', 'dataset-baru-2023')->sole();
        $this->assertSame($category->id, $created->statistic_category_id);
        $this->assertSame(['under_15' => 4, 'age_15_19' => 6], $created->rows()->sole()->values_json);
    }

    private function adminDataUser(): User
    {
        $role = Role::query()->create(['name' => 'Admin Data', 'code' => 'admin_data']);

        return User::factory()->create(['role_id' => $role->id, 'is_active' => true]);
    }
}
