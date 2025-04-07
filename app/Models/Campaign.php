<?php // app/Models/Campaign.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Campaign extends Model
{
	use HasFactory;

	protected $fillable = [
		'name',
		'description',
		'gm_user_id',
		// join_code is handled by the creating event below
	];

	/**
	 * The "booted" method of the model.
	 * Automatically generate join_code on creation.
	 */
	protected static function booted(): void
	{
		static::creating(function (Campaign $campaign) {
			if (empty($campaign->join_code)) {
				$campaign->join_code = (string) Str::uuid(); // Generate a unique UUID
			}
		});
	}

	/**
	 * Get the GM (User) who owns the campaign.
	 */
	public function gm(): BelongsTo
	{
		return $this->belongsTo(User::class, 'gm_user_id');
	}

	/**
	 * Get the encounters belonging to this campaign.
	 */
	public function encounters(): HasMany
	{
		return $this->hasMany(Encounter::class);
	}

	/**
	 * Get the characters participating in this campaign.
	 */
	public function characters(): BelongsToMany
	{
		// Use the correct pivot table name we created: 'campaign_character'
		return $this->belongsToMany(Character::class, 'campaign_character');
	}
}