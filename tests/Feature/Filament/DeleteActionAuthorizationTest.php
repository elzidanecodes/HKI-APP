<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\AlatBeratsResource\Pages\EditAlatBerats;
use App\Filament\Resources\LogistiksResource\Pages\EditLogistiks;
use App\Filament\Resources\OperatorsResource\Pages\EditOperators;
use App\Filament\Resources\SilosResource\Pages\EditSilos;
use App\Filament\Resources\SiosResource\Pages\EditSios;
use App\Models\AlatBerats;
use App\Models\Logistiks;
use App\Models\Operators;
use App\Models\Silos;
use App\Models\Sios;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * IMPLEMENTATION_PLAN.md Milestone M3.5's own testing requirement: "test
 * proving DeleteAction is hidden/blocked for unauthorized roles."
 *
 * None of these 5 Resources has a dedicated read-only "view" page
 * (Logistik is the only one that does, App\Filament\Resources\
 * LogistiksResource\Pages\ViewLogistiks — irrelevant here since it has
 * no DeleteAction) — a role without update access cannot reach the Edit
 * page at all: EditRecord::mount()'s own authorizeAccess() already
 * returns 403 before the component renders (Resource::canEdit()
 * auto-delegates to the registered Policy, confirmed in Milestone M3.3).
 * That is a strictly stronger block than "the delete button is hidden,"
 * so this asserts the actual 403 for every role/entity pair the approved
 * matrix denies update access to, and asserts the delete button is
 * visible for every pair it grants update access to.
 */
class DeleteActionAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsRole(string $jobTitle): void
    {
        $this->actingAs(User::factory()->create(['job_title' => $jobTitle]));
    }

    private function logistikRecord(): Logistiks
    {
        return Logistiks::create([
            'nama_barang' => 'Semen',
            'kategori_barang' => 'Material',
            'deskripsi_barang' => 'Semen Portland',
            'jumlah_barang' => 100,
            'satuan' => 'sak',
            'lokasi' => 'Gudang A',
            'nama_vendor' => 'PT Vendor',
        ]);
    }

    // --- Alat Berat: HSSE and LOGISTIK manage, DOKON does not ----------

    public function test_hsse_sees_alat_berat_delete_action(): void
    {
        $this->actingAsRole('HSSE');
        $alatBerat = AlatBerats::factory()->create();

        Livewire::test(EditAlatBerats::class, ['record' => $alatBerat->getRouteKey()])
            ->assertPageActionVisible('delete');
    }

    public function test_logistik_sees_alat_berat_delete_action(): void
    {
        $this->actingAsRole('LOGISTIK');
        $alatBerat = AlatBerats::factory()->create();

        Livewire::test(EditAlatBerats::class, ['record' => $alatBerat->getRouteKey()])
            ->assertPageActionVisible('delete');
    }

    public function test_dokumen_control_cannot_reach_alat_berat_edit_page_at_all(): void
    {
        $this->actingAsRole('DOKON');
        $alatBerat = AlatBerats::factory()->create();

        $this->get(route('filament.resources.alat-berats.edit', $alatBerat))
            ->assertForbidden();
    }

    // --- Operator: only HSSE manages -----------------------------------

    public function test_hsse_sees_operator_delete_action(): void
    {
        $this->actingAsRole('HSSE');
        $operator = Operators::factory()->create();

        Livewire::test(EditOperators::class, ['record' => $operator->getRouteKey()])
            ->assertPageActionVisible('delete');
    }

    public function test_logistik_cannot_reach_operator_edit_page_at_all(): void
    {
        $this->actingAsRole('LOGISTIK');
        $operator = Operators::factory()->create();

        $this->get(route('filament.resources.operators.edit', $operator))
            ->assertForbidden();
    }

    public function test_dokumen_control_cannot_reach_operator_edit_page_at_all(): void
    {
        $this->actingAsRole('DOKON');
        $operator = Operators::factory()->create();

        $this->get(route('filament.resources.operators.edit', $operator))
            ->assertForbidden();
    }

    // --- Silo: only HSSE manages ----------------------------------------

    public function test_hsse_sees_silo_delete_action(): void
    {
        $this->actingAsRole('HSSE');
        $silo = Silos::factory()->for(AlatBerats::factory(), 'alatBerat')->create();

        Livewire::test(EditSilos::class, ['record' => $silo->getRouteKey()])
            ->assertPageActionVisible('delete');
    }

    public function test_logistik_cannot_reach_silo_edit_page_at_all(): void
    {
        $this->actingAsRole('LOGISTIK');
        $silo = Silos::factory()->for(AlatBerats::factory(), 'alatBerat')->create();

        $this->get(route('filament.resources.silos.edit', $silo))
            ->assertForbidden();
    }

    public function test_dokumen_control_cannot_reach_silo_edit_page_at_all(): void
    {
        $this->actingAsRole('DOKON');
        $silo = Silos::factory()->for(AlatBerats::factory(), 'alatBerat')->create();

        $this->get(route('filament.resources.silos.edit', $silo))
            ->assertForbidden();
    }

    // --- Sio: only HSSE manages ------------------------------------------

    public function test_hsse_sees_sio_delete_action(): void
    {
        $this->actingAsRole('HSSE');
        $sio = Sios::factory()->for(Operators::factory(), 'operator')->create();

        Livewire::test(EditSios::class, ['record' => $sio->getRouteKey()])
            ->assertPageActionVisible('delete');
    }

    public function test_logistik_cannot_reach_sio_edit_page_at_all(): void
    {
        $this->actingAsRole('LOGISTIK');
        $sio = Sios::factory()->for(Operators::factory(), 'operator')->create();

        $this->get(route('filament.resources.sios.edit', $sio))
            ->assertForbidden();
    }

    public function test_dokumen_control_cannot_reach_sio_edit_page_at_all(): void
    {
        $this->actingAsRole('DOKON');
        $sio = Sios::factory()->for(Operators::factory(), 'operator')->create();

        $this->get(route('filament.resources.sios.edit', $sio))
            ->assertForbidden();
    }

    // --- Logistik: only LOGISTIK manages ---------------------------------

    public function test_logistik_sees_logistik_delete_action(): void
    {
        $this->actingAsRole('LOGISTIK');
        $logistik = $this->logistikRecord();

        Livewire::test(EditLogistiks::class, ['record' => $logistik->getRouteKey()])
            ->assertPageActionVisible('delete');
    }

    public function test_hsse_cannot_reach_logistik_edit_page_at_all(): void
    {
        $this->actingAsRole('HSSE');
        $logistik = $this->logistikRecord();

        $this->get(route('filament.resources.logistiks.edit', $logistik))
            ->assertForbidden();
    }

    public function test_dokumen_control_cannot_reach_logistik_edit_page_at_all(): void
    {
        $this->actingAsRole('DOKON');
        $logistik = $this->logistikRecord();

        $this->get(route('filament.resources.logistiks.edit', $logistik))
            ->assertForbidden();
    }
}
