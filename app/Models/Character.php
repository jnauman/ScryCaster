<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Represents a character in the application.
 *
 * Characters can be players or monsters and participate in encounters and campaigns.
 * Player characters are typically associated with a User.
 */
class Character extends Model
{
	use HasFactory; // Trait for model factories

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array<int, string>
	 */
	protected $fillable = [
		'name',
		// 'type' is removed as characters are now always players. Monsters are handled by Monster model.
		'ac',            // Armor Class: defensive capability
		'strength',      // Physical power
		'dexterity',     // Agility and reflexes
		'constitution',  // Health and endurance
		'intelligence',  // Reasoning and memory
		'wisdom',        // Perception and insight
		'charisma',      // Influence and leadership
		'max_health',    // Maximum health points
		'user_id',       // Foreign key for the User who owns this character (nullable for monsters)
		'data',          // JSON field for additional character data
		'class',
		'ancestry',
		'title',
		'image',
	];


	/**
	 * Defines the relationship to the User who owns this character (if it's a player character).
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function user(): BelongsTo
	{
		// A Character (typically a player) belongs to a User.
		return $this->belongsTo(User::class);
	}

	/**
	 * Defines the many-to-many relationship with encounters this character is part of.
	 *
	 * This uses the 'encounter_character' pivot table and includes pivot data
	 * like 'initiative_roll' (character's roll for turn order) and 'order' (calculated turn order).
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
	 */
	public function encounters(): BelongsToMany
	{
		// A Character can be in many Encounters, and an Encounter can have many Characters.
		// 'encounter_character' is the pivot table.
		// 'withPivot' specifies additional columns on the pivot table to retrieve.
		return $this->belongsToMany(Encounter::class, 'encounter_character')
					->withPivot('initiative_roll', 'order');
	}

	/**
	 * Defines the many-to-many relationship with campaigns this character participates in.
	 *
	 * This uses the 'campaign_character' pivot table.
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
	 */
	public function campaigns(): BelongsToMany
	{
		// A Character can participate in many Campaigns, and a Campaign can have many Characters.
		// 'campaign_character' is the pivot table.
		return $this->belongsToMany(Campaign::class, 'campaign_character');
	}

	/**
	 * Generates CSS classes for displaying this character in an encounter list.
	 *
	 * The classes vary based on whether the character is a player or monster,
	 * and whether it's their current turn in the encounter.
	 * This method assumes the character is part of an encounter and has 'type' and pivot data ('order') available.
	 *
	 * @param int $currentEncounterTurn The order number of the character whose turn it currently is.
	 * @return string A string of CSS classes, e.g., "player-current-turn" or "monster-not-turn".
	 */
	public function getListItemCssClasses(int $currentEncounterTurn): string
	{
		$baseType = 'player'; // Type is now fixed for Character model
		// Check if pivot data exists and if the character's order matches the current turn.
		$isCurrentTurn = ($this->pivot && isset($this->pivot->order) && $this->pivot->order == $currentEncounterTurn);

		if ($isCurrentTurn) {
			return "{$baseType}-current-turn";
		} else {
			return "{$baseType}-not-turn";
		}
	}
}