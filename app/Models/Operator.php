<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Operator extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_operator',
        'jenis_alat',
        'merk_alat',
        'tipe_alat',
        'tahun_produksi',
        'foto_sio',
        'foto_silo',
        'nomer_hp',
    ];
}
