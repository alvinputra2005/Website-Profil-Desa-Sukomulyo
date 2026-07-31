<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_responses_include_security_headers_and_compatible_csp(): void
    {
        $response = $this->get(route('beranda'));

        $response
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        $policy = (string) $response->headers->get('Content-Security-Policy');

        $this->assertStringContainsString("default-src 'self'", $policy);
        $this->assertStringContainsString("object-src 'none'", $policy);
        $this->assertStringContainsString("base-uri 'self'", $policy);
        $this->assertStringContainsString("form-action 'self' https://wa.me", $policy);
        $this->assertStringContainsString("frame-ancestors 'self'", $policy);
        $this->assertStringContainsString('https://fonts.googleapis.com', $policy);
        $this->assertStringContainsString('https://fonts.gstatic.com', $policy);
        $this->assertStringContainsString('https://www.google.com', $policy);
        $this->assertStringContainsString('blob:', $policy);
        $this->assertStringNotContainsString('*', $policy);
    }

    public function test_security_headers_are_also_applied_to_error_responses(): void
    {
        $this->get('/halaman-yang-tidak-ada')
            ->assertNotFound()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('Content-Security-Policy');
    }

    public function test_local_csp_allows_the_active_vite_development_server(): void
    {
        $hotFile = public_path('hot');
        $originalContents = is_file($hotFile) ? file_get_contents($hotFile) : null;
        $originalEnvironment = app()->environment();

        file_put_contents($hotFile, 'http://192.168.137.1:5173');
        app()->detectEnvironment(fn () => 'local');

        try {
            $policy = (string) $this->get(route('beranda'))
                ->assertOk()
                ->headers->get('Content-Security-Policy');

            $this->assertStringContainsString(
                "script-src 'self' 'unsafe-inline' http://192.168.137.1:5173",
                $policy
            );
            $this->assertStringContainsString(
                "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com http://192.168.137.1:5173",
                $policy
            );
            $this->assertStringContainsString(
                "img-src 'self' data: https: http://192.168.137.1:5173",
                $policy
            );
            $this->assertStringContainsString(
                "connect-src 'self' http://192.168.137.1:5173 ws://192.168.137.1:5173",
                $policy
            );
        } finally {
            app()->detectEnvironment(fn () => $originalEnvironment);

            if ($originalContents === null) {
                @unlink($hotFile);
            } else {
                file_put_contents($hotFile, $originalContents);
            }
        }
    }

    public function test_admin_responses_send_an_http_noindex_header(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow')
            ->assertHeader('Cache-Control', 'no-store, private')
            ->assertHeader('Pragma', 'no-cache');

        $this->get(route('admin.dashboard'))
            ->assertRedirect(route('login'))
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow')
            ->assertHeader('Cache-Control', 'no-store, private')
            ->assertHeader('Pragma', 'no-cache');
    }

    public function test_hsts_is_only_sent_for_secure_production_requests(): void
    {
        $this->get(route('beranda'))
            ->assertHeaderMissing('Strict-Transport-Security');

        $originalEnvironment = config('app.env');
        Config::set('app.env', 'production');

        try {
            $this->get('https://localhost/')
                ->assertHeader(
                    'Strict-Transport-Security',
                    'max-age=31536000; includeSubDomains'
                );
        } finally {
            Config::set('app.env', $originalEnvironment);
        }
    }
}
