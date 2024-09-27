<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class AlatBerats extends Model implements HasMedia
{
    use InteractsWithMedia;
    protected $primaryKey = 'id_alat';
    protected $fillable = [
        'nama_alat',
        'merk_alat',
        'tipe_alat',
        'tahun_produksi',
    ];
}
