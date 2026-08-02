<?php

namespace Tests\Feature;

use App\Models\LetterService;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LetterServiceIconTest extends TestCase
{
    use RefreshDatabase;

    public function test_birth_certificate_uses_a_supported_selectable_icon(): void
    {
        $this->seed(DatabaseSeeder::class);

        $service = LetterService::where('code', 'SKL')->firstOrFail();

        $this->assertSame('fa-birthday-cake', $service->icon);
        $this->get(route('letter-services.index'))
            ->assertOk()
            ->assertSee('fas fa-birthday-cake', false)
            ->assertDontSee('fa-baby', false);
    }

    public function test_sktm_service_uses_the_surat_keterangan_label(): void
    {
        $this->seed(DatabaseSeeder::class);

        $service = LetterService::where('code', 'SKTM')->firstOrFail();

        $this->assertSame('Surat Keterangan', $service->name);
        $this->assertSame('SKTM', LetterService::orderBy('display_order')->value('code'));
        $this->get(route('letter-services.index'))
            ->assertOk()
            ->assertSee('Surat Keterangan')
            ->assertSee('Untuk SKTM dan kebutuhan administrasi lainnya.')
            ->assertSee('letter-choice--featured', false)
            ->assertDontSee('Surat Keterangan Tidak Mampu');
    }

    public function test_data_admin_can_select_an_icon_for_a_letter_service(): void
    {
        $role = Role::factory()->create(['code' => 'admin_data']);
        $admin = User::factory()->create(['role_id' => $role->id, 'is_active' => true]);
        $service = LetterService::factory()->create(['icon' => 'fa-file']);

        $this->actingAs($admin)
            ->get(route('admin.letter-services.edit', $service))
            ->assertOk()
            ->assertSee('Ikon layanan')
            ->assertSee('value="fa-birthday-cake"', false);

        $this->actingAs($admin)
            ->put(route('admin.letter-services.update', $service), [
                'name' => $service->name,
                'code' => $service->code,
                'description' => $service->description,
                'icon' => 'fa-birthday-cake',
                'requirements' => [
                    ['code' => 'kartu-keluarga', 'label' => 'Kartu Keluarga', 'required' => '1'],
                ],
                'processing_days' => 3,
                'fee_information' => 'Gratis',
                'display_order' => 0,
                'is_active' => '1',
            ])
            ->assertRedirect(route('admin.letter-services.index'));

        $this->assertSame('fa-birthday-cake', $service->fresh()->icon);
    }
}
