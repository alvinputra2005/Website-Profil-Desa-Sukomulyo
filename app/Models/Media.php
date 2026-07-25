<?php
namespace App\Models;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
class Media extends CmsModel { use SoftDeletes; protected $table='media'; public function uploader(){return $this->belongsTo(User::class,'uploaded_by');} public function getUrlAttribute(): string { if($this->disk==='public')return '/storage/'.ltrim($this->storage_path,'/');if($this->disk==='r2'&&config('filesystems.disks.r2.proxy'))return route('media.file',[$this,'original']);return Storage::disk($this->disk)->url($this->storage_path); } public function getThumbnailUrlAttribute(): string { if(!$this->thumbnail_path)return $this->url;if($this->disk==='public')return '/storage/'.ltrim($this->thumbnail_path,'/');if($this->disk==='r2'&&config('filesystems.disks.r2.proxy'))return route('media.file',[$this,'thumbnail']);return Storage::disk($this->disk)->url($this->thumbnail_path); } }
