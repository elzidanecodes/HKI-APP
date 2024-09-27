<?php

namespace App\Filament\Resources\LogistiksResource\Pages;

use App\Filament\Resources\LogistiksResource;
use Filament\Notifications\Notification;
use Filament\Pages\Actions;
use Filament\Pages\Actions\ButtonAction;
use Filament\Resources\Pages\CreateRecord;

class CreateLogistiks extends CreateRecord
{
    protected static string $resource = LogistiksResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getTitle(): string
    {
        return 'Tambah Data Logistik';
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->title('Data Logistik Ditambahkan')
            ->body('Data logistik telah berhasil ditambahkan.')
            ->success();
    }

    // Mengubah label tombol Create
    protected function getFormActions(): array
    {
        return [
            ButtonAction::make('submit')
                ->label('Simpan Data Baru') // Ubah label tombol Create
                ->color('primary') // Warna tombol tetap sama
                ->submit('store'),  // Action tetap dikaitkan dengan penyimpanan

            ButtonAction::make('cancel')
                ->label('Batal') // Label untuk tombol Cancel
                ->url($this->getResource()::getUrl('index')) // Arahkan ke halaman index
                ->color('secondary'), // Warna tombol, bisa disesuaikan
        ];
    }
}
