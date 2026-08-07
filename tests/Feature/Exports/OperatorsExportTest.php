<?php

namespace Tests\Feature\Exports;

use App\Exports\OperatorsExport;
use App\Models\Operators;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

/**
 * Regression test for TECHNICAL_AUDIT.md H3 / IMPLEMENTATION_PLAN.md
 * Milestone M5.1: OperatorsExport selected columns that don't exist on
 * `operators` and called getFirstMedia() with no InteractsWithMedia
 * trait present — the Export button always failed. This fails if that
 * class of bug recurs.
 */
class OperatorsExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_export_produces_a_file_without_error(): void
    {
        Operators::factory()->count(3)->create();

        Excel::fake();

        Excel::download(new OperatorsExport, 'operators.xlsx');

        Excel::assertDownloaded('operators.xlsx', function (OperatorsExport $export) {
            $rows = $export->collection();

            return $rows->count() === 3
                && $export->headings() === ['Nama Operator', 'Nomor HP'];
        });
    }

    public function test_collection_matches_current_operators_schema(): void
    {
        $operator = Operators::factory()->create([
            'nama_operator' => 'Budi Santoso',
            'nomor_hp' => '081234567890',
        ]);

        $rows = (new OperatorsExport)->collection();

        $this->assertSame([
            'nama_operator' => 'Budi Santoso',
            'nomor_hp' => '081234567890',
        ], $rows->first());
    }
}
