<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RegionApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_content_admin_can_load_all_region_levels_from_wilayah_id(): void
    {
        Http::fake(function (Request $request) {
            $data = match ($request->url()) {
                'https://wilayah.id/api/provinces.json' => [['code' => '35', 'name' => 'Jawa Timur']],
                'https://wilayah.id/api/regencies/35.json' => [['code' => '35.25', 'name' => 'Kabupaten Gresik']],
                'https://wilayah.id/api/districts/35.25.json' => [['code' => '35.25.04', 'name' => 'Cerme']],
                'https://wilayah.id/api/villages/35.25.04.json' => [['code' => '35.25.04.2013', 'name' => 'Sukomulyo']],
                default => [],
            };

            return Http::response(['data' => $data, 'meta' => ['updated_at' => '2025-07-04']]);
        });

        $this->actingAs($this->user('admin_konten'))
            ->getJson(route('admin.regions.provinces'))
            ->assertOk()
            ->assertJsonPath('data.0.code', '35');

        $this->getJson(route('admin.regions.regencies', '35'))
            ->assertOk()
            ->assertJsonPath('data.0.name', 'Kabupaten Gresik');

        $this->getJson(route('admin.regions.districts', '35.25'))
            ->assertOk()
            ->assertJsonPath('data.0.name', 'Cerme');

        $this->getJson(route('admin.regions.villages', '35.25.04'))
            ->assertOk()
            ->assertJsonPath('data.0.name', 'Sukomulyo');

        Http::assertSentCount(4);
    }

    public function test_region_data_is_cached_and_upstream_failures_return_service_unavailable(): void
    {
        Http::fake([
            'https://wilayah.id/api/provinces.json' => Http::response([
                'data' => [['code' => '35', 'name' => 'Jawa Timur']],
            ]),
            'https://wilayah.id/api/regencies/35.json' => Http::response(['message' => 'Server error'], 500),
        ]);

        $this->actingAs($this->user('admin_konten'));

        $this->getJson(route('admin.regions.provinces'))->assertOk();
        $this->getJson(route('admin.regions.provinces'))->assertOk();
        Http::assertSentCount(1);

        $this->getJson(route('admin.regions.regencies', '35'))
            ->assertStatus(503)
            ->assertJsonPath('data', [])
            ->assertJsonPath('message', 'Data wilayah sedang tidak dapat dimuat. Silakan coba lagi.');
    }

    public function test_region_proxy_requires_content_management_permission_and_valid_codes(): void
    {
        Http::fake();
        $this->actingAs($this->user('admin_data'))
            ->getJson(route('admin.regions.provinces'))
            ->assertForbidden();

        $this->actingAs($this->user('admin_konten'))
            ->getJson('/admin/wilayah/regencies/tidak-valid')
            ->assertNotFound();

        Http::assertNothingSent();
    }

    private function user(string $roleCode): User
    {
        $role = Role::create([
            'name' => ucwords(str_replace('_', ' ', $roleCode)),
            'code' => $roleCode,
        ]);

        return User::factory()->create([
            'role_id' => $role->id,
            'is_active' => true,
        ]);
    }
}
