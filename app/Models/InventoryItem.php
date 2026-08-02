<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryItem extends CmsModel
{
    protected function casts(): array
    {
        return ['details' => 'array', 'acquisition_year' => 'integer', 'quantity' => 'integer', 'value' => 'decimal:2'];
    }

    public function mutations(): HasMany { return $this->hasMany(InventoryMutation::class)->latest('mutation_date'); }
    public function updatedBy(): BelongsTo { return $this->belongsTo(User::class, 'updated_by'); }
}
