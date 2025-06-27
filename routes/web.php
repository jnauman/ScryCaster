<?php

use App\Livewire\EncounterDashboard;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;
use App\Models\User; // Added for type hinting, though Auth::user() is fine
use App\Models\Encounter; // Required for type hinting and potentially static methods if used

Route::get('/', function () {
	if (auth()->check()) {
		// If logged in, redirect to the Filament dashboard
		return redirect()->route('filament.app.pages.dashboard');
	}

	// If a guest, show the public landing page
	return view('welcome');
});

Route::get('dashboard', function () {
    /** @var \App\Models\User $user */
    $user = Auth::user();
    $gmEncounters = collect();
    $playerEncounters = collect();

    // 1. Get encounters from campaigns the user GMs
    // Eager load encounters and their campaign to avoid N+1 queries
    // and to have campaign name readily available in the view.
    $gmCampaigns = $user->campaignsGm()->with('encounters.campaign')->get();
    foreach ($gmCampaigns as $campaign) {
        $gmEncounters = $gmEncounters->merge($campaign->encounters);
    }

    // 2. Get encounters where the user's characters are participants
    // Eager load encounters and their campaign
    $characters = $user->characters()->with('encounters.campaign')->get();
    foreach ($characters as $character) {
        $playerEncounters = $playerEncounters->merge($character->encounters);
    }

    // 3. Merge and unique the collections by encounter ID
    $allEncounters = $gmEncounters->merge($playerEncounters)
                                  ->unique('id')
                                  ->sortBy('name') // Sort by name for consistent ordering
                                  ->values(); // Re-index the collection

    return view('dashboard', ['encounters' => $allEncounters]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
	Route::get('/encounter/{encounter}', EncounterDashboard::class)->name('encounter.dashboard');

});

require __DIR__.'/auth.php';
