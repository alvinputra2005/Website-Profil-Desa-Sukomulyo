<?php

namespace App\Policies;

class DataResourcePolicy extends CmsResourcePolicy
{
    protected string $ability = 'manage-data';
}
