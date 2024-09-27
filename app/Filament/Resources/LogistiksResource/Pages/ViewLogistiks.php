<?php

namespace App\Filament\Resources\LogistiksResource\Pages;

use App\Filament\Resources\OperatorsResource;
use Filament\Resources\Pages\ViewRecord;

class ViewLogistiks extends ViewRecord
{
    protected static string $resource = OperatorsResource::class;

    protected function getTitle(): string
    {
        return 'Lihat Data Logistik';
    }
}