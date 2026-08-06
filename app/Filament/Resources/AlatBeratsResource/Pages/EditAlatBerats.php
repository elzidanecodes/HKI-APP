<?php

namespace App\Filament\Resources\AlatBeratsResource\Pages;

use App\Filament\Resources\AlatBeratsResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;
use Filament\Pages\Actions\ButtonAction;

class EditAlatBerats extends EditRecord
{
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

    // Override tombol default di form action
    protected function getFormActions(): array
    {
        return [
            ButtonAction::make('submit')
                ->label('Perbarui Data')
                ->action('save') 
                ->color('primary')
                ->submit('update'),  

                ButtonAction::make('cancel')
                ->label('Batal')
                ->url($this->getResource()::getUrl('index'))
                ->color('secondary'),
        ];
    }
}
