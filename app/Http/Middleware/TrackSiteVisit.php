<?php

namespace App\Http\Middleware;

use App\Models\SiteVisit;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class TrackSiteVisit
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (
            $request->isMethod('GET')
            && ! $request->is('admin', 'admin/*', 'media-file/*')
            && ! auth()->check()
            && $response->isSuccessful()
            && Schema::hasTable('site_visits')
            && ! $request->session()->has('site_visit_recorded')
        ) {
            SiteVisit::firstOrCreate(
                ['session_hash' => hash('sha256', $request->session()->getId())],
                ['entry_path' => '/'.$request->path(), 'visited_at' => now()]
            );

            $request->session()->put('site_visit_recorded', true);
        }

        return $response;
    }
}
