<?php

// app/Exports/OperatorsExport.php

namespace App\Exports;

use App\Models\Operators;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

/**
 * Regression fix (TECHNICAL_AUDIT.md H3, IMPLEMENTATION_PLAN.md
 * Milestone M5.1): this used to select columns that don't exist on
 * `operators` (`nomor_silo`, `nama_alat`, `merk_alat`, `tipe_alat`,
 * `tahun_produksi`, `foto_sio`, `foto_silo` all belong to AlatBerats/
 * Silos/Sios, not Operators) and eager-load a `media` relation that
 * doesn't exist (no model uses InteractsWithMedia) — the Export button
 * always failed. Trimmed to the columns operators actually has
 * (`nama_operator`, `nomor_hp`); the WithDrawings/getFirstMedia() photo
 * feature is removed rather than reworked, since there is no photo data
 * on this model to draw.
 */
class OperatorsExport implements FromCollection, ShouldAutoSize, WithHeadings
{
    private $operators;

    public function __construct()
    {
        $this->operators = Operators::all();
    }

    public function collection()
    {
        return $this->operators->map(function ($operator) {
            return [
                'nama_operator' => $operator->nama_operator,
                'nomor_hp' => $operator->nomor_hp,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Nama Operator',
            'Nomor HP',
        ];
    }
}
