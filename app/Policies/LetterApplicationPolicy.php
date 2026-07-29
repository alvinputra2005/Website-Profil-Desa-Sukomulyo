<?php

namespace App\Policies;

use App\Models\LetterApplication;
use App\Models\User;

class LetterApplicationPolicy
{
    private function manage(User $user): bool
    {
        return $user->hasRole('admin_data');
    }

    public function viewAny(User $user): bool
    {
        return $this->manage($user);
    }

    public function view(User $user, LetterApplication $application): bool
    {
        return $this->manage($user);
    }

    public function update(User $user, LetterApplication $application): bool
    {
        return $this->manage($user);
    }

    public function changeStatus(User $user, LetterApplication $application): bool
    {
        return $this->manage($user);
    }

    public function linkResident(User $user, LetterApplication $application): bool
    {
        return $this->manage($user);
    }

    public function openWhatsApp(User $user, LetterApplication $application): bool
    {
        return $this->manage($user);
    }

    public function complete(User $user, LetterApplication $application): bool
    {
        return $this->manage($user);
    }
}
