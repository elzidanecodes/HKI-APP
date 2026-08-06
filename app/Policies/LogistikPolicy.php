<?php

namespace App\Policies;

use App\Domain\Identity\Enums\Role;
use App\Models\Logistiks;
use App\Models\User;
use App\Policies\Concerns\LogsPolicyDecisions;

/**
 * Logistics stock data. Matrix is the business-approved authorization
 * matrix: LOGISTIK gets full CRUD; HSSE gets view only; DOKUMEN CONTROL
 * (Role::DOKON) gets read only. Content unchanged from this Policy's
 * original M3.2 draft — only enforcement changed, per
 * IMPLEMENTATION_PLAN.md Milestone M3.3 — see LogsPolicyDecisions.
 *
 * deleteAny() added in Milestone M3.5 to gate LogistiksResource's
 * DeleteBulkAction — the same CRUD grant as delete() above, not a new
 * one; bulk delete is just a UI convenience for the same permission.
 */
class LogistikPolicy
{
    use LogsPolicyDecisions;

    private const MANAGE_ROLES = [Role::LOGISTIK];

    private const VIEW_ROLES = [Role::HSSE, Role::LOGISTIK, Role::DOKON];

    public function viewAny(User $user): bool
    {
        return $this->logDecision('viewAny', $user, $this->hasAnyRole($user, self::VIEW_ROLES));
    }

    public function view(User $user, Logistiks $logistik): bool
    {
        return $this->logDecision('view', $user, $this->hasAnyRole($user, self::VIEW_ROLES));
    }

    public function create(User $user): bool
    {
        return $this->logDecision('create', $user, $this->hasAnyRole($user, self::MANAGE_ROLES));
    }

    public function update(User $user, Logistiks $logistik): bool
    {
        return $this->logDecision('update', $user, $this->hasAnyRole($user, self::MANAGE_ROLES));
    }

    public function delete(User $user, Logistiks $logistik): bool
    {
        return $this->logDecision('delete', $user, $this->hasAnyRole($user, self::MANAGE_ROLES));
    }

    public function deleteAny(User $user): bool
    {
        return $this->logDecision('deleteAny', $user, $this->hasAnyRole($user, self::MANAGE_ROLES));
    }
}
