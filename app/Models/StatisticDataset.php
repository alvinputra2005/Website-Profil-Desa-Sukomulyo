<?php
namespace App\Models;
class StatisticDataset extends CmsModel { public function values(){return $this->hasMany(StatisticValue::class,'dataset_id')->orderBy('display_order');} public function creator(){return $this->belongsTo(User::class,'created_by');} }
