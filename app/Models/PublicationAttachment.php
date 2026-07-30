<?php

namespace App\Models;

class PublicationAttachment extends CmsModel
{
    protected $casts = [
        'display_order' => 'integer',
        'download_count' => 'integer',
    ];

    public function publication()
    {
        return $this->belongsTo(Publication::class);
    }

    public function media()
    {
        return $this->belongsTo(Media::class);
    }
}
