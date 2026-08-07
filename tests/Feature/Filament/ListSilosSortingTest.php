<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\SilosResource\Pages\ListSilos;
use App\Models\AlatBerats;
use App\Models\Silos;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Regression test for TECHNICAL_AUDIT.md M1 / IMPLEMENTATION_PLAN.md
 * Milestone M5.1: SilosResource::getTableQuery() never executed —
 * Filament v2 Resources are static, so the intended sort by
 * tanggal_expired silently never applied. It now lives on ListSilos,
 * the page that can actually execute it.
 */
class ListSilosSortingTest extends TestCase
{
    use RefreshDatabase;

    public function test_list_silos_is_sorted_by_tanggal_expired_descending(): void
    {
        $this->actingAs(User::factory()->create(['job_title' => 'HSSE']));

        $alatBerat = AlatBerats::factory()->create();

        $soonest = Silos::factory()->for($alatBerat, 'alatBerat')->create([
            'tanggal_terbit' => now()->subMonths(11)->toDateString(),
        ]);
        $latest = Silos::factory()->for($alatBerat, 'alatBerat')->create([
            'tanggal_terbit' => now()->toDateString(),
        ]);
        $middle = Silos::factory()->for($alatBerat, 'alatBerat')->create([
            'tanggal_terbit' => now()->subMonths(6)->toDateString(),
        ]);

        Livewire::test(ListSilos::class)
            ->assertSeeInOrder([
                $latest->nomor_silo,
                $middle->nomor_silo,
                $soonest->nomor_silo,
            ]);
    }
}
