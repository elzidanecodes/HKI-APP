<?php

namespace App\Filament\Resources\AlatBeratsResource\Pages;

use App\Filament\Concerns\HasStandardEditFormActions;
use App\Filament\Resources\AlatBeratsResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditAlatBerats extends EditRecord
{
    use HasStandardEditFormActions;

    protected static string $resource = AlatBeratsResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->authorize('delete', $this->getRecord()),
        ];
    }

     protected function getTitle(): string
    {
        return 'Ubah Data Alat Berat';
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->title('Data Alat Berat Diperbarui')
            ->body('Data alat berat telah berhasil diperbarui.')
            ->success();
    }
}
