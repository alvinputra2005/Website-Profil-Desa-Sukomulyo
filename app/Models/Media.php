<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Media extends CmsModel
{
    use SoftDeletes;

    protected $table = 'media';

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getUrlAttribute(): string
    {
        return $this->variantUrl($this->storage_path, 'original');
    }

    public function getMediumUrlAttribute(): string
    {
        return $this->medium_path ? $this->variantUrl($this->medium_path, 'medium') : $this->url;
    }

    public function getThumbnailUrlAttribute(): string
    {
        return $this->thumbnail_path ? $this->variantUrl($this->thumbnail_path, 'thumbnail') : $this->url;
    }

    private function variantUrl(string $path, string $variant): string
    {
        if ($this->disk === 'public') {
            return '/storage/'.ltrim($path, '/');
        }

        if ($this->disk === 'r2' && config('filesystems.disks.r2.proxy')) {
            return route('media.file', [$this, $variant]);
        }

        return Storage::disk($this->disk)->url($path);
    }
}
