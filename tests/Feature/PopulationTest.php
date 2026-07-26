<?php

namespace Tests\Feature;

use App\Models\PopulationGroup;
use App\Models\Resident;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PopulationTest extends TestCase
{
    use RefreshDatabase;

    public function test_population_workflow_covers_input_grouping_statistics_and_report(): void
    {
        $role = Role::create(['name' => 'Admin Data', 'code' => 'admin_data']);
        $admin = User::factory()->create(['role_id' => $role->id, 'is_active' => true]);
        $this->actingAs($admin);

        $payload = [
            'nik' => '3300000000000001',
            'name' => 'Siti Sukomulyo',
            'sex' => 'P',
            'birth_place' => 'Sukomulyo',
            'birth_date' => '1990-01-01',
            'religion' => 'Islam',
            'marital_status' => 'Kawin',
            'citizenship' => 'WNI',
            'education' => 'SLTA/Sederajat',
            'occupation' => 'Petani/Pekebun',
            'resident_status' => 'permanent',
            'status' => 'active',
            'family_relationship' => 'Kepala Keluarga',
            'household_relationship' => 'Kepala Rumah Tangga',
            'hamlet' => 'Sukomulyo',
            'rw' => '1',
            'rt' => '2',
            'current_address' => 'Jl. Desa No. 1',
            'initial_event_type' => 'birth',
            'event_date' => now()->startOfMonth()->format('Y-m-d'),
            'registered_at' => now()->format('Y-m-d'),
        ];
        $this->post(route('admin.population.residents.store'), $payload)
            ->assertRedirect()
            ->assertSessionHasNoErrors();
        $resident = Resident::firstOrFail();

        $this->post(route('admin.population.families.store'), [
            'family_card_number' => '3300000000000002',
            'head_resident_id' => $resident->id,
            'hamlet' => 'Sukomulyo',
            'rw' => '1',
            'rt' => '2',
            'address' => 'Jl. Desa No. 1',
            'is_active' => '1',
        ])->assertRedirect()->assertSessionHasNoErrors();
        $this->assertDatabaseHas('families', ['family_card_number' => '3300000000000002', 'head_resident_id' => $resident->id]);

        $this->post(route('admin.population.households.store'), [
            'household_number' => 'RTM-001',
            'head_resident_id' => $resident->id,
            'hamlet' => 'Sukomulyo',
            'rw' => '1',
            'rt' => '2',
            'address' => 'Jl. Desa No. 1',
            'is_active' => '1',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->post(route('admin.population.groups.store'), [
            'code' => 'PKK-01',
            'name' => 'PKK Sukomulyo',
            'category' => 'PKK',
            'chairperson_id' => $resident->id,
            'is_active' => '1',
        ])->assertRedirect()->assertSessionHasNoErrors();
        $group = PopulationGroup::firstOrFail();
        $this->assertDatabaseHas('population_group_members', ['group_id' => $group->id, 'resident_id' => $resident->id, 'position' => 'Ketua']);

        $this->get(route('admin.population.statistics'))->assertOk()->assertSee('Statistik Kependudukan');
        $this->get(route('admin.population.report'))->assertOk()->assertSee('Laporan Kependudukan Bulanan');
        $this->get(route('admin.population.report.export'))->assertOk()->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $this->get(route('data-desa-statistik'))->assertOk()->assertSee('Jumlah Penduduk');
        $this->get(route('beranda'))
            ->assertOk()
            ->assertSee('Data Pendidikan')
            ->assertSee('Data Pekerjaan')
            ->assertSee('fas fa-graduation-cap', false)
            ->assertSee('fas fa-briefcase', false)
            ->assertDontSee('Wilayah Administratif')
            ->assertDontSee('Luas Wilayah');
        $this->get(route('laporan-penduduk'))->assertOk()->assertSee('Laporan Penduduk');
    }
}
