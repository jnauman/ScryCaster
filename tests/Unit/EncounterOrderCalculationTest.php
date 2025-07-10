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

        // Expected order with shared group order:
        // 1. Player (Init 20, Dex 14) -> Order 1
        // 2. HighDexIndividualMonster (Init 15, Dex 16) -> Order 2
        // 3. Wolves Group (Effective Init 15, Effective Dex 12 from groupMonster2) -> Order 3
        //    - groupMonster2 (Init 15, Dex 12)
        //    - groupMonster1 (Init 15, Dex 10)
        // 4. IndividualMonster (Init 10, Dex 10) -> Order 4

        $this->encounter->calculateOrder(); // This is what we are testing

        // Refresh models to get updated 'order' attributes
        $playerChar->refresh();
        $groupMonster1->refresh();
        $groupMonster2->refresh();
        $individualMonster->refresh();
        $highDexIndividualMonster->refresh();

        // Verify 'order' attribute explicitly
        $this->assertEquals(1, $this->encounter->playerCharacters()->find($playerChar->id)->pivot->order, "PlayerChar order incorrect");
        $this->assertEquals(2, $highDexIndividualMonster->order, "HighDexIndividualMonster order incorrect");

        // Both monsters in the 'Wolves' group should now have the same order
        $this->assertEquals(3, $groupMonster1->order, "GroupMonster1 (Wolf) order incorrect");
        $this->assertEquals(3, $groupMonster2->order, "GroupMonster2 (Wolf) order incorrect - should share order with GroupMonster1");

        $this->assertEquals(4, $individualMonster->order, "IndividualMonster order incorrect");

        // Additionally, verify the sequence from getCombatants()
        // getCombatants() sorts by the 'order' attribute (or pivot_order for players)
        $sortedCombatants = $this->encounter->getCombatants()->all(); // Convert to array

        // Due to shared order numbers, the internal sorting of items with the SAME order number
        // might depend on other factors (like original array order or secondary sort criteria in getCombatants if any).
        // We primarily care that they are grouped correctly by order number.

        // Check Player
        $this->assertInstanceOf(Character::class, $sortedCombatants[0]);
        $this->assertEquals($playerChar->id, $sortedCombatants[0]->id);
        $this->assertEquals(1, $sortedCombatants[0]->pivot->order);

        // Check HighDexIndividualMonster
        $this->assertInstanceOf(MonsterInstance::class, $sortedCombatants[1]);
        $this->assertEquals($highDexIndividualMonster->id, $sortedCombatants[1]->id);
        $this->assertEquals(2, $sortedCombatants[1]->order);

        // Check Wolves Group (Order 3)
        // The order of groupMonster1 and groupMonster2 within the combatant list at order 3
        // might vary if getCombatants() doesn't have a secondary sort for monsters with the same order.
        // We'll check that both are present and have order 3.
        $wolvesInOrder3 = collect([$sortedCombatants[2], $sortedCombatants[3]])
            ->filter(fn($c) => $c instanceof MonsterInstance && ($c->id === $groupMonster1->id || $c->id === $groupMonster2->id))
            ->filter(fn($c) => $c->order === 3);

        $this->assertCount(2, $wolvesInOrder3, "Both wolves should be in order 3.");
        $this->assertTrue($wolvesInOrder3->contains(fn($c) => $c->id === $groupMonster1->id));
        $this->assertTrue($wolvesInOrder3->contains(fn($c) => $c->id === $groupMonster2->id));

        // Check IndividualMonster (Order 4)
        $this->assertInstanceOf(MonsterInstance::class, $sortedCombatants[4]);
        $this->assertEquals($individualMonster->id, $sortedCombatants[4]->id);
        $this->assertEquals(4, $sortedCombatants[4]->order);
    }
}
