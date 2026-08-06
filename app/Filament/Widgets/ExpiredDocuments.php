<?php

namespace App\Filament\Widgets;

use App\Models\Silos;
use App\Models\Sios;
use Filament\Widgets\Widget;

class ExpiredDocuments extends Widget
{
    protected static string $view = 'filament.widgets.expired-documents';

    protected static ?int $sort = 3;

    protected function getViewData(): array
    {
        return [
            'expiredSio' => Sios::expired()->get(),
            'expiredSilo' => Silos::expired()->get(),
        ];
    }
}
