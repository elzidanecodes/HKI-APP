<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * routes/web.php used to register two GET '/' routes
     * (TECHNICAL_AUDIT.md finding L1) — Laravel's router keeps the
     * last-registered route for a given method+URI, so the redirect
     * below was already the one actually reachable; the other (returning
     * the login view directly) was dead code, removed in
     * IMPLEMENTATION_PLAN.md Milestone M5.1. This assertion is unchanged
     * by that removal — it already documented the route's real behavior.
     */
    public function test_the_root_route_redirects_to_the_admin_panel(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/admin');
    }
}
