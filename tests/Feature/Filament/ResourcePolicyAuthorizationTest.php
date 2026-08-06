<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\AlatBeratsResource;
use App\Filament\Resources\LogistiksResource;
use App\Filament\Resources\OperatorsResource;
use App\Filament\Resources\SilosResource;
use App\Filament\Resources\SiosResource;
use App\Models\AlatBerats;
use App\Models\Logistiks;
use App\Models\Operators;
use App\Models\Silos;
use App\Models\Sios;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * IMPLEMENTATION_PLAN.md Milestone M3.3: "Filament Resources gain can()
 * checks purely for UI hint purposes (hide/show buttons)." Filament v2's
 * base Resource class already delegates canViewAny()/canCreate()/
 * canEdit()/canDelete() to the registered Policy automatically
 * (vendor/filament/filament/src/Resources/Resource.php:can()) — no
 * Resource file needed its own override once Policies (Milestone M3.2)
 * are registered and enforcing (this milestone). This test proves that
 * automatic delegation actually produces the approved matrix's result,
 * rather than adding redundant explicit overrides.
 */
class ResourcePolicyAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsRole(string $jobTitle): User
    {
        $user = User::factory()->create(['job_title' => $jobTitle]);
        $this->actingAs($user);

        return $user;
    }

    public function test_alat_berats_resource_reflects_the_approved_matrix(): void
    {
        $alatBerat = AlatBerats::factory()->create();

        $this->actingAsRole('HSSE');
        $this->assertTrue(AlatBeratsResource::canCreate());
        $this->assertTrue(AlatBeratsResource::canEdit($alatBerat));

        $this->actingAsRole('LOGISTIK');
        $this->assertTrue(AlatBeratsResource::canCreate());
        $this->assertTrue(AlatBeratsResource::canEdit($alatBerat));

        $this->actingAsRole('DOKON');
        $this->assertFalse(AlatBeratsResource::canCreate());
        $this->assertFalse(AlatBeratsResource::canEdit($alatBerat));
        $this->assertTrue(AlatBeratsResource::canViewAny());
    }

    public function test_operators_resource_reflects_the_approved_matrix(): void
    {
        $operator = Operators::factory()->create();

        $this->actingAsRole('HSSE');
        $this->assertTrue(OperatorsResource::canCreate());
        $this->assertTrue(OperatorsResource::canEdit($operator));

        $this->actingAsRole('LOGISTIK');
        $this->assertFalse(OperatorsResource::canCreate());
        $this->assertFalse(OperatorsResource::canEdit($operator));
        $this->assertFalse(OperatorsResource::canViewAny());

        $this->actingAsRole('DOKON');
        $this->assertFalse(OperatorsResource::canCreate());
        $this->assertTrue(OperatorsResource::canViewAny());
    }

    public function test_silos_resource_reflects_the_approved_matrix(): void
    {
        $silo = Silos::factory()->for(AlatBerats::factory(), 'alatBerat')->create();

        $this->actingAsRole('HSSE');
        $this->assertTrue(SilosResource::canCreate());

        $this->actingAsRole('LOGISTIK');
        $this->assertFalse(SilosResource::canCreate());
        $this->assertFalse(SilosResource::canViewAny());

        $this->actingAsRole('DOKON');
        $this->assertFalse(SilosResource::canCreate());
        $this->assertTrue(SilosResource::canViewAny());
        $this->assertFalse(SilosResource::canEdit($silo));
    }

    public function test_sios_resource_reflects_the_approved_matrix(): void
    {
        $sio = Sios::factory()->for(Operators::factory(), 'operator')->create();

        $this->actingAsRole('HSSE');
        $this->assertTrue(SiosResource::canCreate());

        $this->actingAsRole('LOGISTIK');
        $this->assertFalse(SiosResource::canCreate());
        $this->assertFalse(SiosResource::canViewAny());

        $this->actingAsRole('DOKON');
        $this->assertFalse(SiosResource::canCreate());
        $this->assertTrue(SiosResource::canViewAny());
        $this->assertFalse(SiosResource::canEdit($sio));
    }

    public function test_logistiks_resource_reflects_the_approved_matrix(): void
    {
        $logistik = Logistiks::create([
            'nama_barang' => 'Semen',
            'kategori_barang' => 'Material',
            'deskripsi_barang' => 'Semen Portland',
            'jumlah_barang' => 100,
            'satuan' => 'sak',
            'lokasi' => 'Gudang A',
            'nama_vendor' => 'PT Vendor',
        ]);

        $this->actingAsRole('LOGISTIK');
        $this->assertTrue(LogistiksResource::canCreate());
        $this->assertTrue(LogistiksResource::canEdit($logistik));

        $this->actingAsRole('HSSE');
        $this->assertFalse(LogistiksResource::canCreate());
        $this->assertTrue(LogistiksResource::canViewAny());

        $this->actingAsRole('DOKON');
        $this->assertFalse(LogistiksResource::canCreate());
        $this->assertTrue(LogistiksResource::canViewAny());
    }
}
