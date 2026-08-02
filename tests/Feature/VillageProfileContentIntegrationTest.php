<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Models\VillageProfileSection;
use App\Services\SiteCache;
use Database\Seeders\VillageProfileContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class VillageProfileContentIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_profile_pages_render_seeded_descriptive_content_without_header_illustrations(): void
    {
        $this->seedProfile();

        $this->get(route('profile-desa'))
            ->assertOk()
            ->assertSee('Ringkasan Identitas Desa Sukomulyo')
            ->assertSee('Dusun Bakir')
            ->assertSee('Dusun Biyan')
            ->assertSee('Dusun Gumul')
            ->assertSee('Dusun Talasan')
            ->assertSee('Dusun Kedungrejo')
            ->assertSee('6.565')
            ->assertDontSee('profile-administrative-path', false);

        $this->get(route('profile-desa.detail', 'sejarah'))
            ->assertOk()
            ->assertDontSee('<h2>Sejarah Desa Sukomulyo</h2>', false)
            ->assertSee('Mbah Syekh Subakir')
            ->assertSee('Mbah Roso Joyo')
            ->assertSee('Suko')
            ->assertSee('Mulyo')
            ->assertSee('Demang Joyo Karto')
            ->assertDontSee('profile-story-visual', false)
            ->assertDontSee('profile-hamlet-grid', false)
            ->assertSee('<th scope="col">Nama Kerawang</th>', false)
            ->assertSee('<th scope="col">Dusun</th>', false)
            ->assertSee('<th scope="col">No.</th>', false)
            ->assertSee('<th scope="col">Nama Pemimpin</th>', false)
            ->assertSee('<th scope="col">Masa Jabatan</th>', false)
            ->assertSee('data-disable-table-copy', false)
            ->assertSee('<table>', false);

        $visionMission = $this->get(route('profile-desa.detail', 'visi-misi'))
            ->assertOk()
            ->assertSee('Mewujudkan Desa Sukomulyo yang aman maju dan sejahtera')
            ->assertSee('Menumbuhkembangkan usaha kecil dan menengah')
            ->assertDontSee('profile-direction-visual', false)
            ->assertSee('data-profile-section="vision"', false)
            ->assertSee('data-profile-section="mission"', false);

        preg_match(
            '/<section[^>]+data-profile-section="mission"[^>]*>(.*?)<\/section>/s',
            $visionMission->getContent(),
            $missionSection,
        );

        $this->assertArrayHasKey(1, $missionSection);
        $this->assertSame(11, substr_count($missionSection[1], '<li>'));
    }

    public function test_admin_can_resave_seeded_content_safely_and_profile_cache_is_invalidated(): void
    {
        $admin = $this->seedProfile();

        $this->actingAs($admin)
            ->get(route('admin.village-content.profile-edit'))
            ->assertOk()
            ->assertSee('35.07.26.2002')
            ->assertSee('Kabupaten Malang')
            ->assertSee('Desa Sukomulyo merupakan desa yang berada di Kecamatan Pujon');

        $this->get(route('profile-desa'))
            ->assertOk()
            ->assertSee('Desa Sukomulyo merupakan desa yang berada di Kecamatan Pujon');
        $this->assertTrue(Cache::has(SiteCache::PROFILE));

        $response = $this->put(route('admin.village-content.profile-update'), [
            'site_name' => 'Desa Sukomulyo',
            'tagline' => 'Website Resmi Pemerintah Desa Sukomulyo',
            'village_code' => '35.07.26.2002',
            'village_bps_code' => '',
            'postal_code' => '65391',
            'address' => 'Kantor Desa Sukomulyo, Kecamatan Pujon, Kabupaten Malang, Jawa Timur 65391',
            'email' => '',
            'phone' => '',
            'mobile' => '',
            'website' => '',
            'district_name' => 'Pujon',
            'district_code' => '35.07.26',
            'district_head_name' => '',
            'district_head_nip' => '',
            'regency_name' => 'Kabupaten Malang',
            'regency_code' => '35.07',
            'province_name' => 'Jawa Timur',
            'province_code' => '35',
            'profile_content' => '<p>Profil hasil pembaruan admin.</p><script>alert(1)</script>',
            'status' => 'published',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertFalse(Cache::has(SiteCache::PROFILE));
        $this->assertStringNotContainsString(
            '<script',
            VillageProfileSection::where('section_key', 'profile')->value('content'),
        );
        $this->get(route('profile-desa'))
            ->assertOk()
            ->assertSee('Profil hasil pembaruan admin.')
            ->assertDontSee('alert(1)');

        $this->put(route('admin.village-content.profile-update'), [
            'site_name' => 'Desa Sukomulyo',
            'village_code' => 'kode-tidak-valid',
            'district_name' => 'Pujon',
            'district_code' => '35.07.26',
            'regency_name' => 'Kabupaten Malang',
            'regency_code' => '35.07',
            'province_name' => 'Jawa Timur',
            'province_code' => '35',
            'profile_content' => '<p>Konten valid.</p>',
            'status' => 'published',
        ])->assertSessionHasErrors('village_code');
    }

    public function test_history_and_vision_mission_can_be_resaved_without_losing_safe_rich_text(): void
    {
        $admin = $this->seedProfile();
        $history = VillageProfileSection::where('section_key', 'history')->firstOrFail();
        $vision = VillageProfileSection::where('section_key', 'vision')->firstOrFail();
        $mission = VillageProfileSection::where('section_key', 'mission')->firstOrFail();

        $this->actingAs($admin)
            ->put(route('admin.village-content.update', 'history'), [
                'title' => $history->title,
                'content' => $history->content.'<script>alert(1)</script>',
                'status' => 'published',
            ])
            ->assertSessionHasNoErrors();

        $this->put(route('admin.village-content.vision-mission.update'), [
            'vision' => $vision->content,
            'mission' => $mission->content,
            'status' => 'published',
        ])->assertSessionHasNoErrors();

        $history->refresh();
        $mission->refresh();
        $this->assertStringContainsString('<table>', $history->content);
        $this->assertStringContainsString('<th scope="col">Nama Kerawang</th>', $history->content);
        $this->assertStringNotContainsString('<script', $history->content);
        $this->assertSame(11, substr_count($mission->content, '<li>'));
    }

    private function seedProfile(): User
    {
        $role = Role::create(['code' => 'super_admin', 'name' => 'Super Admin']);
        $admin = User::factory()->create([
            'role_id' => $role->id,
            'email' => 'admin@sukomulyo.desa.id',
            'is_active' => true,
        ]);
        $this->seed(VillageProfileContentSeeder::class);

        return $admin;
    }
}
