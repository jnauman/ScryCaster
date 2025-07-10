<?php

namespace Database\Factories;

use App\Models\Encounter;
use App\Models\Campaign;
use Illuminate\Database\Eloquent\Factories\Factory;

class EncounterFactory extends Factory
{
    protected $model = Encounter::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->words(2, true) . ' Encounter',
            'campaign_id' => Campaign::factory(),
            'current_round' => 0,
            'current_turn' => 0,
            // Add other fields as necessary, e.g., torch timer fields if they have defaults
            'torch_timer_duration' => null,
            'torch_timer_remaining' => null,
            'torch_timer_is_running' => false,
        ];
    }
}
