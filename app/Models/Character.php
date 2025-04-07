<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Character extends Model
{
	use HasFactory;

	protected $fillable = [
		'name',
		'type',
		'ac',
		'strength',
		'dexterity',
		'constitution',
		'intelligence',
		'wisdom',
		'charisma',
		'max_health',
		'current_health',
		'user_id',
	];


	/**
	 * Get the user that owns the character.
	 * Use the fully qualified namespace for the return type hint.
	 */
	public function user(): BelongsTo
	{
		return $this->belongsTo(User::class);
	}

	/**
	 * Get the encounters this character is in. (Existing)
	 */
	public function encounters(): BelongsToMany
	{
		return $this->belongsToMany(Encounter::class, 'encounter_character')->withPivot('initiative_roll', 'order');
	}

	/**
	 * Get the campaigns this character participates in.
	 */
	public function campaigns(): BelongsToMany
	{
		// Use the correct pivot table name: 'campaign_character'
		return $this->belongsToMany(Campaign::class, 'campaign_character');
	}

}