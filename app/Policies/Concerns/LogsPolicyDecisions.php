<?php

namespace App\Policies\Concerns;

use App\Domain\Identity\Enums\Role;
use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * IMPLEMENTATION_PLAN.md Milestone M3.2 introduced this in "log-only
 * mode" (every method logged its decision but always returned true).
 * Milestone M3.3 enforces it for real: logDecision() now returns
 * $wouldAllow. Denials log at `warning`, allows at `info`, per
 * ARCHITECTURE_BLUEPRINT.md §8.8 ("Setiap penolakan otorisasi —
 * warning").
 *
 * Extracted here because six Policies each need this same logging
 * boilerplate (Blueprint P1: justified by duplication that would
 * otherwise exist in ≥2 places — here, six).
 */
trait LogsPolicyDecisions
{
    protected function hasAnyRole(User $user, array $roles): bool
    {
        $role = Role::tryFrom($user->job_title);

        return $role !== null && in_array($role, $roles, true);
    }

    protected function logDecision(string $action, User $user, bool $wouldAllow): bool
    {
        $context = [
            'policy' => static::class,
            'action' => $action,
            'user_id' => $user->id,
            'job_title' => $user->job_title,
            'would_allow' => $wouldAllow,
        ];

        $wouldAllow
            ? Log::info('policy.decision', $context)
            : Log::warning('policy.decision', $context);

        return $wouldAllow;
    }
}
