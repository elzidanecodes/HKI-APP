<?php

namespace App\Filament\Resources\OperatorsResource\Pages;

use App\Filament\Resources\OperatorsResource;
use Filament\Notifications\Notification;
use Filament\Pages\Actions;
use Filament\Pages\Actions\ButtonAction;
use Filament\Pages\Actions\CreateAction;
use Filament\Resources\Pages\CreateRecord;

class CreateOperators extends CreateRecord
{
    protected static string $resource = OperatorsResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    // Mengubah judul halaman Create
    protected function getTitle(): string
    {
        return 'Tambah Data Operator';
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->title('Data Operator Ditambahkan')
            ->body('Data operator telah berhasil ditambahkan.')
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
