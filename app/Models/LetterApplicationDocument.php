<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LetterApplicationDocument extends Model
{
    protected $fillable = ['letter_application_id', 'requirement_key', 'label', 'disk', 'path', 'original_name', 'mime_type', 'file_size', 'review_status', 'review_note', 'reviewed_by', 'reviewed_at'];
    protected $casts = ['reviewed_at' => 'datetime'];
    public function application() { return $this->belongsTo(LetterApplication::class, 'letter_application_id'); }
    public function reviewer() { return $this->belongsTo(User::class, 'reviewed_by'); }
}
