<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Logistiks extends Model
{
    use HasFactory;

    protected $table = 'logistiks';
    protected $primaryKey = 'id_logistik';

    protected $fillable = [
        'nama_barang',
        'kategori_barang',
        'deskripsi_barang',
        'jumlah_barang',
        'satuan',
        'lokasi',
        'nama_vendor',
    ];
}