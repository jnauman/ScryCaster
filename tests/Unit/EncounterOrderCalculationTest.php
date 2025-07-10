<?php

namespace Tests\Unit;

use App\Models\Campaign;
use App\Models\Character;
use App\Models\Encounter;
use App\Models\Monster;
use App\Models\MonsterInstance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EncounterOrderCalculationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Campaign $campaign;
    protected Encounter $encounter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->campaign = Campaign::factory()->create(['gm_user_id' => $this->user->id]);
        $this->encounter = Encounter::factory()->create(['campaign_id' => $this->campaign->id]);
    }

    public function test_calculate_order_correctly_sorts_grouped_and_individual_monsters_and_players()
    {
        $playerChar = Character::factory()->create(['user_id' => $this->user->id, 'dexterity' => 14]);
        $this->encounter->playerCharacters()->attach($playerChar->id, ['initiative_roll' => 20]);

        $monsterType = Monster::factory()->create(['user_id' => $this->user->id, 'dexterity' => 10]);
        $groupMonster1 = MonsterInstance::factory()->create([
            'encounter_id' => $this->encounter->id,
            'monster_id' => $monsterType->id,
            'initiative_group' => 'Wolves',
            'initiative_roll' => 15, // Group initiative
        ]);
        $groupMonster2 = MonsterInstance::factory()->create([ // Higher dexterity for tie-breaking within group
            'encounter_id' => $this->encounter->id,
            'monster_id' => Monster::factory()->create(['user_id' => $this->user->id, 'dexterity' => 12])->id,
            'initiative_group' => 'Wolves',
            'initiative_roll' => 15, // Group initiative
        ]);
        $individualMonster = MonsterInstance::factory()->create([ // Lower initiative than group
            'encounter_id' => $this->encounter->id,
            'monster_id' => $monsterType->id,
            'initiative_roll' => 10,
        ]);
         $highDexIndividualMonster = MonsterInstance::factory()->create([ // Same initiative as group, but higher dex than player
            'encounter_id' => $this->encounter->id,
            'monster_id' => Monster::factory()->create(['user_id' => $this->user->id, 'dexterity' => 16])->id,
            'initiative_roll' => 15,
        ]);


        $this->encounter->calculateOrder();

        // Expected order:
        // 1. Player (Init 20)
        // 2. HighDexIndividualMonster (Init 15, Dex 16) - Wins tie-break against Wolves group due to higher dex
        // 3. Wolf 2 (Group Init 15, Dex 12) - Within group, higher dex goes first
        // 4. Wolf 1 (Group Init 15, Dex 10)
        // 5. IndividualMonster (Init 10)

        $sortedCombatants = $this->encounter->getCombatants(); // This helper gets them already sorted by 'order'

        $this->assertInstanceOf(Character::class, $sortedCombatants[0]);
        $this->assertEquals($playerChar->id, $sortedCombatants[0]->id);

        $this->assertInstanceOf(MonsterInstance::class, $sortedCombatants[1]);
        $this->assertEquals($highDexIndividualMonster->id, $sortedCombatants[1]->id);

        $this->assertInstanceOf(MonsterInstance::class, $sortedCombatants[2]);
        $this->assertEquals($groupMonster2->id, $sortedCombatants[2]->id); // Wolf with Dex 12

        $this->assertInstanceOf(MonsterInstance::class, $sortedCombatants[3]);
        $this->assertEquals($groupMonster1->id, $sortedCombatants[3]->id); // Wolf with Dex 10

        $this->assertInstanceOf(MonsterInstance::class, $sortedCombatants[4]);
        $this->assertEquals($individualMonster->id, $sortedCombatants[4]->id);

        // Verify 'order' attribute explicitly
        $this->assertEquals(1, $this->encounter->playerCharacters()->find($playerChar->id)->pivot->order);
        $this->assertEquals(2, $highDexIndividualMonster->refresh()->order);
        $this->assertEquals(3, $groupMonster2->refresh()->order);
        $this->assertEquals(4, $groupMonster1->refresh()->order);
        $this->assertEquals(5, $individualMonster->refresh()->order);
    }
}
