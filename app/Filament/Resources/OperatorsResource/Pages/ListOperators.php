<?php

namespace App\Filament\Resources\OperatorsResource\Pages;

use App\Exports\OperatorsExport;
use App\Filament\Resources\OperatorsResource;
use Filament\Forms\Components\Actions\Modal\Actions\Action;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;
use Maatwebsite\Excel\Facades\Excel;

class ListOperators extends ListRecords
{
    protected static string $resource = OperatorsResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Buat baru'),
            Actions\Action::make('export')
                ->label('Export ke Excel')
                ->action(function () {
                    return Excel::download(new OperatorsExport, 'operators.xlsx');
                }),
                
        ];
    }
}
