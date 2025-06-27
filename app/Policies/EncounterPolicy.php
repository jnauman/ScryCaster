<?php

namespace App\Policies;

use App\Models\Encounter;
use App\Models\User;
// For Laravel 10+, you might use Illuminate\Auth\Access\Response.

/**
 * Policy for Encounter model.
 *
 * Defines authorization rules for actions related to encounters, such as viewing,
 * creating, updating, and deleting. Access is generally restricted to the
 * Game Master (GM) of the parent campaign or players involved in the encounter.
 */
class EncounterPolicy
{
	/**
     * Determine whether the user can view any encounters.
     *
     * This policy method is typically used for index pages.
     * Actual filtering of encounters (e.g., showing only encounters from GM's campaigns)
     * should be handled at the query level in the resource or controller.
     * Returning true here allows access to the encounter listing page; the query does the filtering.
     *
     * @param User $user The currently authenticated user.
     * @return bool True if the user can view any encounters, false otherwise.
     */
    public function viewAny(User $user): bool
	{
		// Allows any authenticated user to access the encounter list view.
		// The EncounterResource::getEloquentQuery() or similar should handle scoping
		// to only relevant encounters (e.g., those belonging to the user's campaigns).
		return true;
	}

	/**
     * Determine whether the user can view a specific encounter.
     *
     * A user can view an encounter if they are the GM of its parent campaign,
     * or if they are a player whose character is participating in the encounter.
     *
     * @param User $user The currently authenticated user.
     * @param Encounter $encounter The encounter being viewed.
     * @return bool True if the user can view the encounter, false otherwise.
     */
    public function view(User $user, Encounter $encounter): bool
	{
		// Eager load the campaign relationship if not already loaded to avoid extra queries.
		$encounter->loadMissing('campaign');

		// Check if the user is the Game Master of the campaign this encounter belongs to.
		if ($encounter->campaign && $encounter->campaign->gm_user_id === $user->id) {
			return true;
		}

		// Check if the user has any character participating in this specific encounter.
		// This assumes 'characters' is a relationship on the Encounter model,
		// and 'user_id' is an attribute on the Character model.
		return $encounter->playerCharacters()->where('user_id', $user->id)->exists();
	}

	/**
     * Determine whether the user can create encounters.
     *
     * Currently, any authenticated user is allowed to create an encounter.
     * However, in practice, creation is typically done via a Campaign's relation manager,
     * which would implicitly limit creation to GMs of that campaign.
     *
     * @param User $user The currently authenticated user.
     * @return bool True if the user can create encounters, false otherwise.
     */
    public function create(User $user): bool
	{
		// Allows any authenticated user to initiate encounter creation.
		// The form (e.g., in EncounterResource) should restrict campaign selection to those the user GMs.
		return true;
	}

	/**
     * Determine whether the user can update the specified encounter.
     *
     * Only the Game Master (GM) of the parent campaign can update the encounter.
     *
     * @param User $user The currently authenticated user.
     * @param Encounter $encounter The encounter to be updated.
     * @return bool True if the user can update the encounter, false otherwise.
     */
    public function update(User $user, Encounter $encounter): bool
	{
		$encounter->loadMissing('campaign'); // Ensure campaign is loaded.
		// Only the GM of the campaign this encounter belongs to can update it.
		return $encounter->campaign && $encounter->campaign->gm_user_id === $user->id;
	}

	/**
     * Determine whether the user can delete the specified encounter.
     *
     * Only the Game Master (GM) of the parent campaign can delete the encounter.
     *
     * @param User $user The currently authenticated user.
     * @param Encounter $encounter The encounter to be deleted.
     * @return bool True if the user can delete the encounter, false otherwise.
     */
    public function delete(User $user, Encounter $encounter): bool
	{
		$encounter->loadMissing('campaign'); // Ensure campaign is loaded.
		// Only the GM of the campaign this encounter belongs to can delete it.
		return $encounter->campaign && $encounter->campaign->gm_user_id === $user->id;
	}

	// Soft delete methods (restore, forceDelete) would follow a similar pattern if implemented.
    /**
     * Determine whether the user can add a character to the encounter (e.g., via relation manager).
     *
     * Only the Game Master (GM) of the parent campaign can add characters.
     *
     * @param User $user The currently authenticated user.
     * @param Encounter $encounter The encounter to which a character might be added.
     * @return bool True if the user can add characters, false otherwise.
     */
    public function addCharacter(User $user, Encounter $encounter): bool
	{
		$encounter->loadMissing('campaign');
		return $encounter->campaign && $encounter->campaign->gm_user_id === $user->id;
	}

	/**
     * Determine whether the user can attach any character to the encounter (e.g., via relation manager).
     *
     * Only the Game Master (GM) of the parent campaign can attach characters.
     *
     * @param User $user The currently authenticated user.
     * @param Encounter $encounter The encounter.
     * @return bool True if the user can attach characters, false otherwise.
     */
    public function attachAnyCharacter(User $user, Encounter $encounter): bool
	{
		$encounter->loadMissing('campaign');
		return $encounter->campaign && $encounter->campaign->gm_user_id === $user->id;
	}

	/**
     * Determine whether the user can detach any character from the encounter (e.g., via relation manager).
     *
     * Only the Game Master (GM) of the parent campaign can detach characters.
     *
     * @param User $user The currently authenticated user.
     * @param Encounter $encounter The encounter.
     * @return bool True if the user can detach characters, false otherwise.
     */
    public function detachAnyCharacter(User $user, Encounter $encounter): bool
	{
		$encounter->loadMissing('campaign');
		return $encounter->campaign && $encounter->campaign->gm_user_id === $user->id;
	}

	/**
     * Determine whether the user can access the "run" page for this encounter.
     *
     * Access is granted if the user can view the encounter (GM or participating player).
     *
     * @param User $user The currently authenticated user.
     * @param Encounter $encounter The encounter.
     * @return bool True if the user can run the encounter, false otherwise.
     */
    public function run(User $user, Encounter $encounter): bool
	{
		// Leverages the same logic as viewing an encounter.
		// If a user can view it (GM or involved player), they can access the run page.
		return $this->view($user, $encounter);
	}
}