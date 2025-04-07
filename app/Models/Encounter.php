<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Encounter extends Model
{
	use HasFactory;

	protected $fillable = [
		'name',
		'current_round',
		'current_turn',
		'campaign_id',
	];

	public function calculateOrder()
	{
		$characters = $this->characters->sortByDesc('pivot.initiative_roll')->values();
		$characters = $characters->groupBy('pivot.initiative_roll');

		$order = 1;
		foreach ($characters as $initiativeGroup) {
			$initiativeGroup = $initiativeGroup->sortByDesc(function ($character) {
				return $character->dexterity;
			});

			foreach ($initiativeGroup as $character) {
				$character->pivot->order = $order;
				$character->pivot->save();
				$order++;
			}
		}
	}
	public function characters()
	{
		return $this->belongsToMany(Character::class, 'encounter_character')->withPivot('initiative_roll', 'order');
	}

	/**
	 * Get the campaign this encounter belongs to.
	 */
	public function campaign(): BelongsTo
	{
		return $this->belongsTo(Campaign::class);
	}
}