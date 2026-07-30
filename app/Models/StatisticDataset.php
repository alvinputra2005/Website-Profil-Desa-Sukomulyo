<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class StatisticDataset extends CmsModel
{
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'columns_json' => 'array',
            'totals_json' => 'array',
            'source_metadata_json' => 'array',
            'visualization_config_json' => 'array',
            'requires_manual_review' => 'boolean',
        ];
    }

    public function values(): HasMany
    {
        return $this->hasMany(StatisticValue::class, 'dataset_id')->orderBy('display_order');
    }

    public function rows(): HasMany
    {
        return $this->hasMany(StatisticRow::class)->orderBy('display_order');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function import(): BelongsTo
    {
        return $this->belongsTo(StatisticImport::class, 'statistic_import_id');
    }

    public function categoryRelation(): BelongsTo
    {
        return $this->belongsTo(StatisticCategory::class, 'statistic_category_id');
    }
}
