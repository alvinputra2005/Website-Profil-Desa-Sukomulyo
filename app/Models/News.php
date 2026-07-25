<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
class News extends CmsModel { use SoftDeletes; protected $table='news'; protected $casts=['published_at'=>'datetime']; public function category(){return $this->belongsTo(NewsCategory::class);} public function author(){return $this->belongsTo(User::class,'author_id');} public function featuredImage(){return $this->belongsTo(Media::class,'featured_image_id');} public function scopePublished(Builder $q): Builder { return $q->where('status','published')->where(fn($x)=>$x->whereNull('published_at')->orWhere('published_at','<=',now())); } }
