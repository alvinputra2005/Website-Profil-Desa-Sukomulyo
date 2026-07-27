<?php

namespace App\Policies;

class AdminResourcePolicy extends CmsResourcePolicy
{
    protected string $ability = 'manage-users';
}
