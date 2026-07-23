<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class Household extends CmsModel
{
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'registered_at' => 'date',
            'is_dtks_registered' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function head()
    {
        return $this->belongsTo(Resident::class, 'head_resident_id')->withTrashed();
    }

    public function area()
    {
        return $this->belongsTo(PopulationArea::class);
    }

    public function members()
    {
        return $this->hasMany(Resident::class, 'household_id');
    }
}
