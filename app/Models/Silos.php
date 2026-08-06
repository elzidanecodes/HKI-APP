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
    ];

    protected $casts = [
        'tanggal_terbit' => 'date',
        'tanggal_expired' => 'date',
    ];

    // Relasi ke alat berat
    public function alatBerat()
    {
        return $this->belongsTo(AlatBerats::class);
    }

    /**
     * Single source of truth for this SILO's validity — replaces the raw
     * date comparisons Milestone M2.5 retired. Implements
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

    // Still called by HseStats and ExpiredDocuments widgets — those
    // callers are outside this milestone's scope, so the scope itself
    // stays. A future milestone can migrate them to validity()/isExpired().
    public function scopeExpired(Builder $query): Builder
    {
        return $query->whereDate('tanggal_expired', '<', now());
    }
}
