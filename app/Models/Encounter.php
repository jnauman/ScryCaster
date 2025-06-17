<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\MonsterInstance; // Added for HasMany relationship
// use Illuminate\Support\Collection; // Removed as it was unused

/**
 * Represents an encounter in the application.
 *
 * An encounter is a specific scene or event within a campaign, typically involving combat
 * or interaction between characters. It tracks turn order and round progression.
 */
class Encounter extends Model
{
	use HasFactory; // Trait for model factories

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array<int, string>
	 */
	protected $fillable = [
		'name',           // Name of the encounter
		'current_round',  // The current round number in the encounter
		'current_turn',   // The order number of the character whose turn it currently is
		'campaign_id',    // Foreign key linking to the Campaign this encounter belongs to
		'current_image',  // Path to the current image displayed for the encounter
	];

	/**
	 * Calculates and assigns the turn order for characters in this encounter.
	 *
	 * The order is determined primarily by initiative rolls (descending).
	 * Ties in initiative are broken by dexterity scores (descending).
	 * The calculated order is saved to the 'order' field in the 'encounter_character' pivot table.
	 *
	 * @return void
	 */
	public function calculateOrder(): void
	{
		// TODO: This method needs to be refactored to handle both playerCharacters and monsterInstances.
		// The current logic is specific to the old characters() relationship.
		// For now, commenting out to prevent errors. A full redesign of initiative handling
		// across player characters and monster instances will be required.

		/*
		// Retrieve characters associated with this encounter, ordered by initiative roll (descending)
		$characters = $this->playerCharacters()->orderBy('pivot_initiative_roll', 'desc')->get();

		// Further group by initiative roll to handle ties
		$groupedByInitiative = $characters->groupBy('pivot.initiative_roll');

		$order = 1;
		foreach ($groupedByInitiative as $initiativeGroup) {
			// Sort characters within the same initiative group by dexterity (descending) to break ties
			$sortedGroup = $initiativeGroup->sortByDesc(function ($character) {
				return $character->dexterity; // Assumes Character model has a 'dexterity' attribute
			});

			foreach ($sortedGroup as $character) {
				// Update the 'order' in the pivot table for each character
				$this->playerCharacters()->updateExistingPivot($character->id, ['order' => $order]);
				$order++;
			}
		}
		*/
	}

	/**
	 * Defines the many-to-many relationship with player characters participating in this encounter.
	 * Uses the 'encounter_character' pivot table.
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
	 */
	public function playerCharacters(): BelongsToMany
	{
		// An Encounter can have many Player Characters (via Character model),
		// and a Player Character can be in many Encounters.
		// 'encounter_character' is the pivot table.
		// 'withPivot' specifies additional columns on the pivot table to retrieve.
		return $this->belongsToMany(Character::class, 'encounter_character')
					->withPivot('initiative_roll', 'order');
	}

    /**
     * Defines the one-to-many relationship with monster instances in this encounter.
     */
    public function monsterInstances(): HasMany
    {
        return $this->hasMany(MonsterInstance::class);
    }

	/**
	 * Defines the relationship to the Campaign this encounter belongs to.
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function campaign(): BelongsTo
	{
		// An Encounter belongs to one Campaign.
		return $this->belongsTo(Campaign::class);
	}
}