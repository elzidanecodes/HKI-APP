<?php

namespace Tests\Feature\Policies;

use App\Models\Operators;
use App\Models\User;
use App\Policies\OperatorPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\Feature\Policies\Concerns\AssertsPolicyDecisions;
use Tests\TestCase;

/**
 * IMPLEMENTATION_PLAN.md Milestone M3.3: role x entity x action matrix
 * for Operator, enforced. HSSE gets full CRUD; DOKUMEN CONTROL (DOKON)
 * gets read only; LOGISTIK gets no access at all, not even viewing — the
 * business-approved authorization matrix (OperatorPolicy's own doc
 * comment explains why this differs from the Milestone M3.2 draft, which
 * had LOGISTIK as view-only).
 */
class OperatorPolicyTest extends TestCase
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
        $operator = Operators::factory()->create();

        $expected = [
            'HSSE' => ['view' => true, 'manage' => true],
            'LOGISTIK' => ['view' => false, 'manage' => false],
            'DOKON' => ['view' => true, 'manage' => false],
        ];

        foreach ($expected as $jobTitle => $wouldAllow) {
            $user = $this->userWithRole($jobTitle);

            $this->assertSame($wouldAllow['view'], $user->can('viewAny', Operators::class));
            $this->assertPolicyDecisionLogged($user, OperatorPolicy::class, 'viewAny', $wouldAllow['view']);

            $this->assertSame($wouldAllow['view'], $user->can('view', $operator));
            $this->assertPolicyDecisionLogged($user, OperatorPolicy::class, 'view', $wouldAllow['view']);

            $this->assertSame($wouldAllow['manage'], $user->can('create', Operators::class));
            $this->assertPolicyDecisionLogged($user, OperatorPolicy::class, 'create', $wouldAllow['manage']);

            $this->assertSame($wouldAllow['manage'], $user->can('update', $operator));
            $this->assertPolicyDecisionLogged($user, OperatorPolicy::class, 'update', $wouldAllow['manage']);

            $this->assertSame($wouldAllow['manage'], $user->can('delete', $operator));
            $this->assertPolicyDecisionLogged($user, OperatorPolicy::class, 'delete', $wouldAllow['manage']);
        }
    }
}
