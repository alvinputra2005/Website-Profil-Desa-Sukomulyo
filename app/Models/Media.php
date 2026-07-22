<?php
namespace App\Models;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
class Media extends CmsModel { use SoftDeletes; protected $table='media'; public function uploader(){return $this->belongsTo(User::class,'uploaded_by');} public function getUrlAttribute(): string { return $this->disk==='public' ? '/storage/'.ltrim($this->storage_path,'/') : Storage::disk($this->disk)->url($this->storage_path); } }
