<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
		// Retrieve characters associated with this encounter, ordered by initiative roll (descending)
		$characters = $this->characters()->orderBy('pivot_initiative_roll', 'desc')->get();

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
				$this->characters()->updateExistingPivot($character->id, ['order' => $order]);
				$order++;
			}
		}
	}

	/**
	 * Defines the many-to-many relationship with characters participating in this encounter.
	 *
	 * This uses the 'encounter_character' pivot table and includes pivot data
	 * like 'initiative_roll' and 'order'.
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
	 */
	public function characters(): BelongsToMany
	{
		// An Encounter can have many Characters, and a Character can be in many Encounters.
		// 'encounter_character' is the pivot table.
		// 'withPivot' specifies additional columns on the pivot table to retrieve.
		return $this->belongsToMany(Character::class, 'encounter_character')
					->withPivot('initiative_roll', 'order');
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