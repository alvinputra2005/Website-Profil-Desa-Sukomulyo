<?php

namespace App\Policies;

use App\Models\LetterService;
use App\Models\User;

class LetterServicePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin_data');
    }

    public function view(User $user, LetterService $service): bool
    {
        return $user->hasRole('admin_data');
    }

    public function create(User $user): bool
    {
        return $user->hasRole('admin_data');
    }

    public function update(User $user, LetterService $service): bool
    {
        return $user->hasRole('admin_data');
    }

    public function delete(User $user, LetterService $service): bool
    {
        return $user->hasRole('admin_data');
    }
}
