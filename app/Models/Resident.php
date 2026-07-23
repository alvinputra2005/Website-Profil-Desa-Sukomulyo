<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class Resident extends CmsModel
{
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'registered_at' => 'date',
        ];
    }

    public function area()
    {
        return $this->belongsTo(PopulationArea::class);
    }

    public function family()
    {
        return $this->belongsTo(FamilyCard::class);
    }

    public function household()
    {
        return $this->belongsTo(Household::class);
    }

    public function events()
    {
        return $this->hasMany(ResidentEvent::class)->latest('event_date');
    }

    public function groupMemberships()
    {
        return $this->hasMany(PopulationGroupMember::class);
    }

    public function chairedGroups()
    {
        return $this->hasMany(PopulationGroup::class, 'chairperson_id');
    }

    public function headedFamilies()
    {
        return $this->hasMany(FamilyCard::class, 'head_resident_id');
    }

    public function headedHouseholds()
    {
        return $this->hasMany(Household::class, 'head_resident_id');
    }

    public function getSexLabelAttribute(): string
    {
        return $this->sex === 'L' ? 'Laki-laki' : 'Perempuan';
    }

    public function getAgeAttribute(): ?int
    {
        return $this->birth_date?->age;
    }
}
