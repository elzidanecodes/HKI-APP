<?php

namespace App\Filament\Resources\SiosResource\Pages;

use App\Filament\Resources\SiosResource;
use App\Models\Sios;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateSios extends CreateRecord
{
    protected static string $resource = SiosResource::class;

    protected function beforeCreate(): void
    {
        $operatorId = $this->data['operator_id'];

        $hasActiveSio = Sios::where('operator_id', $operatorId)
            ->currentlyValid()
            ->exists();

        if ($hasActiveSio) {
            Notification::make()
                ->title('SIO Masih Aktif')
                ->body('Operator ini masih memiliki SIO yang aktif dan belum expired.')
                ->danger()
                ->send();

            $this->halt();
        }
    }
}
