<?php

namespace Tests\Feature;

use App\Models\LetterApplication;
use App\Models\LetterService;
use App\Services\Letters\LetterDocumentRequirementService;
use Database\Seeders\LetterServiceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Tests\TestCase;

class AdditionalLetterServicesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(ThrottleRequests::class);
        $this->seed(LetterServiceSeeder::class);
    }

    public function test_requested_services_are_available_with_their_forms(): void
    {
        $expectedServices = [
            'SIPP' => ['Surat Izin Survei/Penelitian', 'Asal kampus/instansi'],
            'KK' => ['Permohonan Kartu Keluarga/Pecah KK', 'Jenis pengajuan Kartu Keluarga'],
            'AKM' => ['Permohonan Akta Kematian', 'Nama almarhum/almarhumah'],
            'AKL' => ['Permohonan Akta Kelahiran', 'Nama lengkap anak'],
            'PPG' => ['Permohonan Pindah Pergi', 'Alamat lengkap tujuan'],
            'PMI' => ['Permohonan Pindah Masuk', 'Nomor Surat Keterangan Pindah'],
            'KIA' => ['Permohonan Kartu Identitas Anak (KIA)', 'Kategori usia anak'],
        ];

        $index = $this->get(route('letter-services.index'))->assertOk();

        foreach ($expectedServices as $code => [$name, $fieldLabel]) {
            $service = LetterService::where('code', $code)->firstOrFail();

            $index->assertSee($name);
            $this->get(route('letter-services.application.create', $service))
                ->assertOk()
                ->assertSee($fieldLabel);
        }

        $this->assertSame(
            ['SKTM', 'KTP', 'SIPP', 'KK', 'AKM', 'AKL', 'PPG', 'PMI', 'KIA'],
            LetterService::orderBy('display_order')->limit(9)->pluck('code')->all(),
        );
        $this->assertNull(LetterService::where('code', 'KTP')->value('pickup_instructions'));
    }

    public function test_survey_end_date_cannot_be_before_start_date(): void
    {
        $service = LetterService::where('code', 'SIPP')->firstOrFail();

        $this->post(route('letter-services.application.store', $service), $this->payload([
            'form_data' => [
                'instansi_asal' => 'Universitas Negeri Malang',
                'nomor_surat_pengantar' => 'UM/123/2026',
                'judul_penelitian' => 'Pemetaan Potensi Desa',
                'lokasi_penelitian' => 'Desa Sukomulyo',
                'tanggal_mulai' => '2026-08-20',
                'tanggal_selesai' => '2026-08-10',
            ],
        ]))->assertSessionHasErrors('form_data.tanggal_selesai');

        $this->assertDatabaseCount('letter_applications', 0);
    }

    public function test_kia_photo_is_only_requested_for_children_aged_five_or_more(): void
    {
        $service = LetterService::where('code', 'KIA')->firstOrFail();
        $application = LetterApplication::factory()->for($service, 'service')->create([
            'form_data_json' => ['kategori_usia_kia' => 'dibawah_5'],
        ]);
        $requirements = app(LetterDocumentRequirementService::class);

        $this->assertNotContains('pas-foto-anak', $requirements->forApplication($application)->pluck('key')->all());

        $application->update(['form_data_json' => ['kategori_usia_kia' => 'usia_5_17']]);

        $this->assertContains('pas-foto-anak', $requirements->forApplication($application->refresh())->pluck('key')->all());
    }

    public function test_kia_age_category_must_match_the_child_birth_date(): void
    {
        $service = LetterService::where('code', 'KIA')->firstOrFail();

        $this->post(route('letter-services.application.store', $service), $this->payload([
            'form_data' => [
                'nama_anak' => 'Anak Sukomulyo',
                'nik_anak' => '3514123456789013',
                'tempat_lahir_anak' => 'Malang',
                'tanggal_lahir_anak' => now()->subYears(7)->format('Y-m-d'),
                'jenis_kelamin_anak' => 'P',
                'kategori_usia_kia' => 'dibawah_5',
                'nama_orang_tua_wali' => 'Warga Pemohon',
            ],
        ]))->assertSessionHasErrors('form_data.kategori_usia_kia');

        $this->assertDatabaseCount('letter_applications', 0);
    }

    private function payload(array $overrides = []): array
    {
        return array_replace_recursive([
            'applicant_name' => 'Warga Pemohon',
            'applicant_nik' => '3514123456789012',
            'applicant_phone' => '081234567890',
            'birth_place' => 'Malang',
            'birth_date' => '1995-05-12',
            'sex' => 'L',
            'address' => 'Dusun Gumul Desa Sukomulyo',
            'hamlet' => 'Gumul',
            'rt' => '1',
            'rw' => '1',
            'purpose' => 'Pengajuan layanan administrasi',
            'form_data' => [],
            'declaration' => '1',
            'website' => '',
        ], $overrides);
    }
}
