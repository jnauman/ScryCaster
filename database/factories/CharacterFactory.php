<?php

namespace Database\Factories;

use App\Models\Character;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CharacterFactory extends Factory
{
    protected $model = Character::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name,
            'ac' => $this->faker->numberBetween(10, 20),
            'strength' => $this->faker->numberBetween(8, 18),
            'dexterity' => $this->faker->numberBetween(8, 18),
            'constitution' => $this->faker->numberBetween(8, 18),
            'intelligence' => $this->faker->numberBetween(8, 18),
            'wisdom' => $this->faker->numberBetween(8, 18),
            'charisma' => $this->faker->numberBetween(8, 18),
            'max_health' => $this->faker->numberBetween(10, 100),
            'user_id' => User::factory(),
            'data' => json_encode([]),
            'class' => $this->faker->randomElement(['Fighter', 'Wizard', 'Rogue', 'Cleric']),
            'ancestry' => $this->faker->randomElement(['Human', 'Elf', 'Dwarf', 'Halfling']),
            'title' => $this->faker->optional()->jobTitle,
            'image' => null,
        ];
    }
}
