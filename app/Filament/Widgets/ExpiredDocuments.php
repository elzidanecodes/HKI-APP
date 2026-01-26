<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use App\Models\Sios;
use App\Models\Silos;

class ExpiredDocuments extends Widget
{
    protected static string $view = 'filament.widgets.expired-documents';
    protected static ?int $sort = 3;
    

    protected function getViewData(): array
    {
        return [
            'expiredSio' => Sios::whereDate('tanggal_expired', '<', today())->get(),
            'expiredSilo' => Silos::whereDate('tanggal_expired', '<', today())->get(),
        ];
    }
}