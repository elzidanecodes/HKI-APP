<?php

namespace Tests\Feature\Policies;

use App\Models\Logistiks;
use App\Models\User;
use App\Policies\LogistikPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\Feature\Policies\Concerns\AssertsPolicyDecisions;
use Tests\TestCase;

/**
 * IMPLEMENTATION_PLAN.md Milestone M3.3: role x entity x action matrix
 * for Logistik, enforced. LOGISTIK manages; HSSE and DOKUMEN CONTROL
 * (DOKON) get read access only — the business-approved authorization
 * matrix (LogistikPolicy's own doc comment explains why). No factory
 * exists yet for Logistiks (out of this milestone's scope), so the test
 * record is created directly via its $fillable attributes.
 */
class LogistikPolicyTest extends TestCase
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
        $logistik = Logistiks::create([
            'nama_barang' => 'Semen',
            'kategori_barang' => 'Material',
            'deskripsi_barang' => 'Semen Portland',
            'jumlah_barang' => 100,
            'satuan' => 'sak',
            'lokasi' => 'Gudang A',
            'nama_vendor' => 'PT Vendor',
        ]);

        $expected = [
            'HSSE' => false,
            'LOGISTIK' => true,
            'DOKON' => false,
        ];

        foreach ($expected as $jobTitle => $canManage) {
            $user = $this->userWithRole($jobTitle);

            $this->assertTrue($user->can('viewAny', Logistiks::class));
            $this->assertPolicyDecisionLogged($user, LogistikPolicy::class, 'viewAny', true);

            $this->assertTrue($user->can('view', $logistik));
            $this->assertPolicyDecisionLogged($user, LogistikPolicy::class, 'view', true);

            $this->assertSame($canManage, $user->can('create', Logistiks::class));
            $this->assertPolicyDecisionLogged($user, LogistikPolicy::class, 'create', $canManage);

            $this->assertSame($canManage, $user->can('update', $logistik));
            $this->assertPolicyDecisionLogged($user, LogistikPolicy::class, 'update', $canManage);

            $this->assertSame($canManage, $user->can('delete', $logistik));
            $this->assertPolicyDecisionLogged($user, LogistikPolicy::class, 'delete', $canManage);
        }
    }
}
