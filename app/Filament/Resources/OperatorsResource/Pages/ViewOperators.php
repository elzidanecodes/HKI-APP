<?php

namespace App\Filament\Resources\OperatorsResource\Pages;

use App\Filament\Resources\OperatorsResource;
use Filament\Resources\Pages\ViewRecord;

class ViewOperators extends ViewRecord
{
    protected static string $resource = OperatorsResource::class;

    protected function getTitle(): string
    {
        return 'Lihat Data Operator';
    }
}