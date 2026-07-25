<?php
namespace App\Models;
class Setting extends CmsModel { protected $casts=['is_public'=>'boolean']; public function updater(){return $this->belongsTo(User::class,'updated_by');} }
