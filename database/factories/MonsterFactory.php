<?php

namespace Database\Factories;

use App\Models\Monster;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class MonsterFactory extends Factory
{
    protected $model = Monster::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->words(2, true) . ' Monster';
        return [
            'name' => $name,
            'slug' => \Illuminate\Support\Str::slug($name),
            'ac' => $this->faker->numberBetween(10, 25),
            'max_health' => $this->faker->numberBetween(10, 300),
            'strength' => $this->faker->numberBetween(3, 30),
            'dexterity' => $this->faker->numberBetween(3, 30),
            'constitution' => $this->faker->numberBetween(3, 30),
            'intelligence' => $this->faker->numberBetween(3, 30),
            'wisdom' => $this->faker->numberBetween(3, 30),
            'charisma' => $this->faker->numberBetween(3, 30),
            'user_id' => User::factory(), // Or null if monsters can be global
            'data' => json_encode([]), // Default empty JSON
            'movement' => $this->faker->randomElement(['30 ft', '25 ft', '40 ft', 'fly 60 ft']),
            'description' => $this->faker->paragraph,
            'traits' => [], // Default empty array, adjust if structure is different
            'attacks' => $this->faker->sentence, // Default simple string, adjust if structured
        ];
    }
}
