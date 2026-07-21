<?php
namespace App\Models;
class MapLayer extends CmsModel { protected $casts=['style_json'=>'array','is_visible'=>'boolean']; public function features(){return $this->hasMany(MapFeature::class,'layer_id');} }
