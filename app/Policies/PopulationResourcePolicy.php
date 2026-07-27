<?php

namespace App\Policies;

use App\Models\User;

abstract class PopulationResourcePolicy
{
    public function before(User $user): ?bool
    {
        return $user->isSuperAdmin() ? true : null;
    }

    public function viewAny(User $user): bool { return $user->can('manage-data'); }
    public function view(User $user, object $model): bool { return $user->can('manage-data'); }
    public function create(User $user): bool { return $user->can('manage-data'); }
    public function update(User $user, object $model): bool { return $user->can('manage-data'); }
    public function delete(User $user, object $model): bool { return $user->can('manage-data'); }
}
