<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class AlatBerats extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;
    protected $primaryKey = 'id_alat';
    protected $fillable = [
        'nomor_silo',
        'nama_alat',
        'merk_alat',
        'tipe_alat',
        'tahun_produksi',
    ];
    public function operators()
    {
        return $this->hasMany(Operators::class, 'nomor_silo', 'nomor_silo');
    }
}
