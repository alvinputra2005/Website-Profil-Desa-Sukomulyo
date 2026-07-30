<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LetterApplicationDocument extends Model
{
    protected $fillable = ['public_id', 'letter_application_id', 'requirement_key', 'label', 'disk', 'path', 'original_name', 'stored_extension', 'mime_type', 'file_size', 'size_bytes', 'upload_status', 'review_status', 'review_note', 'admin_note', 'reviewed_by', 'reviewed_at', 'uploaded_at', 'checksum_sha256', 'etag'];
    protected $casts = ['reviewed_at' => 'datetime', 'uploaded_at' => 'datetime', 'size_bytes' => 'integer'];
    public function application() { return $this->belongsTo(LetterApplication::class, 'letter_application_id'); }
    public function reviewer() { return $this->belongsTo(User::class, 'reviewed_by'); }
    public function getRouteKeyName(): string { return 'public_id'; }
}
