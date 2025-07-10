<?php

namespace Tests\Feature\Livewire;

use App\Livewire\EncounterDashboard;
use App\Models\Campaign;
use App\Models\Encounter;
use App\Models\Monster;
use App\Models\MonsterInstance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class EncounterDashboardDisplayNameTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Campaign $campaign;
    protected Encounter $encounter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        // No actingAs($this->user) here as EncounterDashboard is a public/player view
        $this->campaign = Campaign::factory()->create(['gm_user_id' => $this->user->id]);
        $this->encounter = Encounter::factory()->create(['campaign_id' => $this->campaign->id]);
    }

    public function test_monster_instance_uses_display_name_when_set()
    {
        $monster = Monster::factory()->create(['name' => 'Goblin Archmage']);
        MonsterInstance::factory()->create([
            'encounter_id' => $this->encounter->id,
            'monster_id' => $monster->id,
            'display_name' => 'Mysterious Hooded Figure',
            'order' => 1,
        ]);

        $component = Livewire::test(EncounterDashboard::class, ['encounter' => $this->encounter]);

        $combatants = $component->get('combatants');
        $this->assertCount(1, $combatants);
        $this->assertEquals('Mysterious Hooded Figure', $combatants[0]['name']);
    }

    public function test_monster_instance_uses_monster_name_when_display_name_is_null()
    {
        $monster = Monster::factory()->create(['name' => 'Goblin Sneak']);
        MonsterInstance::factory()->create([
            'encounter_id' => $this->encounter->id,
            'monster_id' => $monster->id,
            'display_name' => null,
            'order' => 1,
        ]);

        $component = Livewire::test(EncounterDashboard::class, ['encounter' => $this->encounter]);
        $combatants = $component->get('combatants');
        $this->assertCount(1, $combatants);
        $this->assertEquals('Goblin Sneak', $combatants[0]['name']);
    }

    public function test_monster_instance_uses_monster_name_when_display_name_is_empty_string()
    {
        $monster = Monster::factory()->create(['name' => 'Orc Warrior']);
        MonsterInstance::factory()->create([
            'encounter_id' => $this->encounter->id,
            'monster_id' => $monster->id,
            'display_name' => '', // Empty string
            'order' => 1,
        ]);

        $component = Livewire::test(EncounterDashboard::class, ['encounter' => $this->encounter]);
        $combatants = $component->get('combatants');
        $this->assertCount(1, $combatants);
        $this->assertEquals('Orc Warrior', $combatants[0]['name']);
    }

    public function test_monster_instance_data_includes_initiative_group_for_player_view()
    {
        $monster = Monster::factory()->create();
        MonsterInstance::factory()->create([
            'encounter_id' => $this->encounter->id,
            'monster_id' => $monster->id,
            'initiative_group' => 'Wolf Pack',
            'order' => 1,
        ]);

        $component = Livewire::test(EncounterDashboard::class, ['encounter' => $this->encounter]);
        $combatants = $component->get('combatants');

        $this->assertCount(1, $combatants);
        $this->assertArrayHasKey('initiative_group', $combatants[0]);
        $this->assertEquals('Wolf Pack', $combatants[0]['initiative_group']);
    }

    public function test_monster_instance_data_has_null_initiative_group_if_not_set()
    {
        $monster = Monster::factory()->create();
        MonsterInstance::factory()->create([
            'encounter_id' => $this->encounter->id,
            'monster_id' => $monster->id,
            'initiative_group' => null, // Explicitly null
            'order' => 1,
        ]);

        $component = Livewire::test(EncounterDashboard::class, ['encounter' => $this->encounter]);
        $combatants = $component->get('combatants');

        $this->assertCount(1, $combatants);
        $this->assertArrayHasKey('initiative_group', $combatants[0]);
        $this->assertNull($combatants[0]['initiative_group']);
    }
}
