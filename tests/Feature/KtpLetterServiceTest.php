<?php

namespace Tests\Feature;

use App\Models\LetterApplication;
use App\Models\LetterService;
use App\Services\Letters\LetterDocumentRequirementService;
use Database\Seeders\LetterServiceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KtpLetterServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_ktp_service_is_available_with_regulation_based_form_fields(): void
    {
        $this->seed(LetterServiceSeeder::class);
        $service = LetterService::where('code', 'KTP')->firstOrFail();

        $this->assertSame('Pengajuan KTP-el', $service->name);
        $this->assertSame(1, $service->display_order);

        $this->get(route('letter-services.index'))
            ->assertOk()
            ->assertSee('Pengajuan KTP-el');

        $this->get(route('letter-services.application.create', $service))
            ->assertOk()
            ->assertSee('Jenis pengajuan KTP-el')
            ->assertSee('Baru / pemula')
            ->assertSee('Penggantian karena hilang')
            ->assertSee('Penggantian karena rusak')
            ->assertSee('Perubahan data')
            ->assertSee('Pindah datang')
            ->assertSee('Status perkawinan')
            ->assertSee('Perekaman biometrik serta penerbitan KTP-el tetap dilakukan oleh Disdukcapil Kabupaten Malang.');
    }

    public function test_underage_unmarried_applicant_cannot_request_a_new_ktp(): void
    {
        $this->seed(LetterServiceSeeder::class);
        $service = LetterService::where('code', 'KTP')->firstOrFail();

        $this->post(route('letter-services.application.store', $service), $this->payload([
            'birth_date' => now()->subYears(16)->format('Y-m-d'),
            'form_data' => [
                'jenis_pengajuan_ktp' => 'baru',
                'status_perkawinan' => 'belum_kawin',
            ],
        ]))->assertSessionHasErrors('birth_date');

        $this->assertDatabaseCount('letter_applications', 0);
    }

    public function test_eligible_resident_can_start_a_new_ktp_application(): void
    {
        $this->seed(LetterServiceSeeder::class);
        $service = LetterService::where('code', 'KTP')->firstOrFail();

        $this->post(route('letter-services.application.store', $service), $this->payload())
            ->assertRedirect();

        $application = LetterApplication::firstOrFail();

        $this->assertSame('baru', $application->form_data_json['jenis_pengajuan_ktp']);
        $this->assertSame('belum_kawin', $application->form_data_json['status_perkawinan']);
        $this->assertNull($application->submitted_at);
    }

    public function test_document_requirements_follow_the_selected_ktp_application_type(): void
    {
        $this->seed(LetterServiceSeeder::class);
        $service = LetterService::where('code', 'KTP')->firstOrFail();
        $application = LetterApplication::factory()->for($service, 'service')->create([
            'form_data_json' => [
                'jenis_pengajuan_ktp' => 'hilang',
                'status_perkawinan' => 'kawin',
            ],
        ]);

        $requirements = app(LetterDocumentRequirementService::class)->forApplication($application);
        $requiredKeys = $requirements
            ->filter(fn (array $requirement): bool => $requirement['required'])
            ->pluck('key')
            ->all();

        $this->assertCount(2, $requirements);
        $this->assertSame(['kartu-keluarga', 'surat-kehilangan'], $requiredKeys);
    }

    private function payload(array $overrides = []): array
    {
        return array_replace_recursive([
            'applicant_name' => 'Warga Pemohon',
            'applicant_nik' => '3514123456789012',
            'applicant_phone' => '081234567890',
            'birth_place' => 'Malang',
            'birth_date' => '2000-01-01',
            'sex' => 'L',
            'address' => 'Dusun Gumul Desa Sukomulyo',
            'hamlet' => 'Gumul',
            'rt' => '1',
            'rw' => '1',
            'purpose' => 'Pengajuan dokumen KTP-el',
            'form_data' => [
                'jenis_pengajuan_ktp' => 'baru',
                'status_perkawinan' => 'belum_kawin',
            ],
            'declaration' => '1',
            'website' => '',
        ], $overrides);
    }
}
