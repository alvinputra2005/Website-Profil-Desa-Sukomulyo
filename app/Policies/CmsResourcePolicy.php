<?php

namespace App\Policies;

use App\Models\User;

class CmsResourcePolicy
{
    public function before(User $user): ?bool
    {
        return $user->isSuperAdmin() ? true : null;
    }

    protected string $ability = 'manage-content';

    public function viewAny(User $user): bool
    {
        return $user->can($this->ability);
    }

    public function view(User $user, object $model): bool
    {
        return $user->can($this->ability);
    }

    public function create(User $user): bool
    {
        return $user->can($this->ability);
    }

    public function update(User $user, object $model): bool
    {
        return $user->can($this->ability);
    }

    public function delete(User $user, object $model): bool
    {
        return $user->can($this->ability);
    }

    public function restore(User $user, object $model): bool
    {
        return $user->can($this->ability);
    }

    public function forceDelete(User $user, object $model): bool
    {
        return $user->can($this->ability);
    }
}
