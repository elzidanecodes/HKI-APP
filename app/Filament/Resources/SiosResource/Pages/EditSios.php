<?php

namespace App\Filament\Resources\SiosResource\Pages;

use App\Filament\Resources\SiosResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;
use Filament\Pages\Actions\ButtonAction;

class EditSios extends EditRecord
{
    protected static string $resource = SiosResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
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

                // DeleteAction::make()
                // ->label('Hapus')
                // ->requiresConfirmation()
                // ->redirect($this->getResource()::getUrl('index'))
                
        ];
    }
}
