<?php

namespace App\Policies;

use App\Models\User;
use App\Policies\Concerns\LogsPolicyDecisions;

/**
 * Users/accounts. Matrix is the business-approved authorization matrix:
 * every authenticated user manages only their own profile — no role gets
 * cross-user access. Content unchanged from this Policy's original M3.2
 * draft — only enforcement changed, per IMPLEMENTATION_PLAN.md Milestone
 * M3.3 — see LogsPolicyDecisions.
 */
class UserPolicy
{
    use LogsPolicyDecisions;

    public function viewAny(User $user): bool
    {
        return $this->logDecision('viewAny', $user, false);
    }

    public function view(User $user, User $model): bool
    {
        return $this->logDecision('view', $user, $user->id === $model->id);
    }

    public function create(User $user): bool
    {
        return $this->logDecision('create', $user, false);
    }

    public function update(User $user, User $model): bool
    {
        return $this->logDecision('update', $user, $user->id === $model->id);
    }

    public function delete(User $user, User $model): bool
    {
        return $this->logDecision('delete', $user, false);
    }
}
