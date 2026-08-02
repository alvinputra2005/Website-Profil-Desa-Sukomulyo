<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use App\Models\VillageProfileSection;
use App\Services\SiteCache;
use Database\Seeders\VillageProfileContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class VillageProfileContentSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_populates_verified_identity_and_profile_sections_idempotently(): void
    {
        $this->admin();

        $this->seed(VillageProfileContentSeeder::class);
        $this->seed(VillageProfileContentSeeder::class);

        foreach ([
            'site.name' => 'Desa Sukomulyo',
            'village.code' => '35.07.26.2002',
            'village.postal_code' => '65391',
            'district.name' => 'Pujon',
            'district.code' => '35.07.26',
            'regency.name' => 'Kabupaten Malang',
            'regency.code' => '35.07',
            'province.name' => 'Jawa Timur',
            'province.code' => '35',
        ] as $key => $value) {
            $this->assertDatabaseHas('settings', ['key' => $key, 'value' => $value]);
        }

        $this->assertDatabaseCount('village_profile_sections', 4);
        foreach (['profile', 'history', 'vision', 'mission'] as $sectionKey) {
            $this->assertDatabaseHas('village_profile_sections', [
                'section_key' => $sectionKey,
                'status' => 'published',
            ]);
        }

        $mission = VillageProfileSection::where('section_key', 'mission')->firstOrFail();
        $history = VillageProfileSection::where('section_key', 'history')->firstOrFail();

        $this->assertSame(11, substr_count($mission->content, '<li>'));
        $this->assertStringContainsString('<table>', $history->content);
        $this->assertStringContainsString('<th scope="col">Nama Pemimpin</th>', $history->content);
        $this->assertStringContainsString('<th scope="col">Nama Kerawang</th>', $history->content);
        $this->assertStringContainsString('<th scope="col">Dusun</th>', $history->content);
        $this->assertSame(2, substr_count($history->content, '<table>'));
        $this->assertStringNotContainsString('profile-hamlet-grid', $history->content);
        $this->assertStringNotContainsString('<ul>', $history->content);
        $this->assertStringContainsString('Berdasarkan cerita rakyat', $history->content);
        $this->assertDatabaseMissing('settings', ['key' => 'village.bps_code']);
        $this->assertDatabaseMissing('settings', ['key' => 'site.email']);
        $this->assertDatabaseMissing('settings', ['key' => 'site.phone']);
        $this->assertDatabaseMissing('settings', ['key' => 'village.mobile']);
        $this->assertDatabaseMissing('settings', ['key' => 'site.url']);
    }

    public function test_seeder_replaces_legacy_placeholders_but_preserves_admin_edits(): void
    {
        $admin = $this->admin();
        VillageProfileSection::create([
            'section_key' => 'history',
            'title' => 'Sejarah Desa',
            'content' => '<p>Desa Sukomulyo tumbuh melalui semangat gotong royong masyarakat.</p>',
            'status' => 'published',
            'display_order' => 0,
            'updated_by' => $admin->id,
        ]);
        VillageProfileSection::create([
            'section_key' => 'vision',
            'title' => 'Visi Desa',
            'content' => '<p>Konten hasil edit admin.</p>',
            'status' => 'published',
            'display_order' => 10,
            'updated_by' => $admin->id,
        ]);
        Setting::create([
            'key' => 'site.address',
            'value' => 'Alamat yang telah dikonfirmasi admin',
            'type' => 'text',
            'group' => 'identitas',
            'is_public' => true,
            'updated_by' => $admin->id,
        ]);

        Cache::put(SiteCache::SETTINGS, ['stale' => true]);
        Cache::put(SiteCache::PROFILE, ['stale' => true]);
        $this->seed(VillageProfileContentSeeder::class);

        $this->assertStringContainsString(
            'Berdasarkan cerita rakyat',
            VillageProfileSection::where('section_key', 'history')->value('content'),
        );
        $this->assertSame(
            '<p>Konten hasil edit admin.</p>',
            VillageProfileSection::where('section_key', 'vision')->value('content'),
        );
        $this->assertSame(
            'Alamat yang telah dikonfirmasi admin',
            Setting::where('key', 'site.address')->value('value'),
        );
        $this->assertFalse(Cache::has(SiteCache::SETTINGS));
        $this->assertFalse(Cache::has(SiteCache::PROFILE));
    }

    private function admin(): User
    {
        $role = Role::firstOrCreate(['code' => 'super_admin'], ['name' => 'Super Admin']);

        return User::factory()->create([
            'role_id' => $role->id,
            'email' => 'admin@sukomulyo.desa.id',
            'is_active' => true,
        ]);
    }
}
