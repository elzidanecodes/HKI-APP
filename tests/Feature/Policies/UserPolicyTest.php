<?php

namespace Tests\Feature\Policies;

use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\Feature\Policies\Concerns\AssertsPolicyDecisions;
use Tests\TestCase;

/**
 * IMPLEMENTATION_PLAN.md Milestone M3.3: role x entity x action matrix
 * for User, enforced. No role manages other users — every user may only
 * view/update their own record, per the business-approved authorization
 * matrix (UserPolicy's own doc comment explains why).
 */
class UserPolicyTest extends TestCase
{
    use AssertsPolicyDecisions;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Log::spy();
    }

    public function test_matrix_for_every_role(): void
    {
        foreach (['HSSE', 'LOGISTIK', 'DOKON'] as $jobTitle) {
            $user = User::factory()->create(['job_title' => $jobTitle]);
            $otherUser = User::factory()->create(['job_title' => $jobTitle]);

            $this->assertFalse($user->can('viewAny', User::class));
            $this->assertPolicyDecisionLogged($user, UserPolicy::class, 'viewAny', false);

            $this->assertTrue($user->can('view', $user));
            $this->assertPolicyDecisionLogged($user, UserPolicy::class, 'view', true);

            $this->assertFalse($user->can('view', $otherUser));
            $this->assertPolicyDecisionLogged($user, UserPolicy::class, 'view', false);

            $this->assertFalse($user->can('create', User::class));
            $this->assertPolicyDecisionLogged($user, UserPolicy::class, 'create', false);

            $this->assertTrue($user->can('update', $user));
            $this->assertPolicyDecisionLogged($user, UserPolicy::class, 'update', true);

            $this->assertFalse($user->can('update', $otherUser));
            $this->assertPolicyDecisionLogged($user, UserPolicy::class, 'update', false);

            $this->assertFalse($user->can('delete', $otherUser));
            $this->assertPolicyDecisionLogged($user, UserPolicy::class, 'delete', false);
        }
    }
}
