<?php

namespace App\Domain\Compliance\ValueObjects;

use App\Domain\Compliance\Enums\DocumentStatus;
use Carbon\CarbonInterface;

/**
 * Single source of truth for "is this document valid?" — replacing the
 * six independent implementations TECHNICAL_AUDIT.md §3 ("Duplikasi #1")
 * identifies, one of which (the scheduler) had already diverged from the
 * rest (finding C1).
 */
final class DocumentValidity
{
    public function __construct(
        private readonly ValidityPeriod $period,
        private readonly int $expiringSoonThresholdDays,
    ) {}

    public static function forPeriod(ValidityPeriod $period, int $expiringSoonThresholdDays): self
    {
        return new self($period, $expiringSoonThresholdDays);
    }

    public function isValidOn(CarbonInterface $today): bool
    {
        return $this->period->isActiveOn($today);
    }

    public function status(CarbonInterface $today): DocumentStatus
    {
        if ($this->period->isExpiredOn($today)) {
            return DocumentStatus::Expired;
        }

        if ($this->period->isExpiringSoonOn($today, $this->expiringSoonThresholdDays)) {
            return DocumentStatus::ExpiringSoon;
        }

        return DocumentStatus::Active;
    }

    public function remainingDaysOn(CarbonInterface $today): int
    {
        return $this->period->remainingDays($today);
    }

    public function expiresAt(): CarbonInterface
    {
        return $this->period->expiresAt;
    }

    /**
     * Whether at least one document in the set is valid — the correct
     * quantifier for "does this operator/equipment have a currently valid
     * document?" (¬∃ valid, negated). TECHNICAL_AUDIT.md finding C1: the
     * scheduler instead asked "∃ an *expired* document?", which is true
     * for anyone who has ever renewed, since expired documents are kept
     * on file for audit history. Any code answering that question must
     * call this, not re-implement the check.
     *
     * @param  DocumentValidity[]  $validities
     */
    public static function anyValid(array $validities, CarbonInterface $today): bool
    {
        foreach ($validities as $validity) {
            if ($validity->isValidOn($today)) {
                return true;
            }
        }

        return false;
    }
}
