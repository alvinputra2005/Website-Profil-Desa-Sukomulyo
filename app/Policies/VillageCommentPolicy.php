<?php

namespace App\Policies;

use App\Models\User;
use App\Models\VillageComment;

class VillageCommentPolicy
{
    public function before(User $user): ?bool
    {
        return $user->isSuperAdmin() ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->can('manage-content');
    }

    public function view(User $user, VillageComment $comment): bool
    {
        return $user->can('manage-content');
    }

    public function update(User $user, VillageComment $comment): bool
    {
        return $user->can('manage-content');
    }

    public function delete(User $user, VillageComment $comment): bool
    {
        return $user->can('manage-content');
    }
}
