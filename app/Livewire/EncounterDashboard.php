<?php

namespace App\Livewire;

use App\Models\Encounter;
use Illuminate\Support\Facades\Storage; // Added for Storage facade
use Illuminate\Support\Facades\Log; // Add if not already there
use Livewire\Component;

/**
 * Livewire component for displaying an interactive encounter dashboard.
 *
 * This component shows encounter details, character order, and the current encounter image.
 * It listens for real-time updates to the encounter image via Laravel Echo.
 */
use App\Models\Character; // Added for type hinting
use App\Models\MonsterInstance; // Added for type hinting

class EncounterDashboard extends Component
{
	/** @var Encounter The loaded Encounter model instance. */
	public Encounter $encounter;

    /** @var array Holds the merged and sorted list of player characters and monster instances. */
    public array $combatants = [];

	/** @var string|null The URL of the current image for the encounter. */
	public ?string $imageUrl;

	/** @var bool Controls the visibility of the sidebar. */
	public bool $sidebarCollapsed = false;

	/**
	 * Mounts the component and loads the initial encounter data.
	 *
	 * @param Encounter $encounter The Encounter model instance to display.
	 * @return void
	 */
	public function mount(Encounter $encounter): void
	{
		$this->encounter = $encounter;
    $this->encounter->refresh(); // Refresh to get the latest data
        $this->loadCombatants(); // Initial load of combatants
        $this->imageUrl = $this->encounter->current_image
            ? Storage::disk('public')->url($this->encounter->current_image)
            : '/images/placeholder.jpg'; // Default placeholder if no image is set
	}

public function loadCombatants(): void
{
    // Re-fetch the encounter with all necessary relationships
    $freshEncounter = Encounter::with([
        'playerCharacters' => function ($query) {
            $query->orderBy('encounter_character.order', 'asc'); // Correct pivot table column for ordering
        },
        'monsterInstances' => function ($query) {
            $query->orderBy('order', 'asc');
        },
        'monsterInstances.monster' // Eager load monster details for monster instances
    ])->find($this->encounter->id);

    if (!$freshEncounter) {
        $this->combatants = [];
        // Optionally, reset this->encounter or handle error appropriately
        // For example, redirect or show a specific error message.
        // Logging the error is a good first step.
        Log::warning('Encounter not found when trying to load combatants.', ['encounter_id' => $this->encounter->id]);
        // If you want to clear the main encounter if it's gone:
        // $this->encounter = null; // Or some default empty Encounter model
        return;
    }
    $this->encounter = $freshEncounter; // Update the component's encounter instance

    // Now use the eager-loaded relationships from $this->encounter
    $playerCharacters = $this->encounter->playerCharacters->map(function ($pc) {
        return [
            'id' => $pc->id,
            'type' => 'player',
            'name' => $pc->name,
            'current_hp' => $pc->current_health,
            'max_hp' => $pc->max_health,
            'ac' => $pc->ac,
            'order' => $pc->pivot->order, // Access pivot data
            'original_model' => $pc,
            'css_classes' => $pc->getListItemCssClasses($this->encounter->current_turn ?? 0),
        ];
    });

    $monsterInstances = $this->encounter->monsterInstances->map(function ($mi) {
        $isCurrentTurn = (isset($mi->order) && $mi->order == ($this->encounter->current_turn ?? 0));
        // Determine monster CSS classes (simplified example, adjust as needed)
        $monsterCssBase = 'monster';
        $monsterCss = $isCurrentTurn ? "{$monsterCssBase}-current-turn" : "{$monsterCssBase}-not-turn";
        if (!$mi->monster) { // Safety check if monster relation failed to load
            Log::error('Monster data missing for monster instance.', ['monster_instance_id' => $mi->id]);
            return null; // Skip this monster instance if essential data is missing
        }
        return [
            'id' => $mi->id,
            'type' => 'monster_instance',
            'name' => $mi->monster->name,
            'current_hp' => $mi->current_health,
            'max_hp' => $mi->monster->max_health,
            'ac' => $mi->monster->ac,
            'order' => $mi->order,
            'original_model' => $mi,
            'css_classes' => $monsterCss, // Placeholder for actual CSS logic
        ];
    })->filter(); // Filter out any nulls from failed monster loads

    $this->combatants = collect($playerCharacters)->merge($monsterInstances)->sortBy('order')->values()->all();
}

