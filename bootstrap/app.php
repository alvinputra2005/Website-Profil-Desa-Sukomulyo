<?php

use App\Http\Middleware\EnsureActiveUser;
use App\Http\Middleware\HandleCmsRedirects;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\TrackSiteVisit;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias(['active' => EnsureActiveUser::class]);
        $middleware->web(prepend: [
            SecurityHeaders::class,
        ]);
        $middleware->web(append: [
            HandleCmsRedirects::class,
            TrackSiteVisit::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
