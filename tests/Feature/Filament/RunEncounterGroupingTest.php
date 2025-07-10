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
        $this->assertEquals('Group: Group A (Goblin 1 (A), Goblin 2 (A))', $initiativeInputs['group_Group A']['name']);
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
}