    public function updateCombatantHealth(int $combatantId, string $combatantType, string $newHpStr): void
    {
        $newHp = filter_var($newHpStr, FILTER_VALIDATE_INT);
        if ($newHp === false || $newHp < 0) $newHp = 0; // Default to 0 if invalid or negative

        if ($combatantType === 'player') {
            $character = Character::find($combatantId);
            if ($character) {
                $character->current_health = $newHp;
                $character->save();
            }
        } elseif ($combatantType === 'monster_instance') {
            $monsterInstance = MonsterInstance::find($combatantId);
            if ($monsterInstance) {
                $monsterInstance->current_health = $newHp;
                $monsterInstance->save();
            }
        }
        $this->loadCombatants(); // Refresh the combatants list
    }

	/**
	 * Defines the event listeners for this component.
	 *
	 * Includes a listener for Livewire's 'refresh' event and a Laravel Echo listener
	 * for real-time updates to the encounter image.
	 * The Echo listener is for the '.EncounterImageUpdated' event on a private channel
	 * specific to this encounter instance.
	 *
	 * @return array<string, string>
	 */
	public function getListeners(): array
	{
		return [
			'refresh' => 'loadCombatants', // Changed from loadEncounter
			// Echo listener for real-time image updates.
			// Listens on a private channel: "private-encounter.{encounterId}"
			// For an event named: "EncounterImageUpdated" (prefixed with a dot if not using broadcastAs)
			"echo-private:encounter.{$this->encounter->id},.EncounterImageUpdated" => 'updateImage',
            "echo:encounter,.TurnChanged" => "handleTurnChanged", // Added listener
		];
	}

    public function handleTurnChanged(array $payload): void
    {
        if ((int)$payload['encounterId'] === (int)$this->encounter->id) {
            $this->encounter->current_round = $payload['currentRound'];
            $this->encounter->current_turn = $payload['currentTurn'];
            // Optionally, persist these changes if the dashboard itself is meant to be a source of truth
            // $this->encounter->save();
            // However, typically the event source (Filament action) would have already persisted this.
            // Refreshing the model from DB might be safer if other attributes could change.
            // $this->encounter->refresh(); // Uncomment if other non-payload attributes might change.

            $this->loadCombatants();
        }
    }

	/**
	 * Handles the 'EncounterImageUpdated' event received via Echo.
	 *
	 * Updates the `imageUrl` property with the new image URL from the event payload.
	 *
	 * @param array $payload The event data. Expected to contain 'imageUrl'.
	 *                       Example: `['encounterId' => 123, 'imageUrl' => '/storage/images/new_image.png']`
	 * @return void
	 */
	public function updateImage(array $payload): void
	{
    if (isset($payload['imageUrl']) && is_string($payload['imageUrl'])) {
        // Optional: Check if the event is for the correct encounter,
        // though private channels usually handle this.
        if (isset($payload['encounterId']) && (int)$payload['encounterId'] === (int)$this->encounter->id) {
            $this->imageUrl = $payload['imageUrl'];
        } elseif (!isset($payload['encounterId'])) {
            // If encounterId is not in payload, update anyway assuming channel is specific enough
            $this->imageUrl = $payload['imageUrl'];
        }
        // If encounterId is set but doesn't match, do nothing or log.
        // else {
        //     Log::warning('Received EncounterImageUpdated event for wrong encounter ID.', [
        //         'expected_id' => $this->encounter->id,
        //         'received_id' => $payload['encounterId'] ?? null,
        //     ]);
        // }
    } else {
        Log::warning('EncounterImageUpdated event received with invalid payload.', ['payload' => $payload]);
    }
	}

	/**
	 * Toggles the collapsed state of the sidebar.
	 *
	 * @return void
	 */
	public function toggleSidebar(): void
	{
		$this->sidebarCollapsed = !$this->sidebarCollapsed;
	}

	/**
	 * Renders the component.
	 *
	 * @return \Illuminate\Contracts\View\View
	 */
	public function render()
	{
		// Returns the Blade view associated with this Livewire component.
		return view('livewire.encounter-dashboard');
	}
}