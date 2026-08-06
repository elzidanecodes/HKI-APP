<?php

namespace Tests\Feature\Policies;

use App\Models\AlatBerats;
use App\Models\Silos;
use App\Models\User;
use App\Policies\SiloPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\Feature\Policies\Concerns\AssertsPolicyDecisions;
use Tests\TestCase;

/**
 * IMPLEMENTATION_PLAN.md Milestone M3.3: role x entity x action matrix
 * for SILO, enforced. HSSE manages; DOKUMEN CONTROL (DOKON) gets read
 * access only; LOGISTIK gets none at all — the business-approved
 * authorization matrix (SiloPolicy's own doc comment explains why).
 */
class SiloPolicyTest extends TestCase
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
        $silo = Silos::factory()->for(AlatBerats::factory(), 'alatBerat')->create();

        $expected = [
            'HSSE' => ['view' => true, 'manage' => true],
            'LOGISTIK' => ['view' => false, 'manage' => false],
            'DOKON' => ['view' => true, 'manage' => false],
        ];

        foreach ($expected as $jobTitle => $wouldAllow) {
            $user = $this->userWithRole($jobTitle);

            $this->assertSame($wouldAllow['view'], $user->can('viewAny', Silos::class));
            $this->assertPolicyDecisionLogged($user, SiloPolicy::class, 'viewAny', $wouldAllow['view']);

            $this->assertSame($wouldAllow['view'], $user->can('view', $silo));
            $this->assertPolicyDecisionLogged($user, SiloPolicy::class, 'view', $wouldAllow['view']);

            $this->assertSame($wouldAllow['manage'], $user->can('create', Silos::class));
            $this->assertPolicyDecisionLogged($user, SiloPolicy::class, 'create', $wouldAllow['manage']);

            $this->assertSame($wouldAllow['manage'], $user->can('update', $silo));
            $this->assertPolicyDecisionLogged($user, SiloPolicy::class, 'update', $wouldAllow['manage']);

            $this->assertSame($wouldAllow['manage'], $user->can('delete', $silo));
            $this->assertPolicyDecisionLogged($user, SiloPolicy::class, 'delete', $wouldAllow['manage']);
        }
    }
}
