<?php

namespace Tests\Feature\Application\Compliance;

use App\Application\Compliance\RenewDocument;
use App\Models\Operators;
use App\Models\Sios;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

/**
 * IMPLEMENTATION_PLAN.md Milestone M4.4 / TECHNICAL_AUDIT.md H5:
 * ARCHITECTURE_BLUEPRINT.md §8.8 requires every state-changing operation
 * to log who/what/when/why at `info`. RenewDocument previously logged
 * nothing on success — contrast with IssueDocumentLoggingTest, which
 * covers `document.issued`; this covers the distinct `document.renewed`
 * event RenewDocument emits.
 */
class RenewDocumentLoggingTest extends TestCase
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

    public function test_renewing_a_sio_logs_who_what_and_why(): void
    {
        $operator = Operators::factory()->create();
        Sios::factory()->for($operator, 'operator')->create([
            'tanggal_expired' => now()->subDay()->toDateString(),
        ]);

        $renewal = new Sios([
            'operator_id' => $operator->id,
            'nomor_sio' => 'SIO-RENEW-0001',
            'tanggal_expired' => now()->addYear()->toDateString(),
        ]);

        $renewed = (new RenewDocument)->handle($renewal);

        Log::shouldHaveReceived('info')->withArgs(
            function (string $message, array $context) use ($renewed) {
                return $message === 'document.renewed'
                    && $context['document_type'] === Sios::class
                    && $context['document_id'] === $renewed->getKey()
                    && $context['attributes']['nomor_sio'] === 'SIO-RENEW-0001'
                    && $context['user_id'] === $this->user->id;
            }
        )->atLeast()->once();
    }
}
