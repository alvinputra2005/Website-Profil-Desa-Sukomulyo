<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryMutation extends CmsModel
{
    protected function casts(): array
    {
        return ['mutation_date' => 'date', 'sale_price' => 'decimal:2'];
    }

    public function item(): BelongsTo { return $this->belongsTo(InventoryItem::class, 'inventory_item_id'); }
    public function createdBy(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
}
