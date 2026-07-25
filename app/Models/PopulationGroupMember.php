<?php

namespace App\Models;

class PopulationGroupMember extends CmsModel
{
    protected function casts(): array
    {
        return [
            'appointment_date' => 'date',
            'dismissal_date' => 'date',
        ];
    }

    public function group()
    {
        return $this->belongsTo(PopulationGroup::class);
    }

    public function resident()
    {
        return $this->belongsTo(Resident::class)->withTrashed();
    }
}
