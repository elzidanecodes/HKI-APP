<?php

namespace Tests\Feature;

use App\Models\AlatBerats;
use App\Models\OperatorAlatAssignment;
use App\Models\Operators;
use App\Models\Silos;
use App\Models\Sios;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * IMPLEMENTATION_PLAN.md Milestone M3.5: stops permanent, silent
 * destruction of audit-relevant data.
 *
 * TECHNICAL_AUDIT.md C4: operator_id's cascadeOnDelete() destroyed
 * assignment history when an Operator was deleted, asymmetric with
 * alat_berat_id's restrictOnDelete() — now symmetric.
 *
 * TECHNICAL_AUDIT.md H7: hard DeleteAction with zero SoftDeletes meant
 * legal documents and their subjects were permanently, silently
 * destroyed. Operators, AlatBerats, Silos, and Sios are now
 * soft-deletable — deletion is recoverable, not destructive.
 */
class RetentionPolicyTest extends TestCase
{
    use RefreshDatabase;

    // Operators is now SoftDeletes, so delete() alone never reaches the
    // database's FK constraint — it's an UPDATE, not a DELETE. The FK
    // restrict is the last line of defense against forceDelete()
    // specifically: the one remaining path that would actually destroy
    // assignment history.
    public function test_operator_with_assignment_history_cannot_be_force_deleted(): void
    {
        $operator = Operators::factory()->create();
        OperatorAlatAssignment::factory()->for($operator, 'operator')->create();

        $this->expectException(QueryException::class);

        $operator->forceDelete();
    }

    // Symmetric with the operator test above — alat_berat_id was already
    // restrictOnDelete() before this milestone; this proves it still is.
    public function test_alat_berat_with_assignment_history_cannot_be_force_deleted(): void
    {
        $alatBerat = AlatBerats::factory()->create();
        OperatorAlatAssignment::factory()->for($alatBerat, 'alatBerat')->create();

        $this->expectException(QueryException::class);

        $alatBerat->forceDelete();
    }

    public function test_deleting_an_operator_soft_deletes_it(): void
    {
        $operator = Operators::factory()->create();

        $operator->delete();

        $this->assertSoftDeleted('operators', ['id' => $operator->id]);
    }

    public function test_deleting_an_alat_berat_soft_deletes_it(): void
    {
        $alatBerat = AlatBerats::factory()->create();

        $alatBerat->delete();

        $this->assertSoftDeleted('alat_berats', ['id' => $alatBerat->id]);
    }

    public function test_deleting_a_silo_soft_deletes_it(): void
    {
        $silo = Silos::factory()->for(AlatBerats::factory(), 'alatBerat')->create();

        $silo->delete();

        $this->assertSoftDeleted('silos', ['id' => $silo->id]);
    }

    public function test_deleting_a_sio_soft_deletes_it(): void
    {
        $sio = Sios::factory()->for(Operators::factory(), 'operator')->create();

        $sio->delete();

        $this->assertSoftDeleted('sios', ['id' => $sio->id]);
    }
}
