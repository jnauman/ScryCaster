<?php

namespace App\Policies;

use App\Models\Character;
use App\Models\User;
// use Illuminate\Auth\Access\HandlesAuthorization; // Not used as explicit booleans are returned.
// For Laravel 10+, you might use Illuminate\Auth\Access\Response.

/**
 * Policy for Character model.
 *
 * Defines authorization rules for actions related to characters, such as viewing,
 * creating, updating, and deleting.
 * The current policy primarily restricts actions to the character's owner (User).
 * GM override logic is noted as a potential future enhancement.
 */
class CharacterPolicy
{
	/**
	 * Determine whether the user can view any characters.
	 *
	 * This policy method is typically used for index pages (e.g., a global list of characters).
	 * Currently allows any authenticated user to access such a list.
	 * Filtering (e.g., by ownership or campaign) would be handled at the query level.
	 *
	 * @param  \App\Models\User  $user The currently authenticated user.
	 * @return bool True if the user can view any characters, false otherwise.
	 */
	public function viewAny(User $user): bool
	{
		// Allows any authenticated user to access a character list view.
		// Specific resource query scoping (e.g., in CharacterResource) would handle
		// showing only relevant characters if this policy is too broad for a context.
		return true;
	}

	/**
	 * Determine whether the user can view a specific character.
	 *
	 * A user can view a character if they are its owner.
	 * Future enhancements could include allowing GMs of campaigns the character is in to view it.
	 *
	 * @param  \App\Models\User  $user The currently authenticated user.
	 * @param  \App\Models\Character  $character The character being viewed.
	 * @return bool True if the user can view the character, false otherwise.
	 */
	public function view(User $user, Character $character): bool
	{
		// Only the user who owns the character can view it.
		// Note: $character->user_id can be null for monster-type characters.
		// If $character->user_id is null, this will correctly return false (unless $user->id is also null, which is unlikely for an authenticated user).
		return $user->id === $character->user_id;

		// Potential future enhancement for GM access:
		// if ($user->id === $character->user_id) {
		//     return true;
		// }
		// return $user->campaignsGm()->whereHas('characters', function ($query) use ($character) {
		//     $query->where('characters.id', $character->id);
		// })->exists();
	}

	/**
	 * Determine whether the user can create characters.
	 *
	 * Any authenticated user can create characters. The character's `user_id`
	 * should be set to the creating user's ID during the creation process.
	 *
	 * @param  \App\Models\User  $user The currently authenticated user.
	 * @return bool True if the user can create characters, false otherwise.
	 */
	public function create(User $user): bool
	{
		// Any authenticated user can create a character.
		// It's assumed the character's user_id will be associated with this user upon creation.
		return true;
	}

	/**
	 * Determine whether the user can update the specified character.
	 *
	 * Only the owner of the character can update it.
	 *
	 * @param  \App\Models\User  $user The currently authenticated user.
	 * @param  \App\Models\Character  $character The character to be updated.
	 * @return bool True if the user can update the character, false otherwise.
	 */
	public function update(User $user, Character $character): bool
	{
		// Only the user who owns the character can update it.
		return $user->id === $character->user_id;
		// Add GM logic later if needed (e.g., GM can update characters in their campaign).
	}

	/**
	 * Determine whether the user can delete the specified character.
	 *
	 * Only the owner of the character can delete it.
	 *
	 * @param  \App\Models\User  $user The currently authenticated user.
	 * @param  \App\Models\Character  $character The character to be deleted.
	 * @return bool True if the user can delete the character, false otherwise.
	 */
	public function delete(User $user, Character $character): bool
	{
		// Only the user who owns the character can delete it.
		return $user->id === $character->user_id;
		// Add GM logic later if needed (e.g., GM can delete characters in their campaign).
	}

	// Soft delete methods (restore, forceDelete) would follow a similar pattern if implemented.
	// public function restore(User $user, Character $character): bool
	// {
	//     return $user->id === $character->user_id;
	// }

	// public function forceDelete(User $user, Character $character): bool
	// {
	//     return $user->id === $character->user_id;
	// }
}