<?php

namespace App\Models;

use App\Domain\Compliance\ValueObjects\DocumentValidity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
     * Apakah alat berat memiliki SILO yang masih aktif.
     *
     * Delegates to Silos::validity() (App\Domain\Compliance\ValueObjects\
     * DocumentValidity) instead of comparing dates directly — this
     * milestone (M2.5) retires the raw whereDate() comparison this method
     * used to run.
     */
    public function hasActiveSilo(): bool
    {
        return DocumentValidity::anyValid(
            $this->silos()->get()->map(fn (Silos $silo) => $silo->validity())->all(),
            now(),
        );
    }

    /**
     * Ambil SILO aktif (null jika tidak ada). Sama seperti sebelumnya:
     * jika ada lebih dari satu SILO valid (perpanjangan dini, H2), yang
     * paling dekat kedaluwarsa yang dipilih.
     */
    public function activeSilo(): ?Silos
    {
        return $this->silos()
            ->get()
            ->filter(fn (Silos $silo) => $silo->validity()->isValidOn(now()))
            ->sortBy('tanggal_expired')
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

        return $silo->validity()->remainingDaysOn(now());
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
