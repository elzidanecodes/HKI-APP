<?php

namespace Tests\Feature\Application\Compliance;

use App\Application\Compliance\IssueDocument;
use App\Models\AlatBerats;
use App\Models\Operators;
use App\Models\Silos;
use App\Models\Sios;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

/**
 * IMPLEMENTATION_PLAN.md Milestone M4.4 / TECHNICAL_AUDIT.md H5:
 * ARCHITECTURE_BLUEPRINT.md §8.8 requires every state-changing operation
 * to log who/what/when/why at `info`. IssueDocument previously logged
 * nothing on success.
 */
class IssueDocumentLoggingTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        Log::spy();

        $this->user = User::factory()->create(['job_title' => 'HSSE']);
        $this->actingAs($this->user);
    }

    public function test_issuing_a_sio_logs_who_what_and_why(): void
    {
        $operator = Operators::factory()->create();

        $sio = new Sios([
            'operator_id' => $operator->id,
            'nomor_sio' => 'SIO-LOG-0001',
            'tanggal_expired' => now()->addYear()->toDateString(),
        ]);

        $issued = (new IssueDocument)->handle($sio);

        Log::shouldHaveReceived('info')->withArgs(
            function (string $message, array $context) use ($issued) {
                return $message === 'document.issued'
                    && $context['document_type'] === Sios::class
                    && $context['document_id'] === $issued->getKey()
                    && $context['attributes']['nomor_sio'] === 'SIO-LOG-0001'
                    && $context['user_id'] === $this->user->id;
            }
        )->atLeast()->once();
    }

    public function test_issuing_a_silo_logs_who_what_and_why(): void
    {
        $alatBerat = AlatBerats::factory()->create();

        $silo = new Silos([
            'alat_berat_id' => $alatBerat->id,
            'nomor_silo' => 'SILO-LOG-0001',
            'tanggal_terbit' => now()->toDateString(),
        ]);

        $issued = (new IssueDocument)->handle($silo);

        Log::shouldHaveReceived('info')->withArgs(
            function (string $message, array $context) use ($issued) {
                return $message === 'document.issued'
                    && $context['document_type'] === Silos::class
                    && $context['document_id'] === $issued->getKey()
                    && $context['attributes']['nomor_silo'] === 'SILO-LOG-0001'
                    && $context['user_id'] === $this->user->id;
            }
        )->atLeast()->once();
    }

    public function test_nothing_is_logged_when_authorization_is_denied(): void
    {
        $this->actingAs(User::factory()->create(['job_title' => 'LOGISTIK']));

        $alatBerat = AlatBerats::factory()->create();

        $silo = new Silos([
            'alat_berat_id' => $alatBerat->id,
            'nomor_silo' => 'SILO-LOG-0002',
            'tanggal_terbit' => now()->toDateString(),
        ]);

        try {
            (new IssueDocument)->handle($silo);
            $this->fail('Expected an AuthorizationException.');
        } catch (\Illuminate\Auth\Access\AuthorizationException $exception) {
            // Expected — denial is already logged by the Policy layer
            // (LogsPolicyDecisions), not duplicated here.
        }

        Log::shouldNotHaveReceived('info');
    }
}
