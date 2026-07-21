<?php
namespace App\Models;
class NewsCategory extends CmsModel { public function news(){return $this->hasMany(News::class,'category_id');} }
