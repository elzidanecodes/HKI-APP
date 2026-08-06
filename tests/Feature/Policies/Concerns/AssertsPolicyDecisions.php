<?php

namespace Tests\Feature\Policies\Concerns;

use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * IMPLEMENTATION_PLAN.md Milestone M3.2 introduced this checking logged
 * output only (Policies were log-only, always returning true). Milestone
 * M3.3 enforces for real, so matrix tests now assert the actual Gate
 * result via $user->can(...) as the primary check — this helper now
 * verifies observability continues, not correctness: denials must log at
 * `warning`, allows at `info` (ARCHITECTURE_BLUEPRINT.md §8.8).
 */
trait AssertsPolicyDecisions
{
    private function assertPolicyDecisionLogged(User $user, string $policyClass, string $action, bool $expectedWouldAllow): void
    {
        $level = $expectedWouldAllow ? 'info' : 'warning';

        Log::shouldHaveReceived($level)->withArgs(
            function (string $message, array $context) use ($policyClass, $action, $user, $expectedWouldAllow) {
                return $message === 'policy.decision'
                    && $context['policy'] === $policyClass
                    && $context['action'] === $action
                    && $context['user_id'] === $user->id
                    && $context['would_allow'] === $expectedWouldAllow;
            }
        )->atLeast()->once();
    }
}
