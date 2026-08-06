<?php

namespace App\Domain\Operations\Exceptions;

/**
 * Thrown by Application\Operations\AssignOperator when
 * AssignmentEligibility reports the assignment is not eligible. Carries
 * the structured reason codes (not just a message) so a future caller —
 * the Filament cutover in Milestone M2.5 — can map each one to its own
 * notification, matching the four distinct messages the current
 * RelationManager shows today.
 */
final class AssignmentIneligible extends \DomainException
{
    /**
     * @param  string[]  $reasons
     */
    public function __construct(private readonly array $reasons)
    {
        parent::__construct('Assignment ineligible: '.implode(', ', $reasons));
    }

    /**
     * @return string[]
     */
    public function reasons(): array
    {
        return $this->reasons;
    }
}
