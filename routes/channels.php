<?php

use App\Models\Encounter;
use App\Models\User; // Added User model import
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Log; // Kept for now, can be removed if logging is not desired

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
| Channels are defined using `Broadcast::channel('channel-name', callback)`.
| The callback receives the authenticated user and any route parameters from the channel name.
| It should return true or an array if the user is authorized, false otherwise.
|
*/

/**
 * Default channel for user-specific notifications.
 * Authorizes a user to listen on their own private channel.
 * Example channel name: App.Models.User.1
 *
 * @param \App\Models\User $user The authenticated user instance.
 * @param int $id The user ID from the channel name.
 * @return bool True if the authenticated user's ID matches the ID in the channel name.
 */
Broadcast::channel('App.Models.User.{id}', function (User $user, int $id) {
    return (int) $user->id === (int) $id;
});

/**
 * Private channel for real-time encounter updates.
 * Authorizes a user to listen on a specific encounter's channel.
 * Example channel name: encounter.123 (translates to private-encounter.123)
 *
 * Authorization logic:
 * 1. The encounter must exist.
 * 2. The user must be the Game Master (GM) of the campaign the encounter belongs to.
 * OR
 * 3. The user must have a character participating in the encounter.
 *
 * @param \App\Models\User $user The authenticated user instance.
 * @param int $encounterId The ID of the encounter from the channel name.
 * @return bool|array True or user data if authorized, false otherwise.
 *                    Returning an array of user data can be useful for presence channels.
 */
Broadcast::channel('encounter.{encounterId}', function (User $user, int $encounterId) {
    // Log::info("Attempting to authorize user {$user->id} for encounter {$encounterId}");

    // Eager load necessary relationships for efficiency.
    $encounter = Encounter::with('campaign', 'characters')->find($encounterId);

    if (!$encounter) {
        // Log::warning("Encounter {$encounterId} not found for authorization.");
        return false; // Encounter does not exist, deny authorization.
    }

    // Rule 1: User is the Game Master of the campaign this encounter belongs to.
    if ($encounter->campaign && $user->id === $encounter->campaign->gm_user_id) {
        // Log::info("User {$user->id} authorized as GM for encounter {$encounterId}");
        // For private channels, returning true is sufficient for authorization.
        // Returning an array of user data is primarily for presence channels to share who is listening.
        return ['id' => $user->id, 'name' => $user->name]; // Or simply return true;
    }

    // Rule 2: User has a character participating in this encounter.
    // This assumes the Character model has a 'user_id' field linking it to a User.
    if ($encounter->characters()->where('user_id', $user->id)->exists()) {
        // Log::info("User {$user->id} authorized as player for encounter {$encounterId}");
        return ['id' => $user->id, 'name' => $user->name]; // Or simply return true;
    }
    
    // Log::warning("User {$user->id} failed authorization for encounter {$encounterId}");
    return false; // User is not authorized if none of the above conditions are met.
});
