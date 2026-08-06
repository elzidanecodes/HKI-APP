<?php

namespace App\Policies;

use App\Domain\Identity\Enums\Role;
use App\Models\Silos;
use App\Models\User;
use App\Policies\Concerns\LogsPolicyDecisions;

/**
 * SILO legal documents. Matrix is the business-approved authorization
 * matrix: HSSE gets full CRUD; DOKUMEN CONTROL (Role::DOKON) gets read
 * only; LOGISTIK gets no access at all — matching TECHNICAL_AUDIT.md
 * C3's explicit naming of LOGISTIK's prior full CRUD access here as the
 * problem this Policy exists to close. Enforced as of
 * IMPLEMENTATION_PLAN.md Milestone M3.3 — see LogsPolicyDecisions.
 */
class SiloPolicy
{
    use LogsPolicyDecisions;

    private const MANAGE_ROLES = [Role::HSSE];

    private const VIEW_ROLES = [Role::HSSE, Role::DOKON];

    public function viewAny(User $user): bool
    {
        return $this->logDecision('viewAny', $user, $this->hasAnyRole($user, self::VIEW_ROLES));
    }

    public function view(User $user, Silos $silo): bool
    {
        return $this->logDecision('view', $user, $this->hasAnyRole($user, self::VIEW_ROLES));
    }

    public function create(User $user): bool
    {
        return $this->logDecision('create', $user, $this->hasAnyRole($user, self::MANAGE_ROLES));
    }

    public function update(User $user, Silos $silo): bool
    {
        return $this->logDecision('update', $user, $this->hasAnyRole($user, self::MANAGE_ROLES));
    }

    public function delete(User $user, Silos $silo): bool
    {
        return $this->logDecision('delete', $user, $this->hasAnyRole($user, self::MANAGE_ROLES));
    }
}
