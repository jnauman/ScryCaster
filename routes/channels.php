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
    Log::debug("Broadcasting Auth: Attempting for user {$user->id} on encounter {$encounterId}.");

    // Eager load necessary relationships for efficiency.
    // Corrected to use 'playerCharacters' based on model analysis.
    $encounter = Encounter::with(['campaign', 'playerCharacters'])->find($encounterId);

    if (!$encounter) {
        Log::warning("Broadcasting Auth: Encounter {$encounterId} not found for user {$user->id}.");
        return false;
    }
    Log::debug("Broadcasting Auth: Encounter {$encounterId} found: " . $encounter->name);

    // Rule 1: User is the Game Master of the campaign this encounter belongs to.
    if ($encounter->campaign) {
        Log::debug("Broadcasting Auth: Campaign found: " . $encounter->campaign->name . ". GM User ID: " . ($encounter->campaign->gm_user_id ?? 'Not set'));
        if (isset($encounter->campaign->gm_user_id) && $user->id === $encounter->campaign->gm_user_id) {
            Log::info("Broadcasting Auth: User {$user->id} AUTHORIZED as GM for encounter {$encounterId}.");
            return ['id' => $user->id, 'name' => $user->name];
        }
    } else {
        Log::debug("Broadcasting Auth: No campaign associated with encounter {$encounterId}.");
    }

    // Rule 2: User has a character participating in this encounter.
    // Using the eager-loaded 'playerCharacters' relationship.
    Log::debug("Broadcasting Auth: Checking player characters for user {$user->id} in encounter {$encounterId}. Total player characters: " . $encounter->playerCharacters->count());
    foreach ($encounter->playerCharacters as $character) {
        Log::debug("Broadcasting Auth: Checking character ID {$character->id} (User ID: {$character->user_id}) against logged in user {$user->id}.");
        if (isset($character->user_id) && $character->user_id === $user->id) {
            Log::info("Broadcasting Auth: User {$user->id} AUTHORIZED as player with character ID {$character->id} for encounter {$encounterId}.");
            return ['id' => $user->id, 'name' => $user->name];
        }
    }
    
    Log::warning("Broadcasting Auth: User {$user->id} DENIED for encounter {$encounterId}. Did not match GM or participating player character.");
    return false;
});
