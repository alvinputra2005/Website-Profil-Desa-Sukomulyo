<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
class ActivityController extends Controller { public function __invoke(){abort_unless(auth()->user()->can('manage-users'),403);return view('admin.activities',['items'=>ActivityLog::with('user')->latest('created_at')->paginate(30)]);} }
