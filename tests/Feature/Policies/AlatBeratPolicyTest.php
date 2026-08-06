<?php

namespace Tests\Feature\Policies;

use App\Models\AlatBerats;
use App\Models\User;
use App\Policies\AlatBeratPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\Feature\Policies\Concerns\AssertsPolicyDecisions;
use Tests\TestCase;

/**
 * IMPLEMENTATION_PLAN.md Milestone M3.3: role x entity x action matrix
 * for AlatBerat, enforced. HSSE and LOGISTIK both get full CRUD; DOKUMEN
 * CONTROL (DOKON) gets read only — the business-approved authorization
 * matrix (AlatBeratPolicy's own doc comment explains why this differs
 * from the Milestone M3.2 draft).
 */
class AlatBeratPolicyTest extends TestCase
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
        $alatBerat = AlatBerats::factory()->create();

        $expected = [
            'HSSE' => true,
            'LOGISTIK' => true,
            'DOKON' => false,
        ];

        foreach ($expected as $jobTitle => $canManage) {
            $user = $this->userWithRole($jobTitle);

            // viewAny/view: everyone in the matrix can view.
            $this->assertTrue($user->can('viewAny', AlatBerats::class));
            $this->assertPolicyDecisionLogged($user, AlatBeratPolicy::class, 'viewAny', true);

            $this->assertTrue($user->can('view', $alatBerat));
            $this->assertPolicyDecisionLogged($user, AlatBeratPolicy::class, 'view', true);

            // create/update/delete: HSSE and LOGISTIK manage; DOKON does not.
            $this->assertSame($canManage, $user->can('create', AlatBerats::class));
            $this->assertPolicyDecisionLogged($user, AlatBeratPolicy::class, 'create', $canManage);

            $this->assertSame($canManage, $user->can('update', $alatBerat));
            $this->assertPolicyDecisionLogged($user, AlatBeratPolicy::class, 'update', $canManage);

            $this->assertSame($canManage, $user->can('delete', $alatBerat));
            $this->assertPolicyDecisionLogged($user, AlatBeratPolicy::class, 'delete', $canManage);
        }
    }
}
