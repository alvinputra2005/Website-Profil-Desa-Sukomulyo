<?php

namespace Tests\Feature;

use App\Models\InventoryItem;
use App\Models\InventoryMutation;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_data_admin_can_manage_inventory_and_record_mutation(): void
    {
        $admin = $this->user('admin_data');

        $this->actingAs($admin)->get(route('admin.inventory.index', 'tanah'))
            ->assertOk()->assertSee('Inventaris Tanah')->assertSee('Laporan Semua Aset');

        $response = $this->post(route('admin.inventory.store', 'tanah'), [
            'name' => 'Tanah Kantor Desa', 'item_code' => '01.01.01', 'register_number' => '000001',
            'acquisition_year' => 2020, 'origin' => 'Pembelian Sendiri', 'value' => 250000000,
            'quantity' => 1, 'condition' => 'Baik', 'notes' => 'Aset desa',
            'details' => ['area' => 1250, 'location' => 'Dusun Krajan', 'right_type' => 'Hak Milik', 'usage' => 'Kantor desa'],
        ]);

        $item = InventoryItem::firstOrFail();
        $response->assertRedirect(route('admin.inventory.index', 'tanah'));
        $this->assertSame('tanah', $item->category);
        $this->assertSame(1250, $item->details['area']);
        $this->assertSame($admin->id, $item->updated_by);

        $this->post(route('admin.inventory.mutations.store', ['tanah', $item]), [
            'asset_status' => 'Hapus', 'mutation_type' => 'Masih Baik Dijual',
            'mutation_date' => now()->toDateString(), 'sale_price' => 275000000,
            'notes' => 'Dijual melalui prosedur penghapusan aset.',
        ])->assertRedirect(route('admin.inventory.show', ['tanah', $item]));

        $this->assertDatabaseHas('inventory_mutations', ['inventory_item_id' => $item->id, 'mutation_type' => 'Masih Baik Dijual']);
        $this->assertSame('Dimutasi', $item->fresh()->status);

        $this->get(route('admin.inventory.report'))->assertOk()
            ->assertSee('Tanah Kantor Desa')->assertSee('250.000.000');
        $this->get(route('admin.inventory.report.print'))->assertOk()->assertSee('BUKU INVENTARIS DAN KEKAYAAN DESA');
        $this->get(route('admin.inventory.report.csv'))->assertOk()->assertDownload();
    }

    public function test_inventory_code_and_register_must_be_unique_in_a_category(): void
    {
        $admin = $this->user('admin_data');
        InventoryItem::create([
            'category' => 'peralatan', 'name' => 'Laptop', 'item_code' => '02.01', 'register_number' => '0001',
            'acquisition_year' => 2025, 'origin' => 'Bantuan Pemerintah', 'value' => 10000000,
            'quantity' => 1, 'condition' => 'Baik', 'status' => 'Aktif', 'updated_by' => $admin->id,
        ]);

        $this->actingAs($admin)->from(route('admin.inventory.create', 'peralatan'))
            ->post(route('admin.inventory.store', 'peralatan'), [
                'name' => 'Laptop lain', 'item_code' => '02.01', 'register_number' => '0001',
                'acquisition_year' => 2025, 'origin' => 'Bantuan Pemerintah', 'value' => 9000000,
                'quantity' => 1, 'condition' => 'Baik',
            ])->assertRedirect(route('admin.inventory.create', 'peralatan'))->assertSessionHasErrors('register_number');
    }

    public function test_content_admin_cannot_access_inventory(): void
    {
        $this->actingAs($this->user('admin_konten'))->get(route('admin.inventory.report'))->assertForbidden();
    }

    private function user(string $code): User
    {
        $role = Role::create(['name' => $code, 'code' => $code]);
        return User::factory()->create(['role_id' => $role->id, 'is_active' => true]);
    }
}
