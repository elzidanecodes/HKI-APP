<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Http\Request;

class Operators extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;
    protected $primaryKey = 'id_operator';
    protected $fillable = [
        'nama_operator',
        'nomor_silo',
        'nama_alat',
        'merk_alat',
        'tipe_alat',
        'tahun_produksi',
        'foto_sio',
        'foto_silo',
        'nomor_hp',
    ];

    public function alatBerats()
    {
        return $this->belongsTo(AlatBerats::class, 'nomor_silo', 'nomor_silo');
    }

    protected static function booted()
{
    static::creating(function ($operator) {
        AlatBerats::updateOrCreate(
            [
                'nomor_silo' => $operator->nomor_silo,
            ],
            [
                'nama_alat' => $operator->nama_alat,
                'merk_alat' => $operator->merk_alat,
                'tipe_alat' => $operator->tipe_alat,
                'tahun_produksi' => $operator->tahun_produksi,
            ]
        );
    });
}

public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('sio') // Media collection untuk foto_sio
            ->singleFile();

        $this
            ->addMediaCollection('silo') // Media collection untuk foto_silo
            ->singleFile();
    }


}
