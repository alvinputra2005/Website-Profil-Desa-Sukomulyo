<?php

namespace Tests\Feature\Web;

use App\Services\Letters\LetterSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdministrativeServicePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_administrative_service_page_shows_the_complete_catalog(): void
    {
        $response = $this->get(route('informasi-desa.detail', ['section' => 'layanan-administrasi']))
            ->assertOk()
            ->assertSee('Syarat Administrasi')
            ->assertSee('Pelayanan')
            ->assertSee('Dokumen yang perlu disiapkan:')
            ->assertSee('Pengajuan Kartu Keluarga')
            ->assertSee('Pengajuan Akta Kematian')
            ->assertSee('Administrasi Nikah')
            ->assertSee('fas fa-venus-mars', false)
            ->assertSee('SKCK')
            ->assertSee('Pengajuan Akta Kelahiran')
            ->assertSee('fas fa-birthday-cake', false)
            ->assertSee('Pindah Tempat (Masuk/Keluar)')
            ->assertSee('Kartu Identitas Anak (KIA)')
            ->assertSee('BPJS')
            ->assertSee('Penerbitan KK Baru')
            ->assertSee('Penerbitan KK Perubahan Data')
            ->assertSee('Penerbitan KK Hilang/Rusak');

        $html = $response->getContent();

        $this->assertSame(8, substr_count($html, 'data-service-item'));
        $this->assertSame(3, substr_count($html, 'data-service-child-item'));
        $this->assertSame(1, substr_count($html, 'data-administrative-services'));
    }

    public function test_page_shows_submission_guide_and_matching_office_hours_without_old_flow(): void
    {
        $response = $this->get(route('informasi-desa.detail', ['section' => 'layanan-administrasi']))
            ->assertOk()
            ->assertDontSee('Alur Pelayanan')
            ->assertSee('Tata Cara Pengajuan')
            ->assertSee('submission-guide-flow', false)
            ->assertSee('Isi formulir data pemohon dan keperluan sesuai dokumen resmi')
            ->assertSee('Unggah seluruh dokumen wajib dalam format JPG, PNG, atau PDF')
            ->assertSee('simpan nomor permohonan dan PIN pelacakan')
            ->assertSee('Konfirmasi melalui WhatsApp Desa dan pantau status permohonan')
            ->assertSee('ambil surat fisik di kantor desa')
            ->assertDontSee('Datang ke Kantor Desa Sukomulyo pada jam pelayanan.')
            ->assertDontSee('diproses melalui SIPEDULI')
            ->assertSee('Jam Layanan')
            ->assertSee('Hari dan jam pelayanan')
            ->assertSee(app(LetterSettings::class)->officeHours())
            ->assertSee('Pengajuan Layanan Surat')
            ->assertSee('Siap Mengajukan Surat?')
            ->assertSee('Mulai Pengajuan')
            ->assertSee('href="'.route('letter-services.index').'"', false)
            ->assertSee('administration-application-cta', false)
            ->assertSee('data-static-application-cta', false)
            ->assertDontSee('service-flow-actor', false)
            ->assertDontSee('Informasi Pelayanan')
            ->assertDontSee('administration-office-card', false)
            ->assertDontSee('administration-whatsapp', false);

        $html = $response->getContent();
        preg_match('/<section[^>]*data-static-application-cta[^>]*>.*?<\/section>/s', $html, $applicationCta);
        $applicationCtaHtml = $applicationCta[0] ?? '';
        $this->assertNotSame('', $applicationCtaHtml);
        $this->assertStringNotContainsString('data-sidebar-toggle', $applicationCtaHtml);
        $this->assertStringNotContainsString('aria-expanded', $applicationCtaHtml);
        $this->assertStringNotContainsString(' hidden', $applicationCtaHtml);
        $this->assertLessThan(
            strpos($html, 'letter-office-hours-heading'),
            strpos($html, 'administration-application-cta'),
        );
    }

    public function test_page_uses_accessible_accordions_and_local_search(): void
    {
        $response = $this->get(route('informasi-desa.detail', ['section' => 'layanan-administrasi']))
            ->assertOk()
            ->assertSee('data-administration-search', false)
            ->assertSee('Cari jenis pelayanan...', false)
            ->assertSee('data-administration-empty', false)
            ->assertSee('data-service-accordion', false)
            ->assertSee('data-service-child-accordion', false)
            ->assertSee('aria-controls="service-panel-kartu-keluarga"', false)
            ->assertSee('aria-expanded="true"', false)
            ->assertSee('Layanan tidak ditemukan');

        $this->assertGreaterThanOrEqual(12, substr_count($response->getContent(), 'data-sidebar-toggle'));
        $this->assertGreaterThanOrEqual(12, substr_count($response->getContent(), 'data-sidebar-panel'));
    }

    public function test_administrative_page_no_longer_uses_the_generic_fallback(): void
    {
        $this->get(route('informasi-desa.detail', ['section' => 'layanan-administrasi']))
            ->assertOk()
            ->assertDontSee('Siapkan KTP, Kartu Keluarga, dan dokumen pendukung sesuai jenis layanan yang diajukan.')
            ->assertDontSee('data-section-grid', false)
            ->assertSee('administration-page-layout', false);
    }

    public function test_other_information_pages_remain_available(): void
    {
        foreach ([
            'agenda' => 'Agenda Desa',
            'bantuan-sosial' => 'Informasi Bantuan Sosial',
            'informasi-publik' => 'Informasi Publik',
        ] as $section => $heading) {
            $this->get(route('informasi-desa.detail', ['section' => $section]))
                ->assertOk()
                ->assertSee($heading)
                ->assertDontSee('data-administrative-services', false);
        }
    }
}
