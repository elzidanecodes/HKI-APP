<?php

namespace Tests\Feature;

use App\Models\AlatBerats;
use App\Models\Operators;
use App\Models\Silos;
use App\Models\Sios;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * IMPLEMENTATION_PLAN.md Milestone M3.4: document downloads now require
 * passing SioPolicy::view/SiloPolicy::view, not merely being
 * authenticated (Phase 0 / Milestone M0.3's interim protection). Per the
 * approved authorization matrix, LOGISTIK is authenticated but has no
 * access to SIO/SILO — this proves authentication alone is no longer
 * sufficient to reach these files, closing TECHNICAL_AUDIT.md C5.
 */
class DocumentAccessTest extends TestCase
{
    use RefreshDatabase;

    private function sioWithFile(): Sios
    {
        Storage::fake('local');
        Storage::disk('local')->put('sio/existing-file.pdf', 'fake-content');

        return Sios::factory()
            ->for(Operators::factory(), 'operator')
            ->create(['file_sio' => 'sio/existing-file.pdf']);
    }

    private function siloWithFile(): Silos
    {
        Storage::fake('local');
        Storage::disk('local')->put('silo/existing-file.pdf', 'fake-content');

        return Silos::factory()
            ->for(AlatBerats::factory(), 'alatBerat')
            ->create(['file_path' => 'silo/existing-file.pdf']);
    }

    public function test_unauthenticated_request_cannot_reach_a_sio_document(): void
    {
        $sio = $this->sioWithFile();

        $this->get(route('documents.sio.show', $sio))->assertRedirect(route('login'));
    }

    public function test_unauthenticated_request_cannot_reach_a_silo_document(): void
    {
        $silo = $this->siloWithFile();

        $this->get(route('documents.silo.show', $silo))->assertRedirect(route('login'));
    }

    public function test_hsse_can_view_sio_and_silo_documents(): void
    {
        $user = User::factory()->create(['job_title' => 'HSSE']);

        $this->actingAs($user)->get(route('documents.sio.show', $this->sioWithFile()))->assertOk();
        $this->actingAs($user)->get(route('documents.silo.show', $this->siloWithFile()))->assertOk();
    }

    public function test_dokumen_control_can_view_sio_and_silo_documents(): void
    {
        $user = User::factory()->create(['job_title' => 'DOKON']);

        $this->actingAs($user)->get(route('documents.sio.show', $this->sioWithFile()))->assertOk();
        $this->actingAs($user)->get(route('documents.silo.show', $this->siloWithFile()))->assertOk();
    }

    // The exact scenario TECHNICAL_AUDIT.md C3/C5 describe: an
    // authenticated LOGISTIK user with the file's URL. Authentication
    // used to be the only gate (Milestone M0.3); this proves it is no
    // longer sufficient.
    public function test_logistik_cannot_view_sio_or_silo_documents_despite_being_authenticated(): void
    {
        $user = User::factory()->create(['job_title' => 'LOGISTIK']);

        $this->actingAs($user)->get(route('documents.sio.show', $this->sioWithFile()))->assertForbidden();
        $this->actingAs($user)->get(route('documents.silo.show', $this->siloWithFile()))->assertForbidden();
    }
}
