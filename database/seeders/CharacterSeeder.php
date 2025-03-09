<?php

namespace Database\Seeders;

use App\Models\Character;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CharacterSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 */
	public function run(): void
	{
		$monsters = [
			[
				'name' => 'Red',
				'type' => 'monster',
				'ac' => 12,
				'max_health' => 20,
				'current_health' => 20,
			],
			[
				'name' => 'Orange',
				'type' => 'monster',
				'ac' => 13,
				'max_health' => 25,
				'current_health' => 25,
			],
			[
				'name' => 'Yellow',
				'type' => 'monster',
				'ac' => 14,
				'max_health' => 30,
				'current_health' => 30,
			],
			[
				'name' => 'Green',
				'type' => 'monster',
				'ac' => 15,
				'max_health' => 35,
				'current_health' => 35,
			],
			[
				'name' => 'Blue',
				'type' => 'monster',
				'ac' => 16,
				'max_health' => 40,
				'current_health' => 40,
			],
			[
				'name' => 'Indigo',
				'type' => 'monster',
				'ac' => 17,
				'max_health' => 45,
				'current_health' => 45,
			],
			[
				'name' => 'Violet',
				'type' => 'monster',
				'ac' => 18,
				'max_health' => 50,
				'current_health' => 50,
			],
		];

		foreach ($monsters as $monster) {
			Character::create($monster);
		}
	}
}