<?php

namespace App\Filament\Resources\OperatorsResource\Pages;

use App\Filament\Concerns\HasStandardEditFormActions;
use App\Filament\Resources\OperatorsResource;
use Filament\Notifications\Notification;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOperators extends EditRecord
{
    use HasStandardEditFormActions;

    protected static string $resource = OperatorsResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->authorize('delete', $this->getRecord()),
        ];
    }

    // Ubah judul halaman
    protected function getTitle(): string
    {
        return 'Ubah Data Operator';
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->title('Data Operator Diperbarui')
            ->body('Data operator telah berhasil diperbarui.')
            ->success();
    }
}
