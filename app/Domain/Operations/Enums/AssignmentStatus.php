<?php

namespace App\Domain\Operations\Enums;

/**
 * The assignment lifecycle (ARCHITECTURE_BLUEPRINT.md §4 Diagram 3):
 * Proposed -> Active -> Ended, or Proposed -> Rejected, or
 * Active -> AutoEnded. AssignmentEligibility (this milestone) only ever
 * produces Active or Rejected — Ended and AutoEnded belong to the
 * Application Actions that end an assignment (Milestone M2.4).
 */
enum AssignmentStatus: string
{
    case Proposed = 'proposed';
    case Active = 'active';
    case Rejected = 'rejected';
    case Ended = 'ended';
    case AutoEnded = 'auto_ended';
}
