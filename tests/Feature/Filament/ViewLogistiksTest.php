<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\LogistiksResource\Pages\ViewLogistiks;
use App\Models\Logistiks;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Regression test for TECHNICAL_AUDIT.md H4 / IMPLEMENTATION_PLAN.md
 * Milestone M5.1: ViewLogistiks hardcoded
 * $resource = OperatorsResource::class, so "Lihat Data Logistik" resolved
 * and rendered the Operator schema instead of the Logistik record it was
 * meant to show. This fails if that class of bug recurs.
 */
class ViewLogistiksTest extends TestCase
{
    use RefreshDatabase;

    public function test_view_logistiks_renders_logistik_fields_not_operator_fields(): void
    {
        $this->actingAs(User::factory()->create(['job_title' => 'LOGISTIK']));

        $logistik = Logistiks::create([
            'nama_barang' => 'Semen Portland',
            'kategori_barang' => 'Material',
            'deskripsi_barang' => 'Semen Portland tipe 1',
            'jumlah_barang' => 100,
            'satuan' => 'zak',
            'lokasi' => 'Gudang A',
            'nama_vendor' => 'PT Vendor',
        ]);

        $component = Livewire::test(ViewLogistiks::class, ['record' => $logistik->getRouteKey()]);

        $component->assertSet('data.nama_barang', 'Semen Portland');
        $component->assertSet('data.kategori_barang', 'Material');
        $component->assertSet('data.nama_vendor', 'PT Vendor');

        $data = $component->get('data');
        $this->assertArrayNotHasKey('nama_operator', $data);
        $this->assertArrayNotHasKey('nomor_hp', $data);
    }
}
