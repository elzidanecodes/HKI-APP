<?php

namespace Tests\Feature\Application\Compliance;

use App\Application\Compliance\IssueDocument;
use App\Models\AlatBerats;
use App\Models\Operators;
use App\Models\Silos;
use App\Models\Sios;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IssueDocumentTest extends TestCase
{
    use RefreshDatabase;

    public function test_issues_a_new_sio(): void
    {
        $operator = Operators::factory()->create();

        $sio = new Sios([
            'operator_id' => $operator->id,
            'nomor_sio' => 'SIO-0001',
            'tanggal_expired' => now()->addYear()->toDateString(),
        ]);

        $issued = (new IssueDocument)->handle($sio);

        $this->assertTrue($issued->exists);
        $this->assertDatabaseHas('sios', [
            'operator_id' => $operator->id,
            'nomor_sio' => 'SIO-0001',
        ]);
    }

    public function test_issues_a_new_silo(): void
    {
        $alatBerat = AlatBerats::factory()->create();

        $silo = new Silos([
            'alat_berat_id' => $alatBerat->id,
            'nomor_silo' => 'SILO-0001',
            'tanggal_terbit' => now()->toDateString(),
        ]);

        $issued = (new IssueDocument)->handle($silo);

        $this->assertTrue($issued->exists);
        $this->assertDatabaseHas('silos', [
            'alat_berat_id' => $alatBerat->id,
            'nomor_silo' => 'SILO-0001',
        ]);
    }
}
