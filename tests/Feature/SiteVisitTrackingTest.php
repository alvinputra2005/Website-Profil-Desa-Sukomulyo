<?php

namespace Tests\Feature;

use App\Models\SiteVisit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiteVisitTrackingTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_visit_is_counted_once_per_session(): void
    {
        $this->get(route('beranda'))->assertOk();
        $this->get(route('profile-desa'))->assertOk();

        $this->assertDatabaseCount('site_visits', 1);
        $this->assertNotNull(SiteVisit::first()?->visited_at);
    }

    public function test_a_new_session_is_counted_as_a_new_visit(): void
    {
        $this->get(route('beranda'))->assertOk();
        $this->app['session']->invalidate();
        $this->get(route('beranda'))->assertOk();

        $this->assertDatabaseCount('site_visits', 2);
    }

    public function test_unsuccessful_public_request_is_not_counted(): void
    {
        $this->get('/halaman-yang-tidak-ada')->assertNotFound();

        $this->assertDatabaseCount('site_visits', 0);
    }
}
