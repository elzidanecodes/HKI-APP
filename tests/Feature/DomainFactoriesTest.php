<?php

namespace Tests\Feature;

use App\Models\AlatBerats;
use App\Models\OperatorAlatAssignment;
use App\Models\Operators;
use App\Models\Silos;
use App\Models\Sios;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DomainFactoriesTest extends TestCase
{
    use RefreshDatabase;

    public function test_alat_berats_factory_creates_a_valid_record(): void
    {
        $record = AlatBerats::factory()->create();

        $this->assertDatabaseHas('alat_berats', ['id' => $record->id]);
    }

    public function test_operators_factory_creates_a_valid_record(): void
    {
        $record = Operators::factory()->create();

        $this->assertDatabaseHas('operators', ['id' => $record->id]);
    }

    public function test_silos_factory_creates_a_valid_record(): void
    {
        $record = Silos::factory()->create();

        $this->assertDatabaseHas('silos', ['id' => $record->id]);
    }

    public function test_sios_factory_creates_a_valid_record(): void
    {
        $record = Sios::factory()->create();

        $this->assertDatabaseHas('sios', ['id' => $record->id]);
    }

    public function test_operator_alat_assignment_factory_creates_a_valid_record(): void
    {
        $record = OperatorAlatAssignment::factory()->create();

        $this->assertDatabaseHas('operator_alat_assignments', ['id' => $record->id]);
    }
}
