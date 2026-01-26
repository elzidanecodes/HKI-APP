<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use App\Models\AlatBerats;
use App\Models\Operators;
use App\Models\OperatorAlatAssignment;
use App\Models\Sios;
use App\Models\Silos;

class HseStats extends StatsOverviewWidget
{
    protected static ?int $sort = 1;
    protected function getCards(): array
    {
        return [
            $this->makeCard(
                'Alat Berat',
                AlatBerats::count(),
                'Total alat terdaftar',
                'primary'
            )
            ->url(route('filament.resources.alat-berats.index'))
            ->openUrlInNewTab(),

            $this->makeCard(
                'Operator',
                Operators::count(),
                'Total operator',
                'success'
            )
            ->url(route('filament.resources.operators.index'))
            ->openUrlInNewTab(),

            $this->makeCard(
                'Assignment Aktif',
                OperatorAlatAssignment::where('is_active', true)->count(),
                'Sedang beroperasi',
                'success'
            ),

            $this->makeCard(
                'SILO Expired',
                + Silos::whereDate('tanggal_expired', '<', now())->count(),
                'SILO',
                'danger',
            )
            ->url(route('filament.resources.silos.index'))
            ->openUrlInNewTab(),

            $this->makeCard(
                'SIO Expired',
                Sios::whereDate('tanggal_expired', '<', now())->count(),
                'SIO',
                'danger',
            )
            ->url(route('filament.resources.sios.index'))
            ->openUrlInNewTab(),
        ];
    }

    protected function makeCard(string $title, int $value, string $description, string $color)
    {
        return \Filament\Widgets\StatsOverviewWidget\Card::make($title, $value)
            ->description($description)
            ->color($color);
    }
}