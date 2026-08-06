<?php

namespace App\Providers;

use App\Models\AlatBerats;
use App\Models\Logistiks;
use App\Models\Operators;
use App\Models\Silos;
use App\Models\Sios;
use App\Models\User;
use App\Policies\AlatBeratPolicy;
use App\Policies\LogistikPolicy;
use App\Policies\OperatorPolicy;
use App\Policies\SiloPolicy;
use App\Policies\SioPolicy;
use App\Policies\UserPolicy;
// use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * Explicit mapping, not naming-convention discovery: Policy class
     * names are already singular (AlatBeratPolicy) ahead of the plural
     * model names Milestone M5.3 will rename, so Laravel's automatic
     * <Model>Policy discovery would not find them.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        AlatBerats::class => AlatBeratPolicy::class,
        Operators::class => OperatorPolicy::class,
        Silos::class => SiloPolicy::class,
        Sios::class => SioPolicy::class,
        Logistiks::class => LogistikPolicy::class,
        User::class => UserPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        //
    }
}
