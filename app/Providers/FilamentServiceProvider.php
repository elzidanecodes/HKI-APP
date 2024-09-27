<?php
namespace App\Providers;

use Filament\Facades\Filament;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class FilamentServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Filament::registerRenderHook('head.end', function () {
            return <<<'HTML'
                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        document.querySelectorAll('[wire\\:click="logout"]').forEach(function (logoutButton) {
                            logoutButton.addEventListener('click', function (event) {
                                event.preventDefault();
                                fetch('/logout', {
                                    method: 'POST',
                                    headers: {
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                    },
                                }).then(() => {
                                    window.location.href = '/';
                                });
                            });
                        });
                    });
                </script>
            HTML;
        });
    }
}