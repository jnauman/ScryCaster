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
use Illuminate\Support\Facades\Broadcast; // Added for channel routes

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

        // Load broadcast channel routes
        if (file_exists(base_path('routes/channels.php'))) {
            Broadcast::routes(); // This is generally preferred for Laravel 8+
            // For older versions or more direct control, you might see:
            // require base_path('routes/channels.php');
        }
    }
}
