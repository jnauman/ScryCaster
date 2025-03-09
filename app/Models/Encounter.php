<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Encounter extends Model
{
	use HasFactory;

	protected $fillable = [
		'name',
		'round_count',
	];

	public function characters()
	{
		return $this->belongsToMany(Character::class, 'encounter_character')->withPivot('initiative_roll', 'current_turn');
	}
}