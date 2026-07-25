<?php
namespace App\Models;
class PublicationAttachment extends CmsModel { public function publication(){return $this->belongsTo(Publication::class);} public function media(){return $this->belongsTo(Media::class);} }
