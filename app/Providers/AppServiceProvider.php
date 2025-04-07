<?php

namespace App\Providers;

use App\Models\Campaign;
use App\Models\Character;
use App\Models\Encounter;
use App\Policies\CampaignPolicy;
use App\Policies\CharacterPolicy;
use App\Policies\EncounterPolicy;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
		Gate::policy(Campaign::class, CampaignPolicy::class);
		Gate::policy(Encounter::class, EncounterPolicy::class);
		Gate::policy(Character::class, CharacterPolicy::class);
    }
}
