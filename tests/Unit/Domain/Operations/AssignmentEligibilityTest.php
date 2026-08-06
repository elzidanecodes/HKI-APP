<?php

namespace Tests\Unit\Domain\Operations;

use App\Domain\Compliance\ValueObjects\DocumentValidity;
use App\Domain\Compliance\ValueObjects\ValidityPeriod;
use App\Domain\Operations\Enums\AssignmentStatus;
use App\Domain\Operations\Services\AssignmentEligibility;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\TestCase;

/**
 * Pure unit tests — no database, no framework boot (plain
 * PHPUnit\Framework\TestCase, matching DocumentValidityTest's convention
 * from Milestone M2.2, not Tests\TestCase).
 */
class AssignmentEligibilityTest extends TestCase
{
    private function validityExpiringOn(CarbonImmutable $expiresAt): DocumentValidity
    {
        return DocumentValidity::forPeriod(
            ValidityPeriod::fromDates($expiresAt->subYear(), $expiresAt),
            30,
        );
    }

    public function test_eligible_when_both_documents_are_valid_and_neither_party_is_busy(): void
    {
        $today = CarbonImmutable::parse('2026-06-01');
        $eligibility = new AssignmentEligibility;

        $reasons = $eligibility->ineligibilityReasons(
            operatorSioValidities: [$this->validityExpiringOn($today->addMonths(6))],
            equipmentSiloValidities: [$this->validityExpiringOn($today->addMonths(6))],
            equipmentHasActiveAssignment: false,
            operatorHasActiveAssignmentElsewhere: false,
            today: $today,
        );

        $this->assertSame([], $reasons);
        $this->assertTrue($eligibility->isEligible(
            [$this->validityExpiringOn($today->addMonths(6))],
            [$this->validityExpiringOn($today->addMonths(6))],
            false,
            false,
            $today,
        ));
        $this->assertSame(AssignmentStatus::Active, $eligibility->status(
            [$this->validityExpiringOn($today->addMonths(6))],
            [$this->validityExpiringOn($today->addMonths(6))],
            false,
            false,
            $today,
        ));
    }

    public function test_ineligible_when_equipment_has_no_valid_silo(): void
    {
        $today = CarbonImmutable::parse('2026-06-01');
        $eligibility = new AssignmentEligibility;

        $reasons = $eligibility->ineligibilityReasons(
            operatorSioValidities: [$this->validityExpiringOn($today->addMonths(6))],
            equipmentSiloValidities: [],
            equipmentHasActiveAssignment: false,
            operatorHasActiveAssignmentElsewhere: false,
            today: $today,
        );

        $this->assertSame(['equipment_silo_not_active'], $reasons);
    }

    public function test_ineligible_when_equipment_already_has_an_active_assignment(): void
    {
        $today = CarbonImmutable::parse('2026-06-01');
        $eligibility = new AssignmentEligibility;

        $reasons = $eligibility->ineligibilityReasons(
            operatorSioValidities: [$this->validityExpiringOn($today->addMonths(6))],
            equipmentSiloValidities: [$this->validityExpiringOn($today->addMonths(6))],
            equipmentHasActiveAssignment: true,
            operatorHasActiveAssignmentElsewhere: false,
            today: $today,
        );

        $this->assertSame(['equipment_already_assigned'], $reasons);
    }

    public function test_ineligible_when_operator_is_already_assigned_elsewhere(): void
    {
        $today = CarbonImmutable::parse('2026-06-01');
        $eligibility = new AssignmentEligibility;

        $reasons = $eligibility->ineligibilityReasons(
            operatorSioValidities: [$this->validityExpiringOn($today->addMonths(6))],
            equipmentSiloValidities: [$this->validityExpiringOn($today->addMonths(6))],
            equipmentHasActiveAssignment: false,
            operatorHasActiveAssignmentElsewhere: true,
            today: $today,
        );

        $this->assertSame(['operator_already_assigned_elsewhere'], $reasons);
    }

