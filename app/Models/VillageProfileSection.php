<?php
namespace App\Models;
class VillageProfileSection extends CmsModel { public function image(){return $this->belongsTo(Media::class,'image_id');} public function updater(){return $this->belongsTo(User::class,'updated_by');} }
