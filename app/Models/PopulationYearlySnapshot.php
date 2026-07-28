<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;

class PopulationYearlySnapshot extends CmsModel
{
    protected $fillable = [
        'year',
        'male_count',
        'female_count',
        'reference_date',
        'source',
        'notes',
        'is_published',
    ];

    protected $appends = ['total_count'];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'male_count' => 'integer',
            'female_count' => 'integer',
            'reference_date' => 'date',
            'is_published' => 'boolean',
        ];
    }

    protected function totalCount(): Attribute
    {
        return Attribute::get(
            fn (): int => $this->male_count + $this->female_count,
        );
    }
}
