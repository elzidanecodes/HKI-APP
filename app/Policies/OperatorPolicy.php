<?php

namespace App\Policies;

use App\Domain\Identity\Enums\Role;
use App\Models\Operators;
use App\Models\User;
use App\Policies\Concerns\LogsPolicyDecisions;

/**
 * Master operator data. Matrix is the business-approved authorization
 * matrix (superseding this Policy's original M3.2 draft, which had
 * LOGISTIK as view-only here): HSSE gets full CRUD; DOKUMEN CONTROL
 * (Role::DOKON) gets read only; LOGISTIK gets no access at all, not even
 * viewing. Enforced as of IMPLEMENTATION_PLAN.md Milestone M3.3 — see
 * LogsPolicyDecisions.
 */
class OperatorPolicy
{
    use LogsPolicyDecisions;

    private const MANAGE_ROLES = [Role::HSSE];

    private const VIEW_ROLES = [Role::HSSE, Role::DOKON];

    public function viewAny(User $user): bool
    {
        return $this->logDecision('viewAny', $user, $this->hasAnyRole($user, self::VIEW_ROLES));
    }

    public function view(User $user, Operators $operator): bool
    {
        return $this->logDecision('view', $user, $this->hasAnyRole($user, self::VIEW_ROLES));
    }

    public function create(User $user): bool
    {
        return $this->logDecision('create', $user, $this->hasAnyRole($user, self::MANAGE_ROLES));
    }

    public function update(User $user, Operators $operator): bool
    {
        return $this->logDecision('update', $user, $this->hasAnyRole($user, self::MANAGE_ROLES));
    }

    public function delete(User $user, Operators $operator): bool
    {
        return $this->logDecision('delete', $user, $this->hasAnyRole($user, self::MANAGE_ROLES));
    }
}
