<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VillagePotential extends CmsModel
{
    protected $casts = ['highlights_json' => 'array'];

    public function image(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'image_id');
    }
}
