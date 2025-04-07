<?php

namespace App\Policies;

use App\Models\Character;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization; // Or Response

class CharacterPolicy
{
	// use HandlesAuthorization;

	/**
	 * Determine whether the user can view any models.
	 * Allow access to the list page, rely on query scoping in Resource.
	 */
	public function viewAny(User $user): bool
	{
		return true;
	}

	/**
	 * Determine whether the user can view the model.
	 * User must own the character OR be a GM of a campaign the character is in?
	 * Let's start simple: Only owner can view detailed page.
	 */
	public function view(User $user, Character $character): bool
	{
		return $user->id === $character->user_id;
		// Add GM logic here later if needed, e.g.:
		// || $user->campaignsGm()->whereHas('characters', fn($q) => $q->where('characters.id', $character->id))->exists()
	}

	/**
	 * Determine whether the user can create models.
	 * Any authenticated user can create characters for themselves.
	 */
	public function create(User $user): bool
	{
		return true;
	}

	/**
	 * Determine whether the user can update the model.
	 * Only the owner.
	 */
	public function update(User $user, Character $character): bool
	{
		return $user->id === $character->user_id;
		// Add GM logic later if needed
	}

	/**
	 * Determine whether the user can delete the model.
	 * Only the owner.
	 */
	public function delete(User $user, Character $character): bool
	{
		return $user->id === $character->user_id;
		// Add GM logic later if needed
	}
}