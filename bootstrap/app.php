<?php

use App\Http\Middleware\EnsureActiveUser;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\TrackSiteVisit;
use App\Services\Web\PublicSiteService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
            TrackSiteVisit::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $renderAnnouncementNotFound = function (Request $request) {
            if (! $request->is('informasi-desa/pengumuman*') || $request->expectsJson()) {
                return null;
            }

            return app(PublicSiteService::class)->notFound();
        };

        $exceptions->render(
            fn (NotFoundHttpException $exception, Request $request) => $renderAnnouncementNotFound($request)
        );
        $exceptions->render(
            fn (ModelNotFoundException $exception, Request $request) => $renderAnnouncementNotFound($request)
        );
    })->create();
