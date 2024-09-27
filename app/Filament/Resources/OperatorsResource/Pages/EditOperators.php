<?php

namespace App\Filament\Resources\OperatorsResource\Pages;

use App\Filament\Resources\OperatorsResource;
use Filament\Pages\Actions;
use Filament\Pages\Actions\ButtonAction;
use Filament\Pages\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Tables\Actions\DeleteAction as ActionsDeleteAction;

class EditOperators extends EditRecord
{
    protected static string $resource = OperatorsResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    // Ubah judul halaman
    protected function getTitle(): string
    {
        return 'Ubah Data Operator';
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

                // DeleteAction::make()
                // ->label('Hapus')
                // ->requiresConfirmation()
                // ->redirect($this->getResource()::getUrl('index'))
                
        ];
    }
}
