<?php

namespace App\Domain\Identity\Enums;

/**
 * TECHNICAL_AUDIT.md finding C3: "Model peran sudah dirancang (job_title)
 * tetapi tidak pernah ditegakkan" — users.job_title is validated at
 * registration (app/Actions/Fortify/CreateNewUser.php) and never read
 * again anywhere in the codebase. This enum gives that existing field a
 * real type; Policies enforcing it are Milestone M3.2 onward, not this
 * one (IMPLEMENTATION_PLAN.md Milestone M3.1 — XS, enum only).
 *
 * Case values match users.job_title's existing DB-level
 * enum('HSSE', 'LOGISTIK', 'DOKON') exactly, so Role::from($user->job_title)
 * is the whole mapping — no translation table needed.
 *
 * "DOKON" is un-defined in the codebase; TECHNICAL_AUDIT.md's own noted
 * assumption is "Dokumen & Kontrak", unconfirmed with the business owner.
 */
enum Role: string
{
    case HSSE = 'HSSE';
    case LOGISTIK = 'LOGISTIK';
    case DOKON = 'DOKON';
}
