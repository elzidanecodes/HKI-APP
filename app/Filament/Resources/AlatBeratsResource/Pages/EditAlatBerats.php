<?php

namespace App\Filament\Resources\AlatBeratsResource\Pages;

use App\Filament\Resources\AlatBeratsResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAlatBerats extends EditRecord
{
    protected static string $resource = AlatBeratsResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
