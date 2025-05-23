<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * Represents a campaign in the application.
 *
 * A campaign is a story or adventure run by a Game Master (GM) for a group of players.
 * It contains encounters and characters.
 */
class Campaign extends Model
{
	use HasFactory; // Trait for model factories

	/**
	 * The attributes that are mass assignable.
	 *
	 * 'join_code' is intentionally excluded as it's auto-generated.
	 *
	 * @var array<int, string>
	 */
	protected $fillable = [
		'name',
		'description',
		'gm_user_id', // The ID of the User who is the Game Master
		// join_code is handled by the creating event below
	];

	/**
	 * The "booted" method of the model.
	 * This method is called when the model is booting.
	 * It's used here to register a creating event listener.
	 */
	protected static function booted(): void
	{
		// Listen for the 'creating' event on the Campaign model.
		static::creating(function (Campaign $campaign) {
			// Automatically generate a unique join_code (UUID) if one isn't provided.
			// This ensures every campaign has a join_code upon creation.
			if (empty($campaign->join_code)) {
				$campaign->join_code = (string) Str::uuid(); // Generate a unique UUID
			}
		});
	}

	/**
	 * Defines the relationship to the User who is the Game Master (GM) of this campaign.
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function gm(): BelongsTo
	{
		// A Campaign belongs to a User (the GM), linked by 'gm_user_id'.
		return $this->belongsTo(User::class, 'gm_user_id');
	}

	/**
	 * Defines the relationship to the encounters that belong to this campaign.
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function encounters(): HasMany
	{
		// A Campaign can have many Encounters.
		return $this->hasMany(Encounter::class);
	}

	/**
	 * Defines the many-to-many relationship with characters participating in this campaign.
	 *
	 * This uses the 'campaign_character' pivot table.
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
	 */
	public function characters(): BelongsToMany
	{
		// A Campaign can have many Characters, and a Character can be in many Campaigns.
		// Uses the 'campaign_character' pivot table to manage this relationship.
		return $this->belongsToMany(Character::class, 'campaign_character');
	}
}