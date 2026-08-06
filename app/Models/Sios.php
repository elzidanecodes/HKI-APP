<?php

namespace App\Models;

use App\Domain\Compliance\Contracts\LegalDocument;
use App\Domain\Compliance\Enums\DocumentStatus;
use App\Domain\Compliance\ValueObjects\DocumentValidity;
use App\Domain\Compliance\ValueObjects\ValidityPeriod;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sios extends Model implements LegalDocument
{
    use HasFactory;

    protected $table = 'sios';

    protected $fillable = [
        'operator_id',
        'nomor_sio',
        'tanggal_terbit',
        'tanggal_expired',
        'file_sio',
    ];

    protected $casts = [
        'tanggal_terbit' => 'date',
        'tanggal_expired' => 'date',
    ];

    // Relasi ke operator
    public function operator()
    {
        return $this->belongsTo(Operators::class);
    }

    /**
     * Single source of truth for this SIO's validity — replaces the raw
     * date comparisons this milestone (M2.5) retires. Implements
     * App\Domain\Compliance\Contracts\LegalDocument (scaffolded in M2.2).
     *
     * sios has no tanggal_terbit column (TECHNICAL_AUDIT.md M9) —
     * created_at stands in as the period start, the same convention
     * App\Application\Compliance\DocumentValidityMapper uses (M2.4). It's
     * inert data ValidityPeriod never uses to gate validity.
     */
    public function validity(): DocumentValidity
    {
        return DocumentValidity::forPeriod(
            ValidityPeriod::fromDates(
                CarbonImmutable::instance($this->created_at),
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
