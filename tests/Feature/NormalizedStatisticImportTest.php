<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\StatisticDataset;
use App\Models\StatisticImport;
use App\Models\StatisticRow;
use App\Models\User;
use App\Services\Statistics\NormalizedStatisticsValidator;
use Database\Seeders\StatisticCategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class NormalizedStatisticImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_validate_preview_and_import_normalized_statistics_idempotently(): void
    {
        Storage::fake('local');
        $admin = $this->adminDataUser();
        $this->seed(StatisticCategorySeeder::class);

        $legacyDataset = StatisticDataset::query()->create([
            'category' => 'penduduk',
            'title' => 'Dataset Lama',
            'slug' => 'dataset-lama',
            'description' => 'Data yang sudah ada sebelum importer baru.',
            'year' => 2026,
            'unit' => 'jiwa',
            'visualization_type' => 'bar',
            'status' => 'published',
            'display_order' => 0,
            'created_by' => $admin->id,
        ]);

        $sourcePath = base_path('storage/app/public/statistics/sensus_normalized.json');
        $response = $this->actingAs($admin)->post(
            route('admin.statistics.import.store'),
            [
                'statistics_file' => new UploadedFile(
                    $sourcePath,
                    'sensus_normalized.json',
                    'application/json',
                    null,
                    true,
                ),
            ],
        );

        $import = StatisticImport::query()->sole();
        $response->assertRedirect(route('admin.statistics.import.preview', $import->public_id));

        $this->assertSame('validated', $import->status);
        $this->assertSame('normalized_census_statistics', $import->schema_name);
        $this->assertSame('2.0', $import->schema_version);
        $this->assertSame(48, $import->total_datasets);
        $this->assertSame(617, $import->total_rows);
        $this->assertCount(3, $import->warnings_json);
        $this->assertSame([], $import->errors_json);

        $this->get(route('admin.statistics.import.preview', $import->public_id))
            ->assertOk()
            ->assertSee('48')
            ->assertSee('617')
            ->assertSee('ikb_tabel_13_2021')
            ->assertSee('Import Dataset Terpilih');

        $validation = app(NormalizedStatisticsValidator::class)->validate($sourcePath);
        $selections = collect($validation['datasets'])
            ->map(fn (array $dataset): array => [
                'dataset_id' => $dataset['dataset_id'],
                'selected' => '1',
                'category_slug' => $dataset['suggested_category'],
                'short_title' => $dataset['short_title'],
                'status' => 'draft',
            ])
            ->all();

        $this->post(
            route('admin.statistics.import.process', $import->public_id),
            ['datasets' => $selections],
        )
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.statistics.import.create'));

        $this->assertSame(48, StatisticDataset::query()->whereNotNull('statistic_import_id')->count());
        $this->assertSame(49, StatisticDataset::query()->count());
        $this->assertSame(617, StatisticRow::query()->count());
        $this->assertTrue(StatisticDataset::query()->whereKey($legacyDataset->id)->exists());

        $reviewDataset = StatisticDataset::query()
            ->where('dataset_key', 'ikb_tabel_13_2021')
            ->sole();
        $this->assertTrue($reviewDataset->requires_manual_review);
        $this->assertSame('needs_review', $reviewDataset->status);

        $firstRow = StatisticDataset::query()
            ->where('dataset_key', 'ik_tabel_1_2021')
            ->sole()
            ->rows()
            ->firstOrFail();
        $this->assertSame('0101', $firstRow->area_code);
        $this->assertSame('BAKIR RW 01', $firstRow->area_name);

        $this->post(
            route('admin.statistics.import.process', $import->public_id),
            ['datasets' => $selections],
        )->assertSessionHasNoErrors();

        $this->assertSame(48, StatisticDataset::query()->whereNotNull('statistic_import_id')->count());
        $this->assertSame(617, StatisticRow::query()->count());
        $this->assertSame('completed', $import->fresh()->status);
    }

    public function test_invalid_schema_is_recorded_and_cannot_be_processed(): void
    {
        Storage::fake('local');
        $admin = $this->adminDataUser();

        $response = $this->actingAs($admin)->post(
            route('admin.statistics.import.store'),
            [
                'statistics_file' => UploadedFile::fake()->createWithContent(
                    'invalid.json',
                    json_encode([
                        'metadata' => ['schema' => ['name' => 'other_schema', 'version' => '1.0']],
                        'datasets' => [],
                    ], JSON_THROW_ON_ERROR),
                ),
            ],
        );

        $import = StatisticImport::query()->sole();
        $response->assertRedirect(route('admin.statistics.import.preview', $import->public_id));
        $this->assertSame('invalid', $import->status);
        $this->assertNotEmpty($import->errors_json);

        $this->get(route('admin.statistics.import.preview', $import->public_id))
            ->assertOk()
            ->assertSee('File belum dapat diimpor');

        $this->post(
            route('admin.statistics.import.process', $import->public_id),
            ['datasets' => []],
        )->assertSessionHasErrors();

        $this->assertSame(0, StatisticDataset::query()->whereNotNull('statistic_import_id')->count());
        $this->assertSame(0, StatisticRow::query()->count());
    }

    private function adminDataUser(): User
    {
        $role = Role::query()->create([
            'name' => 'Admin Data',
            'code' => 'admin_data',
        ]);

        return User::factory()->create([
            'role_id' => $role->id,
            'is_active' => true,
        ]);
    }
}
