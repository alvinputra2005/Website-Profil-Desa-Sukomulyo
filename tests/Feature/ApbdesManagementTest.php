<?php

namespace Tests\Feature;

use App\Models\Apbdes;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApbdesManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_apbdes_menu_uses_requested_sidebar_position(): void
    {
        $response = $this->actingAs($this->user('super_admin'))
            ->get(route('admin.apbdes.index'));

        $response->assertOk()
            ->assertSee('Data APBDes')
            ->assertSeeInOrder(['Informasi Publik', 'APBDes', 'Data Desa']);

        $this->get(route('admin.apbdes.create'))
            ->assertOk()
            ->assertSee('name="revenue[0][name]"', false)
            ->assertSee('Penyelenggaraan Pemerintahan Desa')
            ->assertSee('Penanggulangan Bencana, Keadaan Darurat, dan Mendesak Desa')
            ->assertSee('Permasalahan Pelaksanaan Anggaran')
            ->assertSee('Penyelesaian/Upaya yang Ditempuh');
    }

    public function test_content_admin_cannot_manage_apbdes(): void
    {
        $this->actingAs($this->user('admin_konten'))
            ->get(route('admin.apbdes.index'))
            ->assertForbidden();
    }

    public function test_admin_can_create_and_publish_complete_apbdes_data(): void
    {
        $admin = $this->user('admin_data');

        $response = $this->actingAs($admin)->post(route('admin.apbdes.store'), [
            'year' => 2027,
            'title' => 'APBDes Desa Sukomulyo Tahun 2027',
            'description' => 'Data uji APBDes 2027.',
            'status' => 'published',
            'is_partial_year' => '1',
            'income_budget' => 1500000000,
            'income_realization' => 750000000,
            'spending_budget' => 1400000000,
            'spending_realization' => 600000000,
            'financing_receipt' => 100000000,
            'financing_expenditure' => 25000000,
            'revenue' => [
                [
                    'code' => '1.1',
                    'name' => 'Pendapatan Asli Desa',
                    'short_name' => 'PADes',
                    'budget' => 1500000000,
                    'realization' => 750000000,
                    'note' => 'Realisasi semester pertama.',
                ],
            ],
            'spending' => [
                [
                    'code' => '2.1',
                    'name' => 'Penyelenggaraan Pemerintahan Desa',
                    'short_name' => 'Pemerintahan',
                    'description' => 'Pelayanan dan tata kelola desa.',
                    'budget' => 1400000000,
                    'realization' => 600000000,
                    'note' => null,
                ],
            ],
            'programs' => [
                [
                    'code' => '2.1.01',
                    'category_code' => '2.1',
                    'category' => 'Pemerintahan',
                    'name' => 'Pelayanan Administrasi Desa',
                    'description' => 'Peningkatan layanan warga.',
                    'budget' => 200000000,
                    'realization' => 90000000,
                ],
            ],
            'problems' => 'Penyerapan belum penuh karena tahun masih berjalan.',
            'solutions' => 'Percepatan pelaksanaan kegiatan sesuai rencana kerja.',
            'programs_note' => null,
            'quarters_note' => 'Data yang tersedia merupakan rekap semester pertama.',
            'data_quality_notes' => 'Angka telah direkonsiliasi dengan tabel LPPD.',
            'source_document' => 'LPPD Desa Sukomulyo 2027',
            'source_reference' => 'Bagian APBDes halaman 12–20',
        ]);

        $budget = Apbdes::query()->where('year', 2027)->firstOrFail();

        $response->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.apbdes.edit', $budget));
        $this->assertSame('published', $budget->status);
        $this->assertNotNull($budget->published_at);
        $this->assertSame($admin->id, $budget->updated_by);
        $this->assertCount(1, $budget->programs);

        $this->get(route('transparansi-apbdes'))
            ->assertOk()
            ->assertSee('2027');

        $this->get(route('transparansi-apbdes.show', 2027))
            ->assertOk()
            ->assertSee('Pelayanan Administrasi Desa')
            ->assertSee('Penyerapan belum penuh')
            ->assertSee('Percepatan pelaksanaan kegiatan')
            ->assertSee('LPPD Desa Sukomulyo 2027');
    }

    public function test_draft_budget_is_not_available_publicly_until_published(): void
    {
        $admin = $this->user('admin_data');
        $budget = Apbdes::query()->where('year', 2026)->firstOrFail();

        $this->get(route('transparansi-apbdes.show', 2026))->assertNotFound();

        $this->actingAs($admin)
            ->patch(route('admin.apbdes.toggle-publication', $budget))
            ->assertRedirect();

        $this->get(route('transparansi-apbdes.show', 2026))
            ->assertOk()
            ->assertSee('Detail APBDes Tahun 2026');
    }

    private function user(string $code): User
    {
        $role = Role::create(['name' => $code, 'code' => $code]);

        return User::factory()->create(['role_id' => $role->id, 'is_active' => true]);
    }
}
