<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set(
            'Permissions-Policy',
            'camera=(), microphone=(), geolocation=()'
        );
        $response->headers->set('Content-Security-Policy', $this->contentSecurityPolicy());

        if ($request->is('admin', 'admin/*')) {
            $response->headers->set('X-Robots-Tag', 'noindex, nofollow');
        }

        if ($request->is('layanan-surat/lacak', 'layanan-surat/lacak/*', 'layanan-surat/t/*')) {
            $response->headers->set('X-Robots-Tag', 'noindex, nofollow');
            $response->headers->set('Cache-Control', 'no-store, private');
            $response->headers->set('Pragma', 'no-cache');
        }

        if (config('app.env') === 'production' && $request->isSecure()) {
            $response->headers->set(
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains'
            );
        }

        return $response;
    }

    private function contentSecurityPolicy(): string
    {
        $viteOrigin = $this->viteDevServerOrigin();
        $scriptSources = ["'self'", "'unsafe-inline'"];
        $styleSources = ["'self'", "'unsafe-inline'", 'https://fonts.googleapis.com'];
        $connectSources = ["'self'"];
        $imageSources = ["'self'", 'data:', 'https:'];
        $letterDocumentsOrigin = $this->letterDocumentsOrigin();

        if ($viteOrigin !== null) {
            $scriptSources[] = $viteOrigin;
            $styleSources[] = $viteOrigin;
            $connectSources[] = $viteOrigin;
            $connectSources[] = preg_replace('/^http/', 'ws', $viteOrigin);
            $imageSources[] = $viteOrigin;
        }

        if ($letterDocumentsOrigin !== null) {
            $connectSources[] = $letterDocumentsOrigin;
        }

        return implode('; ', [
            "default-src 'self'",
            "base-uri 'self'",
            "form-action 'self'",
            "frame-ancestors 'self'",
            "object-src 'none'",
            'script-src '.implode(' ', $scriptSources),
            'style-src '.implode(' ', $styleSources),
            "font-src 'self' data: https://fonts.gstatic.com",
            'img-src '.implode(' ', $imageSources),
            "frame-src 'self' https://www.google.com",
            'connect-src '.implode(' ', $connectSources),
            "media-src 'self'",
        ]);
    }

    private function letterDocumentsOrigin(): ?string
    {
        if (config('filesystems.letter_documents_disk') !== 'r2_letters') {
            return null;
        }

        $endpoint = (string) config('filesystems.disks.r2_letters.endpoint');
        $bucket = trim((string) config('filesystems.disks.r2_letters.bucket'));
        $parts = parse_url($endpoint);

        if (
            $bucket === ''
            || ! is_array($parts)
            || ($parts['scheme'] ?? null) !== 'https'
            || empty($parts['host'])
            || ! str_ends_with($parts['host'], '.r2.cloudflarestorage.com')
        ) {
            return null;
        }

        return 'https://'.$bucket.'.'.$parts['host'];
    }

    private function viteDevServerOrigin(): ?string
    {
        if (! app()->environment('local')) {
            return null;
        }

        $hotFile = public_path('hot');

        if (! is_file($hotFile)) {
            return null;
        }

        $url = trim((string) file_get_contents($hotFile));
        $parts = parse_url($url);

        if (
            ! is_array($parts)
            || ! in_array($parts['scheme'] ?? null, ['http', 'https'], true)
            || empty($parts['host'])
        ) {
            return null;
        }

        $origin = $parts['scheme'].'://'.$parts['host'];

        if (isset($parts['port'])) {
            $origin .= ':'.$parts['port'];
        }

        return $origin;
    }
}
