<?php

namespace App\Filament\Resources\SilosResource\Pages;

use App\Filament\Resources\SilosResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListSilos extends ListRecords
{
    protected static string $resource = SilosResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    /**
     * TECHNICAL_AUDIT.md M1 / IMPLEMENTATION_PLAN.md Milestone M5.1:
     * SilosResource::getTableQuery() never executed — Filament v2
     * Resources are static, so that hook point doesn't exist there. This
     * page is a Livewire component (it does get instantiated), so its
     * own getTableQuery() is the working equivalent — same pattern
     * OperatorAssignmentsRelationManager already uses.
     */
    protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()
            ->orderByDesc('tanggal_expired');
    }
}
