<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\EncounterResource\Pages\RunEncounter;
use App\Models\Campaign;
use App\Models\Encounter;
use App\Models\Monster;
use App\Models\MonsterInstance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class RunEncounterGroupingTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Campaign $campaign;
    protected Encounter $encounter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
        $this->campaign = Campaign::factory()->create(['gm_user_id' => $this->user->id]);
        $this->encounter = Encounter::factory()->create(['campaign_id' => $this->campaign->id]);
    }

    public function test_prepare_initiative_inputs_creates_single_entry_for_grouped_monsters()
    {
        $monsterType1 = Monster::factory()->create(['user_id' => $this->user->id, 'dexterity' => 10]);
        MonsterInstance::factory()->create([
            'encounter_id' => $this->encounter->id,
            'monster_id' => $monsterType1->id,
            'initiative_group' => 'Group A',
            'display_name' => 'Goblin 1 (A)',
        ]);
        MonsterInstance::factory()->create([
            'encounter_id' => $this->encounter->id,
            'monster_id' => $monsterType1->id,
            'initiative_group' => 'Group A',
            'display_name' => 'Goblin 2 (A)',
        ]);
        $monsterType2 = Monster::factory()->create(['user_id' => $this->user->id, 'dexterity' => 12]);
        MonsterInstance::factory()->create([
            'encounter_id' => $this->encounter->id,
            'monster_id' => $monsterType2->id,
            'display_name' => 'Bugbear (Ungrouped)',
        ]);

        $livewireComponent = Livewire::test(RunEncounter::class, ['record' => $this->encounter->getRouteKey()])
            ->call('prepareInitiativeInputs');

        $initiativeInputs = $livewireComponent->get('initiativeInputs');

        // Assert that there is one entry for 'Group A'
        $this->assertArrayHasKey('group_Group A', $initiativeInputs);
        // Name assertion will depend on sorting, adjust if specific sorting is implemented for the string
        // For now, check if it contains the expected parts
        $this->assertStringContainsString('Group: Group A', $initiativeInputs['group_Group A']['name']);
        $this->assertStringContainsString('Goblin 1 (A)', $initiativeInputs['group_Group A']['name']);
        $this->assertStringContainsString('Goblin 2 (A)', $initiativeInputs['group_Group A']['name']);
        $this->assertEquals('monster_group', $initiativeInputs['group_Group A']['type']);
        $this->assertCount(2, $initiativeInputs['group_Group A']['member_ids']);

        // Assert that the ungrouped monster has its own entry
        $ungroupedMonsterKey = null;
        foreach ($initiativeInputs as $key => $input) {
            if ($input['type'] === 'monster_instance' && $input['name'] === 'Bugbear (Ungrouped)') {
                $ungroupedMonsterKey = $key;
                break;
            }
        }
        $this->assertNotNull($ungroupedMonsterKey, "Ungrouped monster 'Bugbear (Ungrouped)' not found in initiative inputs.");
        $this->assertEquals('monster_instance', $initiativeInputs[$ungroupedMonsterKey]['type']);
    }

    public function test_save_initiatives_and_start_encounter_applies_group_initiative()
    {
        $monsterType1 = Monster::factory()->create(['user_id' => $this->user->id, 'dexterity' => 10]);
        $mi1 = MonsterInstance::factory()->create([
            'encounter_id' => $this->encounter->id,
            'monster_id' => $monsterType1->id,
            'initiative_group' => 'Group Alpha',
        ]);
        $mi2 = MonsterInstance::factory()->create([
            'encounter_id' => $this->encounter->id,
            'monster_id' => $monsterType1->id,
            'initiative_group' => 'Group Alpha',
        ]);
        $mi3 = MonsterInstance::factory()->create([ // Ungrouped
            'encounter_id' => $this->encounter->id,
            'monster_id' => $monsterType1->id,
        ]);

        Livewire::test(RunEncounter::class, ['record' => $this->encounter->getRouteKey()])
            ->call('prepareInitiativeInputs') // Populate $initiativeInputs structure
            ->set('initiativeInputs.group_Group Alpha.initiative', 15)
            ->set('initiativeInputs.monster_' . $mi3->id . '.initiative', 10)
            ->call('saveInitiativesAndStartEncounter');

        $this->encounter->refresh();
        $this->assertEquals(15, $mi1->refresh()->initiative_roll);
        $this->assertEquals(15, $mi2->refresh()->initiative_roll);
        $this->assertEquals(10, $mi3->refresh()->initiative_roll);

        $this->assertEquals(1, $this->encounter->current_turn);
        $this->assertEquals(1, $this->encounter->current_round);
    }

    public function test_combatants_for_view_includes_initiative_group()
    {
        $monster = Monster::factory()->create();
        MonsterInstance::factory()->create([
            'encounter_id' => $this->encounter->id,
            'monster_id' => $monster->id,
            'initiative_group' => 'Test Group',
        ]);

        $livewireComponent = Livewire::test(RunEncounter::class, ['record' => $this->encounter->getRouteKey()]);
        $livewireComponent->call('loadCombatantsForView'); // Ensure it's loaded

        $combatantsForView = $livewireComponent->get('combatantsForView');
        $this->assertNotEmpty($combatantsForView);
        $foundMonster = false;
        foreach ($combatantsForView as $combatant) {
            if ($combatant['type'] === 'monster_instance' && $combatant['original_model']->monster_id === $monster->id) {
                $this->assertEquals('Test Group', $combatant['initiative_group']);
                $foundMonster = true;
                break;
            }
        }
        $this->assertTrue($foundMonster, 'Monster instance not found in combatantsForView.');
    }

    public function test_next_turn_skips_grouped_monsters()
    {
        // Monster Group (Order 1, 2)
        $groupMonster = Monster::factory()->create(['dexterity' => 12]);
        $gm1 = MonsterInstance::factory()->create([
            'encounter_id' => $this->encounter->id, 'monster_id' => $groupMonster->id,
            'initiative_group' => 'Grouped', 'initiative_roll' => 20, 'display_name' => 'GM1'
        ]);
        $gm2 = MonsterInstance::factory()->create([
            'encounter_id' => $this->encounter->id, 'monster_id' => $groupMonster->id,
            'initiative_group' => 'Grouped', 'initiative_roll' => 20, 'display_name' => 'GM2'
        ]);

        // Individual Monster (Order 3)
        $individualMonster = MonsterInstance::factory()->create([
            'encounter_id' => $this->encounter->id, 'monster_id' => Monster::factory()->create(['dexterity' => 10])->id,
            'initiative_roll' => 15, 'display_name' => 'IM1'
        ]);

        // Setup encounter state
        $this->encounter->calculateOrder(); // This will set orders based on new logic

        // After calculateOrder, gm1 and gm2 should share an order, and individualMonster will have the next order.
        // Let's find out what those orders are.
        $gm1->refresh();
        $gm2->refresh();
        $individualMonster->refresh();

        $groupOrder = $gm1->order; // gm1 and gm2 will share this order
        $individualOrder = $individualMonster->order;

        // Assert that the setup is as expected (group comes before individual)
        $this->assertLessThan($individualOrder, $groupOrder, "Grouped monsters should have a lower (earlier) order number than the individual monster.");
        $this->assertEquals($gm1->order, $gm2->order, "Grouped monsters gm1 and gm2 should share the same order number.");

        // Start the encounter, current turn is the group's turn
        $this->encounter->update(['current_turn' => $groupOrder, 'current_round' => 1]);

        $livewire = Livewire::test(RunEncounter::class, ['record' => $this->encounter->getRouteKey()]);

        // It's the Grouped monsters' turn (e.g., order 1)
        $livewire->call('nextTurn');
        $this->encounter->refresh();

        // The turn should now be the individualMonster's turn (e.g., order 2)
        $this->assertEquals($individualOrder, $this->encounter->current_turn, "Next turn should be the individual monster's order.");
        $this->assertEquals(1, $this->encounter->current_round, "Round should still be 1.");

        // It's the individualMonster's turn
        $livewire->call('nextTurn');
        $this->encounter->refresh();

        // The turn should go back to the Grouped monsters' turn (e.g., order 1) and the round should increment
        $this->assertEquals($groupOrder, $this->encounter->current_turn, "Next turn should be the group's order again.");
        $this->assertEquals(2, $this->encounter->current_round, "Round should have incremented.");
    }
}
