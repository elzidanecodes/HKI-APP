<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use App\Models\OperatorAlatAssignment;


class ActiveAssignmentsBySta extends Widget
{
    protected static string $view = 'filament.widgets.active-assignments-by-sta';
    protected static ?int $sort = 2;
    


    protected function getViewData(): array
    {
        return [
            'assignments' => OperatorAlatAssignment::with(['operator', 'alatBerat'])
                ->where('is_active', true)
                ->get(),
        ];
    }
}