<?php
namespace App\Models;
class GalleryItem extends CmsModel { public function gallery(){return $this->belongsTo(Gallery::class);} public function media(){return $this->belongsTo(Media::class);} }
