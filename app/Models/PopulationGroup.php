<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class PopulationGroup extends CmsModel
{
    use SoftDeletes;

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function chairperson()
    {
        return $this->belongsTo(Resident::class, 'chairperson_id')->withTrashed();
    }

    public function memberships()
    {
        return $this->hasMany(PopulationGroupMember::class, 'group_id');
    }

    public function members()
    {
        return $this->belongsToMany(Resident::class, 'population_group_members', 'group_id', 'resident_id')->withPivot(['member_number', 'position'])->withTimestamps();
    }
}
