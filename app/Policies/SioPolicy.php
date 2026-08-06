<?php

namespace App\Policies;

use App\Domain\Identity\Enums\Role;
use App\Models\Sios;
use App\Models\User;
use App\Policies\Concerns\LogsPolicyDecisions;

/**
 * SIO legal documents. Same shape as SiloPolicy, for the same reason —
 * see that class's doc comment. Enforced as of IMPLEMENTATION_PLAN.md
 * Milestone M3.3 — see LogsPolicyDecisions.
 */
class SioPolicy
{
    use LogsPolicyDecisions;

    private const MANAGE_ROLES = [Role::HSSE];

    private const VIEW_ROLES = [Role::HSSE, Role::DOKON];

    public function viewAny(User $user): bool
    {
        return $this->logDecision('viewAny', $user, $this->hasAnyRole($user, self::VIEW_ROLES));
    }

    public function view(User $user, Sios $sio): bool
    {
        return $this->logDecision('view', $user, $this->hasAnyRole($user, self::VIEW_ROLES));
    }

    public function create(User $user): bool
    {
        return $this->logDecision('create', $user, $this->hasAnyRole($user, self::MANAGE_ROLES));
    }

    public function update(User $user, Sios $sio): bool
    {
        return $this->logDecision('update', $user, $this->hasAnyRole($user, self::MANAGE_ROLES));
    }

    public function delete(User $user, Sios $sio): bool
    {
        return $this->logDecision('delete', $user, $this->hasAnyRole($user, self::MANAGE_ROLES));
    }
}
