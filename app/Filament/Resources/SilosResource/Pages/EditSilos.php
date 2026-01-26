<?php

namespace App\Filament\Resources\SilosResource\Pages;

use App\Filament\Resources\SilosResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;
use Filament\Pages\Actions\ButtonAction;

class EditSilos extends EditRecord
{
    protected static string $resource = SilosResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getTitle(): string
    {
        return 'Ubah Data SILO';
    }


    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->title('Data SILO Diperbarui')
            ->body('Data SILO telah berhasil diperbarui.')
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
