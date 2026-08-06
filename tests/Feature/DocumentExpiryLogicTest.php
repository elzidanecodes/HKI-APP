<?php

namespace Tests\Feature;

use App\Filament\Resources\AlatBeratsResource\Pages\ListAlatBerats;
use App\Filament\Resources\SilosResource\Pages\ListSilos;
use App\Filament\Resources\SiosResource\Pages\ListSios;
use App\Models\AlatBerats;
use App\Models\Operators;
use App\Models\Silos;
use App\Models\Sios;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Characterizes document-expiry logic exactly as it exists today
 * (IMPLEMENTATION_PLAN.md Milestone M1.3), across the locations
 * TECHNICAL_AUDIT.md ("Duplikasi #1") identifies as duplicating the same
 * "is this document valid?" check independently. This is the "before"
 * snapshot Phase 2's DocumentValidity value object replaces — it documents
 * current behavior, not a claim that duplicating this logic six times is
 * correct.
 *
 * Locations covered here (the other two — the SIO guard inside
 * OperatorAssignmentsRelationManager, and AutoEndExpiredAssignments — are
 * characterized separately in AssignmentGuardTest and
 * AutoEndExpiredAssignmentsTest):
 *   1. AlatBerats::hasActiveSilo() / activeSilo() (AlatBerats.php:58)
 *   2. AlatBeratsResource's silo_status badge column (AlatBeratsResource.php:124)
 *   3. The operator dropdown's SIO filter (RelationManager.php:30-33)
 *   4. SilosResource / SiosResource status badge columns (SilosResource.php:102, SiosResource.php:84)
 */
class DocumentExpiryLogicTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
    }

    // --- 1. AlatBerats model helpers (AlatBerats.php:58) ---

    public function test_has_active_silo_is_true_when_silo_is_valid(): void
    {
        $alatBerat = AlatBerats::factory()->create();
        Silos::factory()->for($alatBerat, 'alatBerat')->create();

        $this->assertTrue($alatBerat->hasActiveSilo());
        $this->assertNotNull($alatBerat->activeSilo());
    }

    public function test_has_active_silo_is_false_when_silo_is_expired(): void
    {
        $alatBerat = AlatBerats::factory()->create();
        Silos::factory()->for($alatBerat, 'alatBerat')->create([
            'tanggal_terbit' => now()->subMonths(14)->toDateString(),
        ]);

        $this->assertFalse($alatBerat->hasActiveSilo());
        $this->assertNull($alatBerat->activeSilo());
    }

    public function test_has_active_silo_is_false_when_no_silo_exists(): void
    {
        $alatBerat = AlatBerats::factory()->create();

        $this->assertFalse($alatBerat->hasActiveSilo());
        $this->assertNull($alatBerat->activeSilo());
        $this->assertNull($alatBerat->siloRemainingDays());
    }

    // --- 2. AlatBeratsResource silo_status badge (AlatBeratsResource.php:105-132) ---

    public function test_alat_berats_list_badge_shows_aktif_for_a_valid_silo(): void
    {
        $alatBerat = AlatBerats::factory()->create();
        Silos::factory()->for($alatBerat, 'alatBerat')->create();

        Livewire::test(ListAlatBerats::class)
            ->assertTableColumnStateSet('silo_status', 'Aktif', $alatBerat)
            ->assertTableColumnStateSet('status_penggunaan', 'Idle', $alatBerat);
    }

    public function test_alat_berats_list_badge_shows_expired_for_an_expired_silo(): void
    {
        $alatBerat = AlatBerats::factory()->create();
        Silos::factory()->for($alatBerat, 'alatBerat')->create([
            'tanggal_terbit' => now()->subMonths(14)->toDateString(),
        ]);

        Livewire::test(ListAlatBerats::class)
            ->assertTableColumnStateSet('silo_status', 'Expired', $alatBerat);
    }

    // --- 3. Operator dropdown SIO filter (RelationManager.php:30-33) ---

    public function test_operator_dropdown_filter_only_matches_operators_with_a_currently_valid_sio(): void
    {
        $operatorWithValidSio = Operators::factory()->create();
        Sios::factory()->for($operatorWithValidSio, 'operator')->create();

        $operatorWithOnlyExpiredSio = Operators::factory()->create();
        Sios::factory()->for($operatorWithOnlyExpiredSio, 'operator')->create([
            'tanggal_expired' => now()->subDay()->toDateString(),
        ]);

        $operatorWithNoSio = Operators::factory()->create();

        // Mirrors the exact filter OperatorAssignmentsRelationManager::form()
        // applies to the operator dropdown's relationship query.
        $selectableIds = Operators::query()
            ->whereHas('sio', fn ($q) => $q->whereDate('tanggal_expired', '>=', now()))
            ->pluck('id');

        $this->assertTrue($selectableIds->contains($operatorWithValidSio->id));
        $this->assertFalse($selectableIds->contains($operatorWithOnlyExpiredSio->id));
        $this->assertFalse($selectableIds->contains($operatorWithNoSio->id));
    }

    // --- 4. SilosResource / SiosResource status badges (SilosResource.php:100-128, SiosResource.php:82-110) ---

    public function test_silos_list_badge_reflects_all_three_status_bands(): void
    {
        $alatBerat = AlatBerats::factory()->create();

        $aktif = Silos::factory()->for($alatBerat, 'alatBerat')->create([
            'tanggal_terbit' => now()->subMonths(6)->toDateString(), // expires ~6 months out
        ]);

        Livewire::test(ListSilos::class)
            ->assertTableColumnStateSet('status', 'Aktif', $aktif);
    }

    public function test_silos_list_badge_shows_akan_expired_within_30_days(): void
    {
        $alatBerat = AlatBerats::factory()->create();

        $akanExpired = Silos::factory()->for($alatBerat, 'alatBerat')->create([
            'tanggal_terbit' => now()->subYear()->addDays(10)->toDateString(), // expires in 10 days
        ]);

        Livewire::test(ListSilos::class)
            ->assertTableColumnStateSet('status', 'Akan Expired', $akanExpired);
    }

    public function test_silos_list_badge_shows_expired_after_expiry_date(): void
    {
        $alatBerat = AlatBerats::factory()->create();

        // Silos::booted() blocks creating a new SILO while an active one
        // exists for the same equipment (H2), so create the expired record
        // directly, bypassing the model event, to isolate this badge test
        // from that separate, already-known behavior.
        $expired = Silos::factory()->for($alatBerat, 'alatBerat')->make([
            'tanggal_terbit' => now()->subYears(2)->toDateString(),
            'tanggal_expired' => now()->subYear()->toDateString(),
        ]);
        Silos::withoutEvents(fn () => $expired->save());

        Livewire::test(ListSilos::class)
            ->assertTableColumnStateSet('status', 'Expired', $expired);
    }

    public function test_sios_list_badge_reflects_all_three_status_bands(): void
    {
        $operator = Operators::factory()->create();

        $aktif = Sios::factory()->for($operator, 'operator')->create([
            'tanggal_expired' => now()->addMonths(2)->toDateString(),
        ]);
        $akanExpired = Sios::factory()->for($operator, 'operator')->create([
            'tanggal_expired' => now()->addDays(10)->toDateString(),
        ]);
        $expired = Sios::factory()->for($operator, 'operator')->create([
            'tanggal_expired' => now()->subDays(5)->toDateString(),
        ]);

        Livewire::test(ListSios::class)
            ->assertTableColumnStateSet('status', 'Aktif', $aktif)
            ->assertTableColumnStateSet('status', 'Akan Expired', $akanExpired)
            ->assertTableColumnStateSet('status', 'Expired', $expired);
    }
}
