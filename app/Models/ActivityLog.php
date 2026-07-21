<?php
namespace App\Models;
class ActivityLog extends CmsModel { public $timestamps=false; protected $casts=['old_values_json'=>'array','new_values_json'=>'array','created_at'=>'datetime']; public function user(){return $this->belongsTo(User::class);} }
