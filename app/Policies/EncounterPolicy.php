<?php

namespace App\Policies;

use App\Models\Encounter;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization; // Or Response

class EncounterPolicy
{
	// use HandlesAuthorization;

	/**
	 * Determine whether the user can view any models.
	 * For Filament lists, rely on resource query scoping to show only GM's encounters.
	 */
	public function viewAny(User $user): bool
	{
		return true; // Allow access to the resource page itself.
	}

	/**
	 * Determine whether the user can view the model.
	 * GM of the campaign, or player whose character is in the encounter.
	 */
	public function view(User $user, Encounter $encounter): bool
	{
		// Ensure campaign relationship is loaded
		$encounter->loadMissing('campaign');

		// Is the user the GM of the campaign?
		if ($encounter->campaign && $encounter->campaign->gm_user_id === $user->id) {
			return true;
		}

		// Does the user have a character participating in this encounter?
		// Assumes 'characters' relationship exists on Encounter model
		// and 'user_id' exists on Character model
		return $encounter->characters()->where('user_id', $user->id)->exists();
	}

	/**
	 * Determine whether the user can create models.
	 * Allow any authenticated user FOR NOW, but creation might be restricted
	 * to GMs within the context of a Campaign Resource / Relation Manager.
	 */
	public function create(User $user): bool
	{
		// You might check if the user is a GM of *any* campaign here if needed.
		return true;
	}

	/**
	 * Determine whether the user can update the model.
	 * Only the GM of the parent campaign.
	 */
	public function update(User $user, Encounter $encounter): bool
	{
		$encounter->loadMissing('campaign');
		return $encounter->campaign && $encounter->campaign->gm_user_id === $user->id;
	}

	/**
	 * Determine whether the user can delete the model.
	 * Only the GM of the parent campaign.
	 */
	public function delete(User $user, Encounter $encounter): bool
	{
		$encounter->loadMissing('campaign');
		return $encounter->campaign && $encounter->campaign->gm_user_id === $user->id;
	}

	// Add policy methods for relation managers if needed, e.g., addCharacter
	public function addCharacter(User $user, Encounter $encounter): bool
	{
		$encounter->loadMissing('campaign');
		return $encounter->campaign && $encounter->campaign->gm_user_id === $user->id;
	}
	public function attachAnyCharacter(User $user, Encounter $encounter): bool
	{
		$encounter->loadMissing('campaign');
		return $encounter->campaign && $encounter->campaign->gm_user_id === $user->id;
	}
	public function detachAnyCharacter(User $user, Encounter $encounter): bool
	{
		$encounter->loadMissing('campaign');
		return $encounter->campaign && $encounter->campaign->gm_user_id === $user->id;
	}
	public function run(User $user, Encounter $encounter): bool
	{
		return $this->view($user, $encounter); // Use same logic as view for run page access
	}

}