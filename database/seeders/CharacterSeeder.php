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
		// Character seeding logic can be added here if needed for player characters.
        // Ensure any seeded characters have a valid user_id.
        // Example:
        // \App\Models\User::factory()->create()->each(function ($user) {
        //     Character::factory()->count(2)->create(['user_id' => $user->id]);
        // });
	}
}