<?php

namespace App\Providers;

use App\Models\Character;
use App\Policies\CharacterPolicy;
use App\Models\Monster;
use App\Policies\MonsterPolicy;
use App\Models\MonsterInstance;
use App\Policies\MonsterInstancePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Character::class => CharacterPolicy::class,
        Monster::class => MonsterPolicy::class,
        MonsterInstance::class => MonsterInstancePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        //
    }
}
