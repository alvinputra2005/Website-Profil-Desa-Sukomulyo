<?php

namespace App\Models;

class PopulationArea extends CmsModel
{
    public function residents()
    {
        return $this->hasMany(Resident::class, 'area_id');
    }

    public function families()
    {
        return $this->hasMany(FamilyCard::class, 'area_id');
    }

    public function getLabelAttribute(): string
    {
        return collect([
            'Dusun '.$this->hamlet,
            $this->rw !== '' ? 'RW '.$this->rw : null,
            $this->rt !== '' ? 'RT '.$this->rt : null,
        ])->filter()->implode(' / ');
    }
}
