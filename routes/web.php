<?php

use App\Livewire\EncounterDashboard;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', function () {
	if (auth()->check()) {
		// If logged in, redirect to the Filament dashboard
		return redirect()->route('filament.app.pages.dashboard');
	}

	// If a guest, show the public landing page
	return view('welcome');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
	Route::get('/encounter/{encounterId}', EncounterDashboard::class)->name('encounter.dashboard');

});

//require __DIR__.'/auth.php';
