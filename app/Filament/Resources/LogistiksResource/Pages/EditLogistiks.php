<?php

namespace App\Filament\Resources\LogistiksResource\Pages;

use App\Filament\Resources\LogistiksResource;
use Filament\Notifications\Notification;
use Filament\Pages\Actions;
use Filament\Pages\Actions\ButtonAction;
use Filament\Resources\Pages\EditRecord;

class EditLogistiks extends EditRecord
{
    protected static string $resource = LogistiksResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
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

    // Override tombol default di form action
    protected function getFormActions(): array
    {
        return [
            ButtonAction::make('submit')
                ->label('Perbarui Data') // Ubah label tombol Save
                ->action('save') // Tindakan save tetap dijalankan
                ->color('primary') // Warna tombol tetap sama
                ->submit('update'),  // Action tetap dikaitkan dengan update

                ButtonAction::make('cancel')
                ->label('Batal') // Label untuk tombol Cancel
                ->url($this->getResource()::getUrl('index')) // Arahkan ke halaman index
                ->color('secondary'), // Warna tombol, bisa disesuaikan
                
        ];
    }

}
