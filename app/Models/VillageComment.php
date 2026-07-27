<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VillageComment extends Model
{
    protected $fillable = [
        'name',
        'address',
        'phone',
        'comment',
        'is_visible',
    ];

    protected function casts(): array
    {
        return [
            'is_visible' => 'boolean',
        ];
    }
}
