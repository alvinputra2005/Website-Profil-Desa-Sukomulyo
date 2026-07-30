<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\StatisticDataset;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BulkDeleteStatisticDatasetsTest extends TestCase
{
    use RefreshDatabase;

    public function test_data_admin_can_select_and_soft_delete_multiple_statistic_datasets(): void
    {
        $admin = $this->dataAdmin();
        $first = $this->dataset($admin, 'Dataset Satu');
        $second = $this->dataset($admin, 'Dataset Dua');

        $this->actingAs($admin)
            ->get(route('admin.resources.index', 'statistics'))
            ->assertOk()
            ->assertSee('data-statistics-select-all', false)
            ->assertSee('data-statistics-check', false)
            ->assertSee('Hapus Terpilih');

        $this->actingAs($admin)
            ->delete(route('admin.statistics.bulk-destroy'), ['ids' => [$first->id, $second->id]])
            ->assertRedirect(route('admin.resources.index', 'statistics'))
            ->assertSessionHasNoErrors();

        $this->assertSoftDeleted('statistic_datasets', ['id' => $first->id]);
        $this->assertSoftDeleted('statistic_datasets', ['id' => $second->id]);
    }

    public function test_content_admin_cannot_bulk_delete_statistic_datasets(): void
    {
        $role = Role::query()->create(['name' => 'Admin Konten', 'code' => 'admin_konten']);
        $contentAdmin = User::factory()->create(['role_id' => $role->id, 'is_active' => true]);
        $dataset = $this->dataset($contentAdmin, 'Dataset Tidak Boleh Dihapus');

        $this->actingAs($contentAdmin)
            ->delete(route('admin.statistics.bulk-destroy'), ['ids' => [$dataset->id]])
            ->assertForbidden();

        $this->assertDatabaseHas('statistic_datasets', ['id' => $dataset->id, 'deleted_at' => null]);
    }

    private function dataAdmin(): User
    {
        $role = Role::query()->create(['name' => 'Admin Data', 'code' => 'admin_data']);

        return User::factory()->create(['role_id' => $role->id, 'is_active' => true]);
    }

    private function dataset(User $user, string $title): StatisticDataset
    {
        return StatisticDataset::query()->create([
            'category' => 'kependudukan',
            'title' => $title,
            'slug' => str($title)->slug(),
            'description' => 'Dataset uji hapus massal.',
            'year' => 2021,
            'unit' => 'data',
            'visualization_type' => 'table',
            'status' => 'draft',
            'display_order' => 1,
            'created_by' => $user->id,
        ]);
    }
}
