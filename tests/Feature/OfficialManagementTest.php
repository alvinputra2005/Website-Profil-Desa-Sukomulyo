<?php

namespace Tests\Feature;

use App\Models\Official;
use App\Models\Resident;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OfficialManagementTest extends TestCase
{
    use RefreshDatabase;

    private function admin(string $roleCode = 'super_admin'): User
    {
        $role = Role::create(['name' => 'Administrator', 'code' => $roleCode]);

        return User::factory()->create(['role_id' => $role->id, 'is_active' => true]);
    }

    public function test_official_can_be_created_from_resident_and_identity_is_synchronized(): void
    {
        $resident = Resident::create([
            'nik' => '1301010101010001',
            'name' => 'Siti Aminah',
            'sex' => 'P',
            'birth_place' => 'Sukomulyo',
            'birth_date' => '1988-04-12',
            'religion' => 'Islam',
            'education' => 'Diploma IV/Strata I',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->admin())->post(route('admin.officials.store'), [
            'source' => 'resident',
            'resident_id' => $resident->id,
            'name' => 'Tidak boleh dipakai',
            'position' => 'Kepala Desa',
            'display_order' => 1,
            'is_active' => 1,
            'attendance_enabled' => 1,
        ]);

        $response->assertSessionHasNoErrors();
        $official = Official::firstOrFail();
        $response->assertRedirect(route('admin.officials.edit', $official));
        $this->assertSame($resident->id, $official->resident_id);
        $this->assertSame('Siti Aminah', $official->name);
        $this->assertSame('1301010101010001', $official->nik);
        $this->assertSame('Sukomulyo', $official->birth_place);
        $this->assertSame('P', $official->sex);
    }

    public function test_full_official_workflow_filters_toggles_prints_and_exports(): void
    {
        $admin = $this->admin();
        $payload = [
            'source' => 'external',
            'name' => 'Budi Santoso',
            'title_suffix' => 'S.E.',
            'nik' => '1301010101010002',
            'village_employee_number' => 'NIPD-002',
            'nip' => '198001012010011001',
            'id_card_tag' => 'CARD-002',
            'birth_place' => 'Padang',
            'birth_date' => '1980-01-01',
            'sex' => 'L',
            'education' => 'Diploma IV/Strata I',
            'religion' => 'Islam',
            'rank_grade' => 'III/a',
            'position' => 'Sekretaris Desa',
            'appointment_decree' => 'SK/02/2026',
            'appointment_date' => '2026-01-02',
            'term' => '2026–2032',
            'organization_level' => 2,
            'organization_offset' => 0,
            'organization_layout' => 'hanging',
            'organization_color' => '#526b42',
            'display_order' => 2,
            'is_active' => 1,
            'attendance_enabled' => 1,
            'facebook' => 'https://facebook.com/budi',
        ];

        $this->actingAs($admin)->post(route('admin.officials.store'), $payload)->assertSessionHasNoErrors();
        $official = Official::firstOrFail();

        $this->get(route('admin.officials.index', ['q' => 'NIPD-002', 'status' => 'active']))
            ->assertOk()
            ->assertSee('Budi Santoso')
            ->assertSee('SK/02/2026');
        $this->patch(route('admin.officials.toggle-status', $official))->assertSessionHasNoErrors();
        $this->assertFalse($official->fresh()->is_active);
        $this->patch(route('admin.officials.toggle-attendance', $official))->assertSessionHasNoErrors();
        $this->assertFalse($official->fresh()->attendance_enabled);
        $this->get(route('admin.officials.organization'))->assertOk();
        $this->get(route('admin.officials.print', ['status' => 'all']))->assertOk()->assertSee('Budi Santoso');
        $this->get(route('admin.officials.export'))
            ->assertOk()
            ->assertDownload()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function test_official_module_rejects_invalid_dates_duplicate_cards_and_descendant_as_superior(): void
    {
        $admin = $this->admin();
        $superior = Official::create(['name' => 'Atasan', 'position' => 'Kepala Desa', 'display_order' => 1]);
        $subordinate = Official::create(['name' => 'Bawahan', 'position' => 'Sekretaris Desa', 'superior_id' => $superior->id, 'display_order' => 2]);
        Official::create(['name' => 'Pemegang Kartu', 'position' => 'Kaur', 'id_card_tag' => 'SAMA', 'display_order' => 3]);

        $this->actingAs($admin)->put(route('admin.officials.update', $superior), [
            'source' => 'external',
            'name' => 'Atasan',
            'position' => 'Kepala Desa',
            'superior_id' => $subordinate->id,
            'appointment_date' => '2026-04-01',
            'dismissal_date' => '2026-03-01',
            'id_card_tag' => 'SAMA',
            'display_order' => 1,
        ])->assertSessionHasErrors(['superior_id', 'dismissal_date', 'id_card_tag']);
    }
}
