<?php

namespace App\Providers;

use Filament\Facades\Filament;
use Filament\Navigation\UserMenuItem;
use Illuminate\Support\ServiceProvider;


class FilamentServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Paksa auth via Jetstream
        Filament::serving(function () {
            if (! auth()->check()) {
                redirect()->route('login')->send();
            }
        });


        // Logout Filament → endpoint logout Jetstream
        Filament::registerUserMenuItems([
            'logout' => UserMenuItem::make()
                ->label('Logout')
                ->url(url('/logout')),
        ]);
        
        Filament::registerWidgets([
            \App\Filament\Widgets\HseStats::class,
            \App\Filament\Widgets\ActiveAssignmentsBySta::class,
            \App\Filament\Widgets\ExpiredDocuments::class,
        ]);
    }
}