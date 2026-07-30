<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class StatisticImport extends CmsModel
{
    protected static function booted(): void
    {
        static::creating(function (StatisticImport $import): void {
            $import->public_id ??= (string) Str::ulid();
        });
    }

    protected function casts(): array
    {
        return [
            'warnings_json' => 'array',
            'errors_json' => 'array',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function importer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'imported_by');
    }

    public function datasets(): HasMany
    {
        return $this->hasMany(StatisticDataset::class);
    }
}
