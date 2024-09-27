<?php

namespace App\Filament\Resources\LogistiksResource\Pages;

use App\Filament\Resources\LogistiksResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateLogistiks extends CreateRecord
{
    protected static string $resource = LogistiksResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
