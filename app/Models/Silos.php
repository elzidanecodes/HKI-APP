<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class Silos extends Model
{
    use HasFactory;

    protected $table = 'silos';

    protected $fillable = [
        'alat_berat_id',
        'nomor_silo',
        'tanggal_terbit',
        'tanggal_expired',
        'file_path',
        'is_active',
    ];

    protected $casts = [
        'tanggal_terbit' => 'date',
        'tanggal_expired' => 'date',
        'is_active' => 'boolean',
    ];

    protected static function booted()
    {
        static::saving(function ($silo) {

            // set expired otomatis
            if ($silo->tanggal_terbit) {
                $silo->tanggal_expired = Carbon::parse($silo->tanggal_terbit)
                    ->addYear()
                    ->startOfDay();
            }

            // validasi: hanya 1 SILO aktif per alat
            $hasActiveSilo = Silos::where('alat_berat_id', $silo->alat_berat_id)
                ->whereDate('tanggal_expired', '>=', now())
                ->when($silo->exists, fn ($q) => $q->where('id', '!=', $silo->id))
                ->exists();

            if ($hasActiveSilo) {
                throw ValidationException::withMessages([
                    'alat_berat_id' => 'Alat berat ini masih memiliki SILO yang aktif.',
                ]);
            }
        });
    }

    // Relasi ke alat berat
    public function alatBerat()
    {
        return $this->belongsTo(AlatBerats::class);
    }

    // Helper: cek expired
    public function isExpired(): bool
    {
        return $this->tanggal_expired->isPast();
    }

    // Helper: cek masih berlaku
    public function isValid(): bool
    {
        return ! $this->isExpired();
    }

}