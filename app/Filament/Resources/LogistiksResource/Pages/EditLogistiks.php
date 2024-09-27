<?php

namespace App\Filament\Resources\LogistiksResource\Pages;

use App\Filament\Resources\LogistiksResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLogistiks extends EditRecord
{
    protected static string $resource = LogistiksResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
