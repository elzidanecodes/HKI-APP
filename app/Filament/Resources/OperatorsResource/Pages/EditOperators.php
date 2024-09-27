<?php

namespace App\Filament\Resources\OperatorsResource\Pages;

use App\Filament\Resources\OperatorsResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOperators extends EditRecord
{
    protected static string $resource = OperatorsResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
