<?php

namespace App\Filament\Resources\SiosResource\Pages;

use App\Filament\Concerns\HasStandardEditFormActions;
use App\Filament\Resources\SiosResource;
use Filament\Notifications\Notification;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSios extends EditRecord
{
    use HasStandardEditFormActions;

    protected static string $resource = SiosResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->authorize('delete', $this->getRecord()),
        ];
    }

    protected function getTitle(): string
    {
        return 'Ubah Data SIO';
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->title('Data SIO Diperbarui')
            ->body('Data SIO telah berhasil diperbarui.')
            ->success();
    }
}
