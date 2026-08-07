<?php

namespace App\Filament\Resources\LogistiksResource\Pages;

use App\Filament\Concerns\HasStandardEditFormActions;
use App\Filament\Resources\LogistiksResource;
use Filament\Notifications\Notification;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLogistiks extends EditRecord
{
    use HasStandardEditFormActions;

    protected static string $resource = LogistiksResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->authorize('delete', $this->getRecord()),
        ];
    }

    protected function getTitle(): string
    {
        return 'Ubah Data Logistik';
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->title('Data Logistik Diperbarui')
            ->body('Data logistik telah berhasil diperbarui.')
            ->success();
    }
}
