<?php

namespace App\Filament\Resources\SiosResource\Pages;

use App\Filament\Resources\SiosResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSios extends ListRecords
{
    protected static string $resource = SiosResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
