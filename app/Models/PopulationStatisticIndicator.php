<?php

namespace App\Models;

class PopulationStatisticIndicator extends CmsModel
{
    protected function casts(): array
    {
        return [
            'configuration_json' => 'array',
            'is_enabled' => 'boolean',
            'is_public' => 'boolean',
        ];
    }
}
