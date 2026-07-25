<?php

namespace App\Models;

class ResidentEvent extends CmsModel
{
    protected function casts(): array
    {
        return [
            'event_date' => 'date',
            'reported_at' => 'date',
        ];
    }

    public function resident()
    {
        return $this->belongsTo(Resident::class)->withTrashed();
    }

    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function getEventLabelAttribute(): string
    {
        return [
            'birth' => 'Lahir',
            'arrival' => 'Datang',
            'departure' => 'Pindah',
            'death' => 'Meninggal',
            'missing' => 'Hilang',
            'reactivated' => 'Diaktifkan kembali',
        ][$this->event_type] ?? ucfirst($this->event_type);
    }
}
