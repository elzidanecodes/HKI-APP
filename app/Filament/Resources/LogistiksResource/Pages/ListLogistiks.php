<?php

namespace App\Filament\Resources\LogistiksResource\Pages;

use App\Filament\Resources\LogistiksResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLogistiks extends ListRecords
{
    protected static string $resource = LogistiksResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
