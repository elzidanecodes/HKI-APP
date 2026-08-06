<?php

namespace App\Filament\Resources\SilosResource\Pages;

use App\Application\Compliance\SiloExpiryCalculator;
use App\Filament\Resources\SilosResource;
use Carbon\CarbonImmutable;
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

    /**
     * Silos::booted() previously recomputed tanggal_expired from
     * tanggal_terbit on every save, including edits — removed in
     * IMPLEMENTATION_PLAN.md Milestone M2.7 (TECHNICAL_AUDIT.md M6). This
     * preserves that behavior for edits specifically, since editing
     * doesn't go through IssueDocument/RenewDocument.
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (! empty($data['tanggal_terbit'])) {
            $data['tanggal_expired'] = SiloExpiryCalculator::expiresAt(
                CarbonImmutable::parse($data['tanggal_terbit'])
            )->toDateString();
        }

        return $data;
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
