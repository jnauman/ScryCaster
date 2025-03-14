<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Character extends Model
{
	use HasFactory;

	protected $fillable = [
		'name',
		'type',
		'ac',
		'max_health',
		'current_health',
	];

	public function encounters()
	{
		return $this->belongsToMany(Encounter::class, 'encounter_character')->withPivot('initiative_roll', 'order');
	}

}