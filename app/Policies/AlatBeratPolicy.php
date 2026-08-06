<?php

namespace App\Policies;

use App\Domain\Identity\Enums\Role;
use App\Models\AlatBerats;
use App\Models\User;
use App\Policies\Concerns\LogsPolicyDecisions;

/**
 * Master equipment data. Matrix is the business-approved authorization
 * matrix (superseding this Policy's original M3.2 draft, which had
 * LOGISTIK as view-only here): HSSE and LOGISTIK both get full CRUD;
 * DOKUMEN CONTROL (Role::DOKON) gets read only. Enforced as of
 * IMPLEMENTATION_PLAN.md Milestone M3.3 — see LogsPolicyDecisions.
 */
class AlatBeratPolicy
{
    use LogsPolicyDecisions;

    private const MANAGE_ROLES = [Role::HSSE, Role::LOGISTIK];

    private const VIEW_ROLES = [Role::HSSE, Role::LOGISTIK, Role::DOKON];

    public function viewAny(User $user): bool
    {
        return $this->logDecision('viewAny', $user, $this->hasAnyRole($user, self::VIEW_ROLES));
    }

    public function view(User $user, AlatBerats $alatBerat): bool
    {
        return $this->logDecision('view', $user, $this->hasAnyRole($user, self::VIEW_ROLES));
    }

    public function create(User $user): bool
    {
        return $this->logDecision('create', $user, $this->hasAnyRole($user, self::MANAGE_ROLES));
    }

    public function update(User $user, AlatBerats $alatBerat): bool
    {
        return $this->logDecision('update', $user, $this->hasAnyRole($user, self::MANAGE_ROLES));
    }

    public function delete(User $user, AlatBerats $alatBerat): bool
    {
        return $this->logDecision('delete', $user, $this->hasAnyRole($user, self::MANAGE_ROLES));
    }
}
