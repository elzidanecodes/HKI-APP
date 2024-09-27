<?php
// app/Exports/OperatorsExport.php
namespace App\Exports;

use App\Models\Operators;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class OperatorsExport implements FromCollection, WithHeadings, WithDrawings, ShouldAutoSize
{
    private $operators;
    public function __construct()
    {
        $this->operators = Operators::select('nama_operator','nomor_silo', 'nama_alat', 'merk_alat', 'tipe_alat','tahun_produksi', 'nomor_hp', 'foto_sio', 'foto_silo')->get();
        $this->operators = Operators::with(['media'])->get();
    }

    public function collection()
    {
        return $this->operators->map(function ($operator) {
            return [
                'nama_operator' => $operator->nama_operator,
                'nomor_silo' => $operator->nomor_silo,
                'nama_alat' => $operator->nama_alat,
                'merk_alat' => $operator->merk_alat,
                'tipe_alat' => $operator->tipe_alat,
                'tahun_produksi' => $operator->tahun_produksi,
                'nomor_hp' => $operator->nomor_hp,
                'foto_sio' => '', // Placeholder for image
                'foto_silo' => '', // Placeholder for image
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Nama Operator',
            'Nomor SILO',
            'Nama Alat',
            'Merk Alat',
            'Tipe Alat',
            'Tahun Produksi',
            'Nomor HP',
            'Foto SIO',
            'Foto SILO',
        ];
    }

    /**
     * Memasukkan gambar ke dalam Excel
     */
    public function drawings()
    {
        $drawings = [];
        foreach ($this->operators as $index => $operator) {
            if ($mediaSio = $operator->getFirstMedia('sio')) {
                $drawing = new Drawing();
                $drawing->setName('Foto SIO');
                $drawing->setDescription('Foto SIO');
                $drawing->setPath(public_path('storage/'. $mediaSio->id.'/' . $operator->getFirstMedia('sio')->file_name));
                $drawing->setHeight(80);
                $drawing->setCoordinates('H' . ($index + 2));
                $drawings[] = $drawing;
            }

            if ($mediaSilo = $operator->getFirstMedia('silo')) {
                $drawing = new Drawing();
                $drawing->setName('Foto Silo');
                $drawing->setDescription('Foto Silo');
                $drawing->setPath(public_path('storage/'. $mediaSilo->id.'/' . $operator->getFirstMedia('silo')->file_name));
                $drawing->setHeight(80);
                $drawing->setCoordinates('I' . ($index + 2));
                $drawings[] = $drawing;
            }
        }

        return $drawings;
    }


}