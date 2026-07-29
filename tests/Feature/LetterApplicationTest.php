<?php

namespace Tests\Feature;

use App\Enums\LetterApplicationStatus;
use App\Models\LetterApplication;
use App\Models\LetterService;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LetterApplicationTest extends TestCase
{
    use RefreshDatabase;

    public function test_resident_can_submit_and_track_with_hashed_secrets(): void
    {
        $service = LetterService::factory()->create();
        $response = $this->post(route('letter-services.application.store', $service), [
            'applicant_name' => 'Budi Santoso',
            'applicant_nik' => '3514123456789012',
            'applicant_phone' => '081234567890',
            'address' => 'Dusun Sukomulyo RT 001 RW 001',
            'purpose' => 'Mengurus administrasi usaha',
            'declaration' => '1',
            'website' => '',
        ]);

        $application = LetterApplication::firstOrFail();
        $response->assertRedirect();
        $this->assertSame(LetterApplicationStatus::Submitted, $application->status);
        $this->assertSame('3514123456789012', $application->applicant_nik);
        $this->assertSame('6281234567890', $application->applicant_phone);
        $this->assertNotSame('3514123456789012', $application->getRawOriginal('applicant_nik'));
        $this->assertSame(64, strlen($application->tracking_token_hash));
        $this->assertNotSame('123456', $application->tracking_pin_hash);
    }

    public function test_only_data_admin_and_super_admin_can_access_applications(): void
    {
        $application = LetterApplication::factory()->create();
        $contentAdmin = $this->user('admin_konten');
        $dataAdmin = $this->user('admin_data');

        $this->actingAs($contentAdmin)->get(route('admin.letter-applications.show', $application))->assertForbidden();
        $this->actingAs($dataAdmin)->get(route('admin.letter-applications.show', $application))->assertOk();
    }

    public function test_public_pages_render_and_tracking_is_private(): void
    {
        $service = LetterService::factory()->create();
        $token = str_repeat('a', 64);
        $application = LetterApplication::factory()->for($service, 'service')->create([
            'tracking_token_hash' => hash('sha256', $token),
        ]);
        $application->statusHistories()->create(['to_status' => LetterApplicationStatus::Submitted, 'created_at' => now()]);

        $this->get(route('letter-services.index'))->assertOk()->assertSee($service->name);
        $this->get(route('letter-services.application.create', $service))->assertOk()->assertSee('submission_key');
        $this->get(route('letter-services.track.token', $token))
            ->assertOk()
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow')
            ->assertSee($application->application_number);
    }

    private function user(string $roleCode): User
    {
        $role = Role::factory()->create(['code' => $roleCode]);

        return User::factory()->create(['role_id' => $role->id, 'is_active' => true]);
    }
}
