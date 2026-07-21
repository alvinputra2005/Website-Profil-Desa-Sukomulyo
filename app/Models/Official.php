<?php
namespace App\Models;
class Official extends CmsModel { protected $casts=['is_active'=>'boolean']; public function photo(){return $this->belongsTo(Media::class,'photo_id');} }
