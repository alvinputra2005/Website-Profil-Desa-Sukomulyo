<?php
namespace App\Models;
use Illuminate\Database\Eloquent\SoftDeletes;
class Gallery extends CmsModel { use SoftDeletes; protected $casts=['event_date'=>'date']; public function cover(){return $this->belongsTo(Media::class,'cover_media_id');} public function items(){return $this->hasMany(GalleryItem::class)->orderBy('display_order');} public function creator(){return $this->belongsTo(User::class,'created_by');} }
