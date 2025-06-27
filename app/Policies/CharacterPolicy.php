<?php

namespace App\Policies;

use App\Models\Character;
use App\Models\User;
// use Illuminate\Auth\Access\HandlesAuthorization; // Not used as explicit booleans are returned.
// For Laravel 10+, you might use Illuminate\Auth\Access\Response.

/**
 * Policy for the Character model (Player Characters).
 *
 * Defines authorization rules for actions related to player characters, such as viewing,
 * creating, updating, and deleting.
 * The policy primarily restricts actions to the player character's owner (User/GM).
 */
class CharacterPolicy
{
	/**
     * Determine whether the user can view any player characters.
     *
     * This policy method is typically used for index pages.
     * Currently allows any authenticated user to access such a list.
     * Filtering by ownership is handled at the Filament resource query level.
     *
     * @param User $user The currently authenticated user.
     * @return bool True if the user can view any player characters.
     */
    public function viewAny(User $user): bool
	{
		// Allows any authenticated user to access a player character list view.
		// The CharacterResource query will scope this to the user's own characters.
		return true;
	}

	/**
     * Determine whether the user can view a specific player character.
     *
     * A user can view a player character if they are its owner.
     *
     * @param User $user The currently authenticated user.
     * @param Character $character The player character being viewed.
     * @return bool True if the user can view the player character.
     */
    public function view(User $user, Character $character): bool
	{
		// Only the user who owns the player character can view it.
		// $character->user_id is non-nullable for player characters.
		return $user->id === $character->user_id;
	}

	/**
     * Determine whether the user can create player characters.
     *
     * Any authenticated user can create player characters. The character's `user_id`
     * will be set to the creating user's ID.
     *
     * @param User $user The currently authenticated user.
     * @return bool True if the user can create player characters.
     */
    public function create(User $user): bool
	{
		// Any authenticated user can create a player character.
		// The CharacterResource will ensure user_id is set to the authenticated user.
		return true;
	}

	/**
     * Determine whether the user can update the specified player character.
     *
     * Only the owner of the player character can update it.
     *
     * @param User $user The currently authenticated user.
     * @param Character $character The player character to be updated.
     * @return bool True if the user can update the player character.
     */
    public function update(User $user, Character $character): bool
	{
		// Only the user who owns the player character can update it.
		return $user->id === $character->user_id;
	}

	/**
     * Determine whether the user can delete the specified player character.
     *
     * Only the owner of the player character can delete it.
     *
     * @param User $user The currently authenticated user.
     * @param Character $character The player character to be deleted.
     * @return bool True if the user can delete the player character.
     */
    public function delete(User $user, Character $character): bool
	{
		// Only the user who owns the player character can delete it.
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