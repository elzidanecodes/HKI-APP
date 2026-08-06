<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\LogistiksResource\Pages\ListLogistiks;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * IMPLEMENTATION_PLAN.md Milestone M3.5: LogistiksResource's
 * DeleteBulkAction now authorizes against LogistikPolicy::deleteAny(),
 * the same MANAGE_ROLES grant as delete() — LOGISTIK only. Unlike the
 * single-record DeleteAction (see DeleteActionAuthorizationTest), every
 * role can reach the List page (LogistikPolicy::VIEW_ROLES includes all
 * three), so the bulk action's own visibility is what's actually being
 * tested here.
 */
class DeleteBulkActionAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsRole(string $jobTitle): void
    {
        $this->actingAs(User::factory()->create(['job_title' => $jobTitle]));
    }

    public function test_logistik_sees_the_delete_bulk_action(): void
    {
        $this->actingAsRole('LOGISTIK');

        Livewire::test(ListLogistiks::class)
            ->assertTableBulkActionVisible('delete');
    }

    public function test_hsse_does_not_see_the_delete_bulk_action(): void
    {
        $this->actingAsRole('HSSE');

        Livewire::test(ListLogistiks::class)
            ->assertTableBulkActionHidden('delete');
    }

    public function test_dokumen_control_does_not_see_the_delete_bulk_action(): void
    {
        $this->actingAsRole('DOKON');

        Livewire::test(ListLogistiks::class)
            ->assertTableBulkActionHidden('delete');
    }
}
