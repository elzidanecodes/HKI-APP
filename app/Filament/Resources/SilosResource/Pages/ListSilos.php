<?php

namespace App\Filament\Resources\SilosResource\Pages;

use App\Filament\Resources\SilosResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSilos extends ListRecords
{
    protected static string $resource = SilosResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
