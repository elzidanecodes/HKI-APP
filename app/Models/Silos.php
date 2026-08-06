<?php

namespace App\Models;

use App\Domain\Compliance\Contracts\LegalDocument;
use App\Domain\Compliance\Enums\DocumentStatus;
use App\Domain\Compliance\ValueObjects\DocumentValidity;
use App\Domain\Compliance\ValueObjects\ValidityPeriod;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class Silos extends Model implements LegalDocument
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

    /**
     * Single source of truth for this SILO's validity — replaces the raw
     * date comparisons this milestone (M2.5) retires. Implements
     * App\Domain\Compliance\Contracts\LegalDocument (scaffolded in M2.2).
     */
    public function validity(): DocumentValidity
    {
        return DocumentValidity::forPeriod(
            ValidityPeriod::fromDates(
                CarbonImmutable::parse($this->tanggal_terbit),
                CarbonImmutable::parse($this->tanggal_expired),
            ),
            config('hse.expiring_soon_threshold_days'),
        );
    }

    public function statusLabel(): string
    {
        return match ($this->validity()->status(CarbonImmutable::now())) {
            DocumentStatus::Expired => 'Expired',
            DocumentStatus::ExpiringSoon => 'Akan Expired',
            DocumentStatus::Active => 'Aktif',
        };
    }

    // Helper: cek expired
    public function isExpired(): bool
    {
        return ! $this->validity()->isValidOn(CarbonImmutable::now());
    }

    // Helper: cek masih berlaku
    public function isValid(): bool
    {
        return ! $this->isExpired();
    }

    public function scopeCurrentlyValid(Builder $query): Builder
    {
        return $query->whereDate('tanggal_expired', '>=', now());
    }

    public function scopeExpired(Builder $query): Builder
    {
        return $query->whereDate('tanggal_expired', '<', now());
    }
}
