<?php

namespace App\Models;

use Database\Factories\LetterServiceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LetterService extends Model
{
    /** @use HasFactory<LetterServiceFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'slug', 'code', 'description', 'icon', 'requirements_json', 'form_schema_json', 'processing_days', 'fee_information', 'pickup_instructions', 'is_active', 'display_order', 'created_by', 'updated_by'];

    protected function casts(): array
    {
        return ['requirements_json' => 'array', 'form_schema_json' => 'array', 'processing_days' => 'integer', 'is_active' => 'boolean'];
    }

    public function applications()
    {
        return $this->hasMany(LetterApplication::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
