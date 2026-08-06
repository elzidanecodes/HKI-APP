<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * routes/web.php registers two GET '/' routes (TECHNICAL_AUDIT.md
     * finding L1). Laravel's router keeps the last-registered route for a
     * given method+URI, so the second one — a redirect to the Filament
     * admin panel — is the one actually reachable, not the first, which
     * returns the login view. This asserts what the route currently does,
     * not what the duplicate registration was probably meant to do.
     */
    public function test_the_root_route_redirects_to_the_admin_panel(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/admin');
    }
}