    public function test_ineligible_when_operator_has_no_valid_sio(): void
    {
        $today = CarbonImmutable::parse('2026-06-01');
        $eligibility = new AssignmentEligibility;

        $reasons = $eligibility->ineligibilityReasons(
            operatorSioValidities: [],
            equipmentSiloValidities: [$this->validityExpiringOn($today->addMonths(6))],
            equipmentHasActiveAssignment: false,
            operatorHasActiveAssignmentElsewhere: false,
            today: $today,
        );

        $this->assertSame(['operator_sio_not_active'], $reasons);
    }

    public function test_all_four_reasons_are_reported_when_every_guard_fails(): void
    {
        // Matches current behavior characterized in
        // tests/Feature/Filament/AssignmentGuardTest.php: the guards are
        // not short-circuited, so more than one can fail at once.
        $today = CarbonImmutable::parse('2026-06-01');
        $eligibility = new AssignmentEligibility;

        $reasons = $eligibility->ineligibilityReasons(
            operatorSioValidities: [],
            equipmentSiloValidities: [],
            equipmentHasActiveAssignment: true,
            operatorHasActiveAssignmentElsewhere: true,
            today: $today,
        );

        $this->assertSame([
            'equipment_silo_not_active',
            'equipment_already_assigned',
            'operator_already_assigned_elsewhere',
            'operator_sio_not_active',
        ], $reasons);
        $this->assertSame(AssignmentStatus::Rejected, $eligibility->status([], [], true, true, $today));
    }

    // --- Audit-ID-named regression tests --------------------------------

    // TECHNICAL_AUDIT.md C1: the scheduler asks "does an EXPIRED SIO
    // exist?" instead of "does NO VALID SIO exist?", so it ends a
    // legitimate assignment the moment an operator's SIO is renewed (old
    // expired copy kept for audit history, new copy valid). This proves
    // AssignmentEligibility asks the correct question: the operator
    // remains eligible.
    public function test_c1_renewed_document_keeps_assignment_active(): void
    {
        $today = CarbonImmutable::parse('2026-06-01');
        $eligibility = new AssignmentEligibility;

        $oldExpiredSio = $this->validityExpiringOn($today->subMonths(6));
        $renewedValidSio = $this->validityExpiringOn($today->addMonths(6));

        $result = $eligibility->isEligible(
            operatorSioValidities: [$oldExpiredSio, $renewedValidSio],
            equipmentSiloValidities: [$this->validityExpiringOn($today->addMonths(6))],
            equipmentHasActiveAssignment: false,
            operatorHasActiveAssignmentElsewhere: false,
            today: $today,
        );

        $this->assertTrue($result);
    }

    // TECHNICAL_AUDIT.md H2: Silos::booted() currently blocks creating a
    // new SILO while the old one is still valid, forcing renewal to wait
    // until after expiry — which is what makes the C1 scenario the *only*
    // reachable state after any renewal today. Milestone M2.7 removes
    // that persistence-level guard so equipment can end up with two
    // overlapping valid SILO periods (early renewal). This proves the
    // domain layer already handles that shape correctly and isn't itself
    // a source of the H2 restriction: equipment remains eligible.
    public function test_h2_early_renewal_allowed(): void
    {
        $today = CarbonImmutable::parse('2026-06-01');
        $eligibility = new AssignmentEligibility;

        $stillValidOriginal = $this->validityExpiringOn($today->addDays(10)); // expiring soon, not yet expired
        $earlyRenewal = $this->validityExpiringOn($today->addYear()); // issued before the original expired

        $result = $eligibility->isEligible(
            operatorSioValidities: [$this->validityExpiringOn($today->addMonths(6))],
            equipmentSiloValidities: [$stillValidOriginal, $earlyRenewal],
            equipmentHasActiveAssignment: false,
            operatorHasActiveAssignmentElsewhere: false,
            today: $today,
        );

        $this->assertTrue($result);
    }
}
