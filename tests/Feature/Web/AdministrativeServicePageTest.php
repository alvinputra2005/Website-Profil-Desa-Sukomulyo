<?php

namespace Tests\Feature\Web;

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

    public function test_page_shows_flow_and_submission_guide_without_office_information_card(): void
    {
        $this->get(route('informasi-desa.detail', ['section' => 'layanan-administrasi']))
            ->assertOk()
            ->assertSee('Alur Pelayanan')
            ->assertSee('Tata Cara Pengajuan')
            ->assertDontSee('service-flow-actor', false)
            ->assertDontSee('Informasi Pelayanan')
            ->assertDontSee('administration-office-card', false)
            ->assertDontSee('administration-whatsapp', false);
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

        $this->assertGreaterThanOrEqual(13, substr_count($response->getContent(), 'data-sidebar-toggle'));
        $this->assertGreaterThanOrEqual(13, substr_count($response->getContent(), 'data-sidebar-panel'));
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
