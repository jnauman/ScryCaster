<?php

namespace App\Policies;

use App\Models\Campaign;
use App\Models\User;
// For Laravel 10+, you might use Illuminate\Auth\Access\Response.

/**
 * Policy for Campaign model.
 *
 * Defines authorization rules for actions related to campaigns, such as viewing,
 * creating, updating, deleting, and managing campaign characters.
 * Primarily, it restricts campaign management to the Game Master (GM) of the campaign.
 */
class CampaignPolicy
{
	// Laravel's policy system automatically calls methods like `viewAny`, `view`, `create`, etc.
	// These methods receive the authenticated user and, where applicable, the model instance.

	/**
	 * Determine whether the user can view any campaigns.
	 *
	 * This policy method is typically used for index pages.
	 * Actual filtering of campaigns (e.g., showing only GM's campaigns) is often
	 * handled at the query level in the resource or controller for efficiency.
	 * Returning true here allows access to the campaign listing page; the query does the filtering.
	 *
	 * @param  \App\Models\User  $user The currently authenticated user.
	 * @return bool True if the user can view any campaigns, false otherwise.
	 */
	public function viewAny(User $user): bool
	{
		// Allows any authenticated user to access the campaign list view.
		// The CampaignResource::getEloquentQuery() method handles scoping the list
		// to only campaigns the user GMs.
		return true;
	}

	/**
	 * Determine whether the user can view a specific campaign.
	 *
	 * A user can view a campaign if they are the GM or if they are a player
	 * whose character is part of that campaign.
	 *
	 * @param  \App\Models\User  $user The currently authenticated user.
	 * @param  \App\Models\Campaign  $campaign The campaign being viewed.
	 * @return bool True if the user can view the campaign, false otherwise.
	 */
	public function view(User $user, Campaign $campaign): bool
	{
		// Check if the user is the Game Master of the campaign.
		if ($campaign->gm_user_id === $user->id) {
			return true;
		}

		// Check if the user has any character participating in this specific campaign.
		// This assumes 'characters' is a relationship on the User model,
		// and 'campaigns' is a relationship on the Character model.
		return $user->characters()->whereHas('campaigns', function ($query) use ($campaign) {
			$query->where('campaigns.id', $campaign->id); // Check if any of character's campaigns match this one.
		})->exists();
	}

	/**
	 * Determine whether the user can create campaigns.
	 *
	 * Currently, any authenticated user is allowed to create a new campaign.
	 *
	 * @param  \App\Models\User  $user The currently authenticated user.
	 * @return bool True if the user can create campaigns, false otherwise.
	 */
	public function create(User $user): bool
	{
		// Any authenticated user can create a campaign.
		// The gm_user_id will be set to the creator's ID during the creation process.
		return true;
	}

	/**
	 * Determine whether the user can update the specified campaign.
	 *
	 * Only the Game Master (GM) of the campaign can update it.
	 *
	 * @param  \App\Models\User  $user The currently authenticated user.
	 * @param  \App\Models\Campaign  $campaign The campaign to be updated.
	 * @return bool True if the user can update the campaign, false otherwise.
	 */
	public function update(User $user, Campaign $campaign): bool
	{
		// Only the user who is the GM of this campaign can update it.
		return $campaign->gm_user_id === $user->id;
	}

	/**
	 * Determine whether the user can delete the specified campaign.
	 *
	 * Only the Game Master (GM) of the campaign can delete it.
	 *
	 * @param  \App\Models\User  $user The currently authenticated user.
	 * @param  \App\Models\Campaign  $campaign The campaign to be deleted.
	 * @return bool True if the user can delete the campaign, false otherwise.
	 */
	public function delete(User $user, Campaign $campaign): bool
	{
		// Only the user who is the GM of this campaign can delete it.
		return $campaign->gm_user_id === $user->id;
	}

	/**
	 * Determine whether the user can add a character to the campaign.
	 * This is often used by Filament Relation Managers.
	 *
	 * Only the Game Master (GM) of the campaign can add characters.
	 *
	 * @param  \App\Models\User  $user The currently authenticated user.
	 * @param  \App\Models\Campaign  $campaign The campaign to which a character might be added.
	 * @return bool True if the user can add characters, false otherwise.
	 */
	public function addCharacter(User $user, Campaign $campaign): bool // Specific method for adding one character
	{
		return $campaign->gm_user_id === $user->id;
	}

	/**
	 * Determine whether the user can attach any character to the campaign.
	 * This is often used by Filament Relation Managers for attaching existing records.
	 *
	 * Only the Game Master (GM) of the campaign can attach characters.
	 *
	 * @param  \App\Models\User  $user The currently authenticated user.
	 * @param  \App\Models\Campaign  $campaign The campaign to which characters might be attached.
	 * @return bool True if the user can attach characters, false otherwise.
	 */
	public function attachAnyCharacter(User $user, Campaign $campaign): bool
	{
		return $campaign->gm_user_id === $user->id;
	}

	/**
	 * Determine whether the user can detach any character from the campaign.
	 * This is often used by Filament Relation Managers for detaching records.
	 *
	 * Only the Game Master (GM) of the campaign can detach characters.
	 *
	 * @param  \App\Models\User  $user The currently authenticated user.
	 * @param  \App\Models\Campaign  $campaign The campaign from which characters might be detached.
	 * @return bool True if the user can detach characters, false otherwise.
	 */
	public function detachAnyCharacter(User $user, Campaign $campaign): bool
	{
		return $campaign->gm_user_id === $user->id;
	}
}