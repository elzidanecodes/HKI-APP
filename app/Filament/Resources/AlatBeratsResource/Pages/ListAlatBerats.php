<?php

namespace App\Filament\Resources\AlatBeratsResource\Pages;

use App\Filament\Resources\AlatBeratsResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAlatBerats extends ListRecords
{
    protected static string $resource = AlatBeratsResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
