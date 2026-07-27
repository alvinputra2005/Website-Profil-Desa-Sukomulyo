<?php

namespace App\Policies;

use App\Models\ContactMessage;
use App\Models\User;

class ContactMessagePolicy
{
    public function before(User $user): ?bool
    {
        return $user->isSuperAdmin() ? true : null;
    }

    public function viewAny(User $user): bool { return $user->can('manage-content'); }
    public function view(User $user, ContactMessage $message): bool { return $user->can('manage-content'); }
    public function delete(User $user, ContactMessage $message): bool { return $user->can('manage-content'); }
}
