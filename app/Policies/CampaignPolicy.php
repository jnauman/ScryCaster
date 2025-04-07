<?php

namespace App\Policies;

use App\Models\Campaign;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization; // Or Response for Laravel 10+

class CampaignPolicy
{
	// Use HandlesAuthorization for Laravel <10 or Response class for Laravel 10+
	// For simplicity, we'll use simple boolean returns here.
	// use HandlesAuthorization;

	/**
	 * Determine whether the user can view any models.
	 * For Filament lists, only show campaigns the user GMs.
	 */
	public function viewAny(User $user): bool
	{
		// Typically, you only want GMs seeing the main list in Filament.
		// We previously scoped the query in CampaignResource, which is fine,
		// but defining it here is more standard. Policy checks usually apply
		// AFTER the query scope, so scoping in the resource is still useful.
		return true; // Allow access to the resource page itself, rely on query scope for listing.
	}

	/**
	 * Determine whether the user can view the model.
	 * GM or a player whose character is in the campaign.
	 */
	public function view(User $user, Campaign $campaign): bool
	{
		// Is the user the GM?
		if ($campaign->gm_user_id === $user->id) {
			return true;
		}

		// Does the user have a character participating in this campaign?
		// Assumes 'characters' relationship exists on User model
		// and 'campaigns' relationship exists on Character model
		return $user->characters()->whereHas('campaigns', function ($query) use ($campaign) {
			$query->where('campaigns.id', $campaign->id);
		})->exists();
	}

	/**
	 * Determine whether the user can create models.
	 * Allow any authenticated user to create a campaign for now.
	 */
	public function create(User $user): bool
	{
		return true;
	}

	/**
	 * Determine whether the user can update the model.
	 * Only the GM can update.
	 */
	public function update(User $user, Campaign $campaign): bool
	{
		return $campaign->gm_user_id === $user->id;
	}

	/**
	 * Determine whether the user can delete the model.
	 * Only the GM can delete.
	 */
	public function delete(User $user, Campaign $campaign): bool
	{
		return $campaign->gm_user_id === $user->id;
	}

	/**
	 * Determine whether the user can restore the model. (If using Soft Deletes)
	 */
	// public function restore(User $user, Campaign $campaign): bool
	// {
	//     return $campaign->gm_user_id === $user->id;
	// }

	/**
	 * Determine whether the user can permanently delete the model. (If using Soft Deletes)
	 */
	// public function forceDelete(User $user, Campaign $campaign): bool
	// {
	//     return $campaign->gm_user_id === $user->id;
	// }

	/**
	 * Determine whether the user can attach characters to the campaign.
	 * Only the GM should be able to. (Used by Relation Manager)
	 * Adjust method names if needed (attachAnyCharacter, attachCharacter).
	 */
	public function addCharacter(User $user, Campaign $campaign): bool
	{
		return $campaign->gm_user_id === $user->id;
	}

	public function attachAnyCharacter(User $user, Campaign $campaign): bool
	{
		return $campaign->gm_user_id === $user->id;
	}
	public function detachAnyCharacter(User $user, Campaign $campaign): bool
	{
		return $campaign->gm_user_id === $user->id;
	}
}