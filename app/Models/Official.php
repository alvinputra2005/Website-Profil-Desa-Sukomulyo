<?php

namespace App\Models;

class Official extends CmsModel
{
    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'appointment_date' => 'date',
            'dismissal_date' => 'date',
            'registered_at' => 'date',
            'social_media' => 'array',
            'is_acting' => 'boolean',
            'is_active' => 'boolean',
            'attendance_enabled' => 'boolean',
            'can_sign_on_behalf' => 'boolean',
            'can_sign_for' => 'boolean',
        ];
    }

    public function photo()
    {
        return $this->belongsTo(Media::class, 'photo_id');
    }

    public function resident()
    {
        return $this->belongsTo(Resident::class);
    }

    public function superior()
    {
        return $this->belongsTo(self::class, 'superior_id');
    }

    public function subordinates()
    {
        return $this->hasMany(self::class, 'superior_id')->orderBy('display_order');
    }

    public function getFullNameAttribute(): string
    {
        $name = trim(implode(' ', array_filter([$this->title_prefix, $this->name])));

        return $this->title_suffix ? $name.', '.$this->title_suffix : $name;
    }

    public function getSexLabelAttribute(): string
    {
        return match ($this->sex) {
            'L' => 'Laki-laki',
            'P' => 'Perempuan',
            default => '-',
        };
    }

    public function getPositionLabelAttribute(): string
    {
        return $this->is_acting ? 'Pj. '.$this->position : $this->position;
    }
}
