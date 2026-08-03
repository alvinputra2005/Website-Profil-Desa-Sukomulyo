<?php

namespace Tests\Feature;

use App\Enums\LetterApplicationStatus;
use App\Models\LetterApplication;
use App\Models\LetterService;
use App\Models\Role;
use App\Models\User;
use App\Services\Letters\LetterDocumentRequirementService;
use Database\Seeders\LetterServiceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminLetterServiceConfigurationTest extends TestCase
{
    use RefreshDatabase;

    public function test_data_admin_can_manage_dynamic_fields_and_conditional_documents(): void
    {
        $admin = $this->dataAdmin();
        $service = LetterService::factory()->create([
            'code' => 'DINAMIS',
            'icon' => 'fa-file',
            'form_schema_json' => [
                [
                    'key' => 'jenis_pengajuan',
                    'label' => 'Jenis pengajuan',
                    'type' => 'select',
                    'options' => ['baru' => 'Baru', 'hilang' => 'Hilang'],
                    'required' => true,
                ],
            ],
            'requirements_json' => [
                [
                    'key' => 'surat-kehilangan',
                    'label' => 'Surat Kehilangan',
                    'description' => 'Wajib jika hilang.',
                    'required' => false,
                    'required_when' => ['field' => 'jenis_pengajuan', 'values' => ['hilang']],
                ],
            ],
        ]);

        $this->actingAs($admin)
            ->get(route('admin.letter-services.edit', $service))
            ->assertOk()
            ->assertSee('Formulir Tambahan')
            ->assertSee('value="jenis_pengajuan"', false)
            ->assertSee('baru=Baru')
            ->assertSee('value="hilang"', false);

        $this->actingAs($admin)
            ->put(route('admin.letter-services.update', $service), [
                ...$this->basePayload($service),
                'requirements' => [
                    [
                        'code' => 'surat-kehilangan',
                        'label' => 'Surat Kehilangan dari Kepolisian',
                        'description' => 'Wajib jika pengajuan hilang.',
                        'required' => '0',
                        'condition_field' => 'jenis_pengajuan',
                        'condition_values' => 'hilang',
                    ],
                ],
                'form_fields_present' => '1',
                'form_fields' => [
                    [
                        'key' => 'jenis_pengajuan',
                        'label' => 'Jenis pengajuan dokumen',
                        'type' => 'select',
                        'options' => "baru=Baru\nhilang=Hilang",
                        'required' => '1',
                    ],
                    [
                        'key' => 'nomor_referensi',
                        'label' => 'Nomor referensi',
                        'type' => 'text',
                        'max' => '100',
                        'required' => '0',
                    ],
                    [
                        'key' => 'persetujuan',
                        'label' => 'Persetujuan pemohon',
                        'type' => 'radio',
                        'options' => "ya=Ya\ntidak=Tidak",
                        'required' => '1',
                    ],
                ],
            ])
            ->assertRedirect(route('admin.letter-services.index'))
            ->assertSessionHasNoErrors();

        $service->refresh();
        $this->assertSame('Hilang', data_get($service->form_schema_json, '0.options.hilang'));
        $this->assertFalse(data_get($service->form_schema_json, '1.required'));
        $this->assertSame(100, data_get($service->form_schema_json, '1.max'));
        $this->assertSame('jenis_pengajuan', data_get($service->requirements_json, '0.required_when.field'));
        $this->assertSame(['hilang'], data_get($service->requirements_json, '0.required_when.values'));

        $this->get(route('letter-services.application.create', $service))
            ->assertOk()
            ->assertSee('Jenis pengajuan dokumen')
            ->assertSee('Nomor referensi')
            ->assertSee('Persetujuan pemohon')
            ->assertSee('type="radio"', false);

        $application = LetterApplication::factory()->for($service, 'service')->create([
            'form_data_json' => ['jenis_pengajuan' => 'baru'],
        ]);
        $requirements = app(LetterDocumentRequirementService::class);
        $this->assertCount(0, $requirements->forApplication($application));

        $application->update(['form_data_json' => ['jenis_pengajuan' => 'hilang']]);
        $this->assertSame(['surat-kehilangan'], $requirements->forApplication($application->refresh())->pluck('key')->all());
    }

    public function test_legacy_ktp_conditions_are_shown_in_the_admin_editor(): void
    {
        $this->seed(LetterServiceSeeder::class);
        $service = LetterService::where('code', 'KTP')->firstOrFail();

        $this->actingAs($this->dataAdmin())
            ->get(route('admin.letter-services.edit', $service))
            ->assertOk()
            ->assertSee('value="jenis_pengajuan_ktp"', false)
            ->assertSee('value="hilang"', false)
            ->assertSee('hilang=Penggantian karena hilang');
    }

    public function test_admin_editor_rejects_unknown_condition_fields_and_invalid_options(): void
    {
        $admin = $this->dataAdmin();
        $service = LetterService::factory()->create(['icon' => 'fa-file']);

        $this->actingAs($admin)
            ->put(route('admin.letter-services.update', $service), [
                ...$this->basePayload($service),
                'requirements' => [[
                    'code' => 'dokumen-khusus',
                    'label' => 'Dokumen Khusus',
                    'required' => '0',
                    'condition_field' => 'field_tidak_ada',
                    'condition_values' => 'ya',
                ]],
                'form_fields_present' => '1',
                'form_fields' => [[
                    'key' => 'pilihan',
                    'label' => 'Pilihan',
                    'type' => 'select',
                    'options' => 'format-tanpa-tanda-sama-dengan',
                    'required' => '1',
                ]],
            ])
            ->assertSessionHasErrors([
                'requirements.0.condition_field',
                'form_fields.0.options',
            ]);
    }

    public function test_admin_application_index_renders_draft_without_submitted_date(): void
    {
        $application = LetterApplication::factory()->create([
            'status' => LetterApplicationStatus::Draft,
            'submitted_at' => null,
        ]);

        $this->actingAs($this->dataAdmin())
            ->get(route('admin.letter-applications.index'))
            ->assertOk()
            ->assertSee($application->application_number)
            ->assertSee('Belum dikirim')
            ->assertSee('Draf');
    }

    public function test_admin_can_filter_letter_services_by_status(): void
    {
        $admin = $this->dataAdmin();
        $active = LetterService::factory()->create(['name' => 'Layanan Aktif', 'is_active' => true]);
        $inactive = LetterService::factory()->create(['name' => 'Layanan Nonaktif', 'is_active' => false]);

        $this->actingAs($admin)
            ->get(route('admin.letter-services.index', ['status' => 'active']))
            ->assertOk()
            ->assertSee('name="status"', false)
            ->assertSee('Layanan Aktif')
            ->assertDontSee('Layanan Nonaktif');

        $this->actingAs($admin)
            ->get(route('admin.letter-services.index', ['status' => 'inactive']))
            ->assertOk()
            ->assertSee('Layanan Nonaktif')
            ->assertDontSee('Layanan Aktif');
    }

    public function test_admin_can_filter_letter_applications_by_assignee(): void
    {
        $admin = $this->dataAdmin();
        $assigned = LetterApplication::factory()->create(['assigned_to' => $admin->id]);
        $unassigned = LetterApplication::factory()->create(['assigned_to' => null]);

        $this->actingAs($admin)
            ->get(route('admin.letter-applications.index', ['assigned_to' => $admin->id]))
            ->assertOk()
            ->assertSee('name="assigned_to"', false)
            ->assertSee($assigned->application_number)
            ->assertDontSee($unassigned->application_number);

        $this->actingAs($admin)
            ->get(route('admin.letter-applications.index', ['assigned_to' => 'unassigned']))
            ->assertOk()
            ->assertSee($unassigned->application_number)
            ->assertDontSee($assigned->application_number);
    }

    public function test_admin_can_select_and_bulk_delete_letter_applications(): void
    {
        $admin = $this->dataAdmin();
        $first = LetterApplication::factory()->create(['assigned_to' => null]);
        $second = LetterApplication::factory()->create(['assigned_to' => null]);

        $this->actingAs($admin)
            ->get(route('admin.letter-applications.index'))
            ->assertOk()
            ->assertSee('data-bulk-select-all', false)
            ->assertSee('data-bulk-item', false)
            ->assertSee('Hapus Terpilih');

        $this->actingAs($admin)
            ->delete(route('admin.letter-applications.bulk-destroy'), [
                'ids' => [$first->id, $second->id],
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('letter_applications', ['id' => $first->id]);
        $this->assertDatabaseMissing('letter_applications', ['id' => $second->id]);
    }

    private function basePayload(LetterService $service): array
    {
        return [
            'name' => $service->name,
            'code' => $service->code,
            'description' => $service->description,
            'icon' => $service->icon,
            'processing_days' => 3,
            'fee_information' => 'Gratis',
            'display_order' => 0,
            'is_active' => '1',
        ];
    }

    private function dataAdmin(): User
    {
        $role = Role::factory()->create(['code' => 'admin_data']);

        return User::factory()->create(['role_id' => $role->id, 'is_active' => true]);
    }
}
