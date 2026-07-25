<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Role extends CmsModel { use HasFactory; public function users() { return $this->hasMany(User::class); } }
