<?php

namespace App\Models;

use App\Enums\LetterApplicationStatus;
use Illuminate\Database\Eloquent\Model;

class LetterApplicationStatusHistory extends Model
{
    public $timestamps = false;

    protected $fillable = ['from_status', 'to_status', 'public_note', 'internal_note', 'changed_by', 'metadata_json', 'created_at'];

    protected function casts(): array
    {
        return ['from_status' => LetterApplicationStatus::class, 'to_status' => LetterApplicationStatus::class, 'metadata_json' => 'array', 'created_at' => 'datetime'];
    }

    public function application()
    {
        return $this->belongsTo(LetterApplication::class, 'letter_application_id');
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
