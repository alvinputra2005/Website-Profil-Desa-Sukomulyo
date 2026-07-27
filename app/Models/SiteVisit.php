<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteVisit extends Model
{
    public $timestamps = false;

    protected $fillable = ['session_hash', 'entry_path', 'visited_at'];

    protected $casts = ['visited_at' => 'datetime'];
}
