<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\MonsterInstance; // Added for HasMany relationship
// use Illuminate\Support\Collection; // Removed as it was unused

/**
 * Represents an encounter in the application.
 *
 * An encounter is a specific scene or event within a campaign, typically involving combat
 * or interaction between characters. It tracks turn order and round progression.
 */
class Encounter extends Model
{
	use HasFactory; // Trait for model factories

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array<int, string>
	 */
	protected $fillable = [
		'name',           // Name of the encounter
		'current_round',  // The current round number in the encounter
		'current_turn',   // The order number of the character whose turn it currently is
		'campaign_id',    // Foreign key linking to the Campaign this encounter belongs to
		// 'current_image', // Path to the current image displayed for the encounter - REMOVED
		'selected_campaign_image_id', // Foreign key to the selected campaign image
	];

	/**
	 * Calculates and assigns the turn order for characters in this encounter.
	 *
	 * The order is determined primarily by initiative rolls (descending).
	 * Ties in initiative are broken by dexterity scores (descending).
	 * The calculated order is saved to the 'order' field in the 'encounter_character' pivot table.
	 *
	 * @return void
	 */
public function calculateOrder(): void
{
    $combatants = collect();

    // Add player characters
    // Ensure pivot data is loaded if not already implicitly handled by ->get() on a BelongsToMany relationship
    $this->playerCharacters()->get()->each(function ($pc) use ($combatants) {
        $combatants->push([
            'id' => $pc->id,
            'type' => 'player',
            'initiative_roll' => $pc->pivot->initiative_roll ?? 0, // initiative_roll from pivot
            'dexterity_for_tiebreak' => $pc->dexterity ?? 10,     // dexterity from Character model
            'original_model' => $pc
        ]);
    });

    // Add monster instances
    $this->monsterInstances()->with('monster')->get()->each(function ($mi) use ($combatants) { // Eager load monster for dexterity
        $combatants->push([
            'id' => $mi->id,
            'type' => 'monster_instance',
            'initiative_roll' => $mi->initiative_roll ?? 0,          // initiative_roll from MonsterInstance model
            'dexterity_for_tiebreak' => $mi->monster->dexterity ?? 10, // dexterity from related Monster model
            'original_model' => $mi
        ]);
    });

    // Sort the combatants
    // Primary sort by initiative_roll (desc), secondary by dexterity_for_tiebreak (desc)
    $sortedCombatants = $combatants->sortByDesc(function ($combatant) {
        return sprintf('%03d-%03d', $combatant['initiative_roll'], $combatant['dexterity_for_tiebreak']);
    })->values(); // values() re-indexes the collection


    // Update order
    $orderIndex = 1;
    foreach ($sortedCombatants as $combatantData) {
        if ($combatantData['type'] === 'player') {
            // For BelongsToMany, updateExistingPivot is used.
            $this->playerCharacters()->updateExistingPivot($combatantData['id'], ['order' => $orderIndex]);
        } elseif ($combatantData['type'] === 'monster_instance') {
            // For HasMany, update the model instance directly.
            $monsterInstance = $combatantData['original_model'];
            $monsterInstance->update(['order' => $orderIndex]);
        }
        $orderIndex++;
    }
}

	/**
     * Defines the many-to-many relationship with player characters participating in this encounter.
     * Uses the 'encounter_character' pivot table.
     *
     * @return BelongsToMany
     */
    public function playerCharacters(): BelongsToMany
	{
		// An Encounter can have many Player Characters (via Character model),
		// and a Player Character can be in many Encounters.
		// 'encounter_character' is the pivot table.
		// 'withPivot' specifies additional columns on the pivot table to retrieve.
		return $this->belongsToMany(Character::class, 'encounter_character')
					->withPivot('initiative_roll', 'order');
	}

    /**
     * Defines the one-to-many relationship with monster instances in this encounter.
     */
    public function monsterInstances(): HasMany
    {
        return $this->hasMany(MonsterInstance::class);
    }

	public function getCombatants()
	{
		// Eager load the relationships to prevent N+1 query problems in the view
		$playerCharacters = $this->playerCharacters()->get();
		$monsterInstances = $this->monsterInstances()->with('monster')->get();

		// Merge the two collections into one
		$allCombatants = $playerCharacters->concat($monsterInstances);

		// Sort the combined collection by the 'order'
		// We need to use a custom sort function because the 'order' attribute
		// is in different places for players (pivot->order) and monsters (order).
		$sortedCombatants = $allCombatants->sortBy(function ($combatant) {
			// Check if the combatant is a Character model
			if ($combatant instanceof Character) {
				return $combatant->pivot->order;
			}
			// Otherwise, it's a MonsterInstance model
			return $combatant->order;
		});

		return $sortedCombatants;
	}

	/**
     * Defines the relationship to the Campaign this encounter belongs to.
     *
     * @return BelongsTo
     */
    public function campaign(): BelongsTo
	{
		// An Encounter belongs to one Campaign.
		return $this->belongsTo(Campaign::class);
	}

	/**
     * Defines the relationship to the selected CampaignImage for this encounter.
     *
     * @return BelongsTo
     */
    public function selectedCampaignImage(): BelongsTo
	{
		return $this->belongsTo(CampaignImage::class, 'selected_campaign_image_id');
	}
}