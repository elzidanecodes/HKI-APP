<?php

namespace Tests\Feature\Policies;

use App\Models\Operators;
use App\Models\Sios;
use App\Models\User;
use App\Policies\SioPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\Feature\Policies\Concerns\AssertsPolicyDecisions;
use Tests\TestCase;

/**
 * IMPLEMENTATION_PLAN.md Milestone M3.3: role x entity x action matrix
 * for SIO, enforced. Same shape as SiloPolicyTest, for the same reason
 * (SioPolicy's own doc comment explains why LOGISTIK gets no access).
 */
class SioPolicyTest extends TestCase
{
    use AssertsPolicyDecisions;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Log::spy();
    }

    private function userWithRole(string $jobTitle): User
    {
        return User::factory()->create(['job_title' => $jobTitle]);
    }

    public function test_matrix_for_every_role(): void
    {
        $sio = Sios::factory()->for(Operators::factory(), 'operator')->create();

        $expected = [
            'HSSE' => ['view' => true, 'manage' => true],
            'LOGISTIK' => ['view' => false, 'manage' => false],
            'DOKON' => ['view' => true, 'manage' => false],
        ];

        foreach ($expected as $jobTitle => $wouldAllow) {
            $user = $this->userWithRole($jobTitle);

            $this->assertSame($wouldAllow['view'], $user->can('viewAny', Sios::class));
            $this->assertPolicyDecisionLogged($user, SioPolicy::class, 'viewAny', $wouldAllow['view']);

            $this->assertSame($wouldAllow['view'], $user->can('view', $sio));
            $this->assertPolicyDecisionLogged($user, SioPolicy::class, 'view', $wouldAllow['view']);

            $this->assertSame($wouldAllow['manage'], $user->can('create', Sios::class));
            $this->assertPolicyDecisionLogged($user, SioPolicy::class, 'create', $wouldAllow['manage']);

            $this->assertSame($wouldAllow['manage'], $user->can('update', $sio));
            $this->assertPolicyDecisionLogged($user, SioPolicy::class, 'update', $wouldAllow['manage']);

            $this->assertSame($wouldAllow['manage'], $user->can('delete', $sio));
            $this->assertPolicyDecisionLogged($user, SioPolicy::class, 'delete', $wouldAllow['manage']);
        }
    }
}
