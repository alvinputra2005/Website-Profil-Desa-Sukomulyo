<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VillageComment extends Model
{
    protected $fillable = [
        'page_key',
        'name',
        'address',
        'phone',
        'comment',
        'like_count',
        'is_visible',
    ];

    protected function casts(): array
    {
        return [
            'is_visible' => 'boolean',
            'like_count' => 'integer',
        ];
    }
}
