<?php
namespace App\Http\Middleware;
use App\Models\Redirect;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;
class HandleCmsRedirects { public function handle(Request $request,Closure $next): Response { if(!$request->is('admin/*')&&$request->isMethod('GET')&&Schema::hasTable('redirects')){ $path='/'.ltrim($request->path(),'/'); if($redirect=Redirect::where('old_path',$path)->first()) return redirect($redirect->new_path,$redirect->status_code); } return $next($request); } }
