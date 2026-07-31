<?php

namespace Tests\Feature;

use App\Models\FamilyCard;
use App\Models\Official;
use App\Models\PopulationGroup;
use App\Models\PopulationGroupMember;
use App\Models\Resident;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class ResourcePolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_content_admin_can_manage_officials_but_data_admin_cannot(): void
    {
        $contentAdmin = $this->userWithRole('admin_konten');
        $dataAdmin = $this->userWithRole('admin_data');
        $official = new Official;

        $this->assertTrue(Gate::forUser($contentAdmin)->allows('create', Official::class));
        $this->assertTrue(Gate::forUser($contentAdmin)->allows('update', $official));
        $this->assertFalse(Gate::forUser($dataAdmin)->allows('viewAny', Official::class));
        $this->assertFalse(Gate::forUser($dataAdmin)->allows('delete', $official));
    }

    public function test_data_admin_can_manage_every_population_resource_but_content_admin_cannot(): void
    {
        $dataAdmin = $this->userWithRole('admin_data');
        $contentAdmin = $this->userWithRole('admin_konten');
        $models = [
            Resident::class => new Resident,
            FamilyCard::class => new FamilyCard,
            PopulationGroup::class => new PopulationGroup,
            PopulationGroupMember::class => new PopulationGroupMember,
        ];

        foreach ($models as $class => $model) {
            $this->assertTrue(Gate::forUser($dataAdmin)->allows('viewAny', $class));
            $this->assertTrue(Gate::forUser($dataAdmin)->allows('create', $class));
            $this->assertTrue(Gate::forUser($dataAdmin)->allows('update', $model));
            $this->assertTrue(Gate::forUser($dataAdmin)->allows('delete', $model));
            $this->assertFalse(Gate::forUser($contentAdmin)->allows('viewAny', $class));
            $this->assertFalse(Gate::forUser($contentAdmin)->allows('update', $model));
        }
    }

    public function test_super_admin_bypasses_all_resource_policies(): void
    {
        $superAdmin = $this->userWithRole('super_admin');

        $this->assertTrue(Gate::forUser($superAdmin)->allows('delete', new Official));
        $this->assertTrue(Gate::forUser($superAdmin)->allows('delete', new Resident));
        $this->assertTrue(Gate::forUser($superAdmin)->allows('delete', new PopulationGroupMember));
    }

    private function userWithRole(string $code): User
    {
        $role = Role::create(['name' => $code, 'code' => $code]);

        return User::factory()->create(['role_id' => $role->id, 'is_active' => true]);
    }
}
