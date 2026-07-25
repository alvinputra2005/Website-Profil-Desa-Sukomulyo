<?php
namespace App\Models;
class MapFeature extends CmsModel { protected $casts=['geometry_json'=>'array','properties_json'=>'array','is_visible'=>'boolean']; public function layer(){return $this->belongsTo(MapLayer::class);} public function photo(){return $this->belongsTo(Media::class,'photo_id');} }
