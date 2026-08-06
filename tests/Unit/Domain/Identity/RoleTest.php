<?php

namespace Tests\Unit\Domain\Identity;

use App\Domain\Identity\Enums\Role;
use PHPUnit\Framework\TestCase;

/**
 * Pure unit test — no database, no framework boot, matching
 * DocumentValidityTest.php's convention. IMPLEMENTATION_PLAN.md Milestone
 * M3.1 completion criteria: covers all three existing users.job_title
 * values, zero framework dependency.
 */
class RoleTest extends TestCase
{
    public function test_maps_each_existing_job_title_value_to_its_case(): void
    {
        $this->assertSame(Role::HSSE, Role::from('HSSE'));
        $this->assertSame(Role::LOGISTIK, Role::from('LOGISTIK'));
        $this->assertSame(Role::DOKON, Role::from('DOKON'));
    }

    public function test_has_exactly_the_three_existing_job_title_values(): void
    {
        $this->assertSame(
            ['HSSE', 'LOGISTIK', 'DOKON'],
            array_map(fn (Role $role) => $role->value, Role::cases()),
        );
    }

    public function test_rejects_a_job_title_value_that_does_not_exist(): void
    {
        $this->assertNull(Role::tryFrom('ADMIN'));
    }
}
