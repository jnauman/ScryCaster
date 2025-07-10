<?php

namespace Database\Factories;

use App\Models\MonsterInstance;
use App\Models\Monster;
use App\Models\Encounter;
use Illuminate\Database\Eloquent\Factories\Factory;

class MonsterInstanceFactory extends Factory
{
    protected $model = MonsterInstance::class;

    public function definition(): array
    {
        $monster = Monster::factory()->create(); // Ensure a monster exists
        return [
            'monster_id' => $monster->id,
            'encounter_id' => Encounter::factory(),
            'display_name' => null,
            'current_health' => $monster->max_health, // Default to max health
            'max_health' => $monster->max_health,
            'initiative_roll' => null,
            'initiative_group' => null,
            'order' => null,
        ];
    }

    /**
     * Configure the model factory.
     *
     * @return $this
     */
    public function configure(): static
    {
        return $this->afterCreating(function (MonsterInstance $instance) {
            // If max_health was not set or current_health is higher, adjust.
            // This ensures current_health is not greater than max_health if max_health is set via state.
            if (is_null($instance->max_health) || $instance->current_health > $instance->max_health) {
                $monster = $instance->monster; // reload monster if necessary, but factory already created one
                $instance->max_health = $monster->max_health;
                if ($instance->current_health > $instance->max_health) {
                    $instance->current_health = $instance->max_health;
                }
                $instance->save();
            }
        });
    }
}
