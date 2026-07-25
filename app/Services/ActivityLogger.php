<?php
namespace App\Services;
use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
final class ActivityLogger { public function log(string $action,string $module,?Model $record=null,?array $old=null,?array $new=null): void { ActivityLog::create(['user_id'=>auth()->id(),'action'=>$action,'module'=>$module,'record_type'=>$record?->getMorphClass(),'record_id'=>$record?->getKey(),'description'=>ucfirst($action).' '.$module,'old_values_json'=>$old,'new_values_json'=>$new,'ip_address'=>request()->ip(),'user_agent'=>substr((string)request()->userAgent(),0,1000),'created_at'=>now()]); } }
