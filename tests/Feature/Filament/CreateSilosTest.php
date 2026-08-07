<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\SilosResource\Pages\CreateSilos;
use App\Models\AlatBerats;
use App\Models\Silos;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Regression test (found during Milestone M4.4's investigation, fixed as
 * a standalone regression fix, not a new milestone).
 *
 * IMPLEMENTATION_PLAN.md Milestone M2.7 removed Silos::booted(), which
 * used to recompute tanggal_expired from tanggal_terbit on every save,
 * including creation. EditSilos::mutateFormDataBeforeSave() replaced
 * that for the edit path (see EditSilosTest.php); the create path had no
 * replacement, so creating a SILO through this page threw
 * "NOT NULL constraint failed: silos.tanggal_expired" and no record was
 * ever written — confirmed by driving this exact page before the fix.
 * This proves the create path now computes it correctly, matching
 * EditSilos' existing, unchanged behavior.
 */
class CreateSilosTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // HSSE has full CRUD on Silos (IMPLEMENTATION_PLAN.md Milestone
        // M3.3's approved authorization matrix).
        $this->actingAs(User::factory()->create(['job_title' => 'HSSE']));
    }

    public function test_creating_a_silo_computes_tanggal_expired_from_tanggal_terbit(): void
    {
        Storage::fake('local');

        $alatBerat = AlatBerats::factory()->create();
        $tanggalTerbit = now()->toDateString();

        Livewire::test(CreateSilos::class)
            ->fillForm([
                'alat_berat_id' => $alatBerat->id,
                'nomor_silo' => 'SILO-CREATE-0001',
                'tanggal_terbit' => $tanggalTerbit,
                'file_path' => UploadedFile::fake()->create('silo.pdf', 100, 'application/pdf'),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $silo = Silos::where('nomor_silo', 'SILO-CREATE-0001')->firstOrFail();

        $this->assertTrue($silo->tanggal_terbit->isSameDay(now()));
        $this->assertTrue($silo->tanggal_expired->isSameDay(now()->addYear()));
    }
}
