<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StatisticRow extends CmsModel
{
    protected function casts(): array
    {
        return ['values_json' => 'array'];
    }

    public function dataset(): BelongsTo
    {
        return $this->belongsTo(StatisticDataset::class, 'statistic_dataset_id');
    }
}
