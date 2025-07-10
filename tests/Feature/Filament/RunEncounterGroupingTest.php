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
        $this->encounter->calculateOrder(); // This will set orders: GM1 (1), GM2 (2), IM1 (3) due to implicit sorting if dex is same, or explicit if different
        // Manually ensure distinct orders for testing if calculateOrder is too complex for this specific test setup
        $gm1->update(['order' => 1]);
        $gm2->update(['order' => 2]); // Same group, so they act together
        $individualMonster->update(['order' => 3]);

        $this->encounter->update(['current_turn' => 1, 'current_round' => 1]); // Start with GM1

        $livewire = Livewire::test(RunEncounter::class, ['record' => $this->encounter->getRouteKey()]);

        // GM1's turn (order 1), belongs to 'Grouped'
        $livewire->call('nextTurn');
        // Should skip GM2 (order 2, also 'Grouped') and go to IM1 (order 3)
        $this->assertEquals(3, $this->encounter->refresh()->current_turn);
        $this->assertEquals(1, $this->encounter->current_round);

        // IM1's turn (order 3)
        $livewire->call('nextTurn');
        // Should go to GM1 (order 1) of next round
        $this->assertEquals(1, $this->encounter->refresh()->current_turn);
        $this->assertEquals(2, $this->encounter->current_round);
    }
}
