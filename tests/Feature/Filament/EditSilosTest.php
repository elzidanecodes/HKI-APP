<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\SilosResource\Pages\EditSilos;
use App\Models\AlatBerats;
use App\Models\Silos;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * IMPLEMENTATION_PLAN.md Milestone M2.7 removed Silos::booted(), which
 * used to recompute tanggal_expired from tanggal_terbit on every save,
 * including edits (TECHNICAL_AUDIT.md M6). EditSilos::
 * mutateFormDataBeforeSave() replaces that for the edit path specifically
 * — this proves editing tanggal_terbit still keeps tanggal_expired
 * consistent, per Execution Rule #5 (preserve behavior).
 */
class EditSilosTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // HSSE has full CRUD on Silos (IMPLEMENTATION_PLAN.md Milestone
        // M3.3's approved authorization matrix) — a random job_title
        // would make this test flaky now that Policies are enforced, not
        // just logged.
        $this->actingAs(User::factory()->create(['job_title' => 'HSSE']));
    }

    public function test_editing_tanggal_terbit_recomputes_tanggal_expired(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('silo/existing-file.pdf', 'fake-content');

        $alatBerat = AlatBerats::factory()->create();
        $silo = Silos::factory()->for($alatBerat, 'alatBerat')->create([
            'tanggal_terbit' => now()->subMonths(3)->toDateString(),
            'file_path' => 'silo/existing-file.pdf',
        ]);

        $newTanggalTerbit = now()->subMonths(2)->toDateString();

        Livewire::test(EditSilos::class, ['record' => $silo->getRouteKey()])
            ->set('data.tanggal_terbit', $newTanggalTerbit)
            ->call('save')
            ->assertHasNoFormErrors();

        $silo->refresh();

        $this->assertTrue($silo->tanggal_terbit->isSameDay(now()->subMonths(2)));
        $this->assertTrue($silo->tanggal_expired->isSameDay(now()->subMonths(2)->addYear()));
    }
}
