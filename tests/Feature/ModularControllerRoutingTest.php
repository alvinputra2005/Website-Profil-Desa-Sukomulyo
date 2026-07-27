<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\Officials\OfficialController;
use App\Http\Controllers\Admin\Population\Residents\ResidentController;
use App\Http\Controllers\Admin\Population\Residents\ResidentImportController;
use App\Http\Controllers\Admin\Village\VillageIdentityController;
use App\Http\Controllers\Admin\Village\VillageSectionController;
use App\Http\Controllers\Admin\Village\VisionMissionController;
use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Web\NewsController;
use App\Http\Controllers\Web\VillageMapController;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use App\Models\VillageProfileSection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ModularControllerRoutingTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_routes_use_modular_web_controllers(): void
    {
        $this->assertSame(HomeController::class, Route::getRoutes()->getByName('beranda')->getActionName());
        $this->assertSame(NewsController::class.'@index', Route::getRoutes()->getByName('berita-desa.index')->getActionName());
        $this->assertSame(VillageMapController::class.'@geoJson', Route::getRoutes()->getByName('peta-desa.geojson')->getActionName());
    }

    public function test_admin_routes_use_modular_domain_controllers(): void
    {
        $this->assertSame(VillageIdentityController::class.'@show', Route::getRoutes()->getByName('admin.village-content.profile')->getActionName());
        $this->assertSame(VisionMissionController::class.'@edit', Route::getRoutes()->getByName('admin.village-content.vision-mission.edit')->getActionName());
        $this->assertSame(VillageSectionController::class.'@edit', Route::getRoutes()->getByName('admin.village-content.edit')->getActionName());
        $this->assertSame(ResidentController::class.'@index', Route::getRoutes()->getByName('admin.population.residents.index')->getActionName());
        $this->assertSame(ResidentImportController::class.'@store', Route::getRoutes()->getByName('admin.population.residents.import')->getActionName());
        $this->assertSame(OfficialController::class.'@index', Route::getRoutes()->getByName('admin.officials.index')->getActionName());
    }

    public function test_vision_mission_controller_executes_its_domain_action(): void
    {
        $role = Role::create(['name' => 'Admin Konten', 'code' => 'admin_konten']);
        $admin = User::factory()->create(['role_id' => $role->id, 'is_active' => true]);

        $this->actingAs($admin)
            ->put(route('admin.village-content.vision-mission.update'), [
                'vision' => '<p>Desa maju</p>',
                'mission' => '<p>Pelayanan terbuka</p>',
                'status' => 'published',
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('village_profile_sections', [
            'section_key' => 'vision',
            'status' => 'published',
        ]);
        $this->assertDatabaseHas('village_profile_sections', [
            'section_key' => 'mission',
            'status' => 'published',
        ]);
    }

    public function test_identity_controller_executes_action_and_sanitizes_profile(): void
    {
        $role = Role::create(['name' => 'Admin Konten', 'code' => 'admin_konten']);
        $admin = User::factory()->create(['role_id' => $role->id, 'is_active' => true]);

        $this->actingAs($admin)
            ->put(route('admin.village-content.profile-update'), [
                'site_name' => 'Desa Sukomulyo',
                'tagline' => 'Desa maju dan melayani',
                'village_code' => '35.25.04.2008',
                'village_bps_code' => '3525042008',
                'postal_code' => '61152',
                'address' => 'Jalan Raya Sukomulyo Nomor 1',
                'email' => 'pemdes@sukomulyo.desa.id',
                'phone' => '(031) 123456',
                'mobile' => '+62 812-3456-7890',
                'website' => 'https://sukomulyo.desa.id',
                'district_name' => 'Kecamatan Contoh',
                'district_code' => '35.25.04',
                'regency_name' => 'Kabupaten Contoh',
                'regency_code' => '35.25',
                'province_name' => 'Jawa Timur',
                'province_code' => '35',
                'profile_content' => '<p>Profil diperbarui.</p><script>alert(1)</script>',
                'status' => 'published',
            ])
            ->assertRedirect(route('admin.village-content.profile'))
            ->assertSessionHasNoErrors();

        $this->assertSame('35.25.04.2008', Setting::where('key', 'village.code')->value('value'));
        $profile = VillageProfileSection::where('section_key', 'profile')->firstOrFail();
        $this->assertStringContainsString('Profil diperbarui.', $profile->content);
        $this->assertStringNotContainsString('<script', $profile->content);
    }
}
