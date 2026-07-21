<?php
namespace App\Models;
class StatisticValue extends CmsModel { protected $casts=['metadata_json'=>'array']; public function dataset(){return $this->belongsTo(StatisticDataset::class);} }
