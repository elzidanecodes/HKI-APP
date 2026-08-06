<?php

namespace App\Domain\Compliance\ValueObjects;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

/**
 * A document's issue-to-expiry date range. Time is always passed in by
 * the caller, never read from the system clock directly — this is what
 * makes "expired yesterday" testable without touching the clock (Blueprint P4).
 *
 * Note: only the expiry bound gates validity here, matching every
 * existing implementation this replaces — none of them check whether
 * startsAt has been reached yet, so this doesn't invent that behavior.
 */
final class ValidityPeriod
{
    public function __construct(
        public readonly CarbonImmutable $startsAt,
        public readonly CarbonImmutable $expiresAt,
    ) {}

    public static function fromDates(CarbonInterface $startsAt, CarbonInterface $expiresAt): self
    {
        return new self(
            CarbonImmutable::instance($startsAt),
            CarbonImmutable::instance($expiresAt),
        );
    }

    /**
     * Computes the expiry bound as an issue date plus a validity length,
     * replacing Silos::booted()'s addYear() computation
     * (TECHNICAL_AUDIT.md M6 — a regulatory rule hardcoded in a
     * persistence hook). The validity length is a plain parameter, never
     * resolved from application configuration here (Blueprint P4) — the
     * caller (Application layer) resolves it and passes it in.
     */
    public static function startingFrom(CarbonInterface $startsAt, int $validityMonths): self
    {
        $startsAt = CarbonImmutable::instance($startsAt);

        return new self(
            $startsAt,
            $startsAt->addMonths($validityMonths)->startOfDay(),
        );
    }

    public function isActiveOn(CarbonInterface $today): bool
    {
        return ! $this->expiresAt->lessThan($today);
    }

    public function isExpiredOn(CarbonInterface $today): bool
    {
        return $this->expiresAt->lessThan($today);
    }

    public function remainingDays(CarbonInterface $today): int
    {
        return $today->diffInDays($this->expiresAt, false);
    }

    public function isExpiringSoonOn(CarbonInterface $today, int $withinDays): bool
    {
        if ($this->isExpiredOn($today)) {
            return false;
        }

        return $this->remainingDays($today) <= $withinDays;
    }
}
