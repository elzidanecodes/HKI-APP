<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

class AlatBerats extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode_alat',
        'nama_alat',
        'merk_alat',
        'tipe_alat',
        'tahun_produksi',
        'sta_lokasi',
    ];


    // Semua SILO alat berat
    public function silos(): HasMany
    {
        return $this->hasMany(Silos::class, 'alat_berat_id');
    }

    // Semua assignment operator
    public function assignments(): HasMany
    {
        return $this->hasMany(
            OperatorAlatAssignment::class,
            'alat_berat_id',
            'id'
        );
    }

    // Assignment aktif (jika ada)
    public function activeAssignment()
    {
        return $this->hasOne(
            OperatorAlatAssignment::class,
            'alat_berat_id',
            'id'
        )->where('is_active', true);
    }


    /**
     * Apakah alat berat memiliki SILO yang masih aktif
     */
    public function hasActiveSilo(): bool
    {
        return $this->silos()
            ->whereDate('tanggal_expired', '>=', now())
            ->exists();
    }

    /**
     * Ambil SILO aktif (null jika tidak ada)
     */
    public function activeSilo(): ?Silos
    {
        return $this->silos()
            ->whereDate('tanggal_expired', '>=', now())
            ->orderBy('tanggal_expired')
            ->first();
    }

    /**
     * Status SILO untuk UI
     */
    public function siloStatus(): string
    {
        return $this->hasActiveSilo() ? 'Aktif' : 'Expired';
    }

    /**
     * Sisa hari menuju expired (null jika tidak ada SILO aktif)
     */
    public function siloRemainingDays(): ?int
    {
        $silo = $this->activeSilo();

        if (! $silo) {
            return null;
        }

        return now()->diffInDays(
            Carbon::parse($silo->tanggal_expired),
            false
        );
    }

    public function latestSilo()
    {
        return $this->silos()
            ->orderByDesc('tanggal_expired')
            ->first();
    }


    /**
     * Apakah SILO akan expired dalam X hari
     */
    public function isSiloExpiringSoon(int $days = 30): bool
    {
        $remaining = $this->siloRemainingDays();

        return $remaining !== null && $remaining <= $days;
    }
}
