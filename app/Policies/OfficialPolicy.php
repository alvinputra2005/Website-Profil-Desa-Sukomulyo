<?php

namespace App\Policies;

use App\Models\Official;
use App\Models\User;

class OfficialPolicy
{
    public function before(User $user): ?bool
    {
        return $user->isSuperAdmin() ? true : null;
    }

    public function viewAny(User $user): bool { return $user->can('manage-content'); }
    public function view(User $user, Official $official): bool { return $user->can('manage-content'); }
    public function create(User $user): bool { return $user->can('manage-content'); }
    public function update(User $user, Official $official): bool { return $user->can('manage-content'); }
    public function delete(User $user, Official $official): bool { return $user->can('manage-content'); }
    public function deleteAny(User $user): bool { return $user->can('manage-content'); }
}
