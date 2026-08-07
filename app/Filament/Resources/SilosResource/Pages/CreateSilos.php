<?php

namespace App\Filament\Resources\SilosResource\Pages;

use App\Application\Compliance\SiloExpiryCalculator;
use App\Filament\Resources\SilosResource;
use Carbon\CarbonImmutable;
use Filament\Resources\Pages\CreateRecord;

class CreateSilos extends CreateRecord
{
    protected static string $resource = SilosResource::class;

    /**
     * Regression fix (found during Milestone M4.4's investigation):
     * Silos::booted() previously computed tanggal_expired from
     * tanggal_terbit on every save, including creation. Milestone M2.7
     * removed that hook and replaced it for the edit path only
     * (EditSilos::mutateFormDataBeforeSave()) — the create path was left
     * with no replacement, so creating a SILO through this page failed
     * with a NOT NULL constraint violation on tanggal_expired. This
     * restores the same computation EditSilos already performs, for
     * creation specifically.
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (! empty($data['tanggal_terbit'])) {
            $data['tanggal_expired'] = SiloExpiryCalculator::expiresAt(
                CarbonImmutable::parse($data['tanggal_terbit'])
            )->toDateString();
        }

        return $data;
    }
}
