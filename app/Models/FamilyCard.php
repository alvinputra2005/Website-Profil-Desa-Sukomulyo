<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class FamilyCard extends CmsModel
{
    use SoftDeletes;

    protected $table = 'families';

    protected function casts(): array
    {
        return [
            'registered_at' => 'date',
            'issued_at' => 'date',
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
        return $this->hasMany(Resident::class, 'family_id');
    }
}
