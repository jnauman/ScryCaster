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
        $this->encounter->loadMissing('playerCharacters', 'monsterInstances.monster'); // Eager load relationships

        $playerCharacters = $this->encounter->playerCharacters()->orderBy('pivot_order', 'asc')->get()->map(function ($pc) {
            return [
                'id' => $pc->id,
                'type' => 'player',
                'name' => $pc->name,
                'current_hp' => $pc->current_health,
                'max_hp' => $pc->max_health,
                'ac' => $pc->ac,
                'order' => $pc->pivot->order,
                'original_model' => $pc, // Keep original model for actions if needed
				'css_classes' => $pc->getListItemCssClasses($this->encounter->current_turn ?? 0),
            ];
        });

        $monsterInstances = $this->encounter->monsterInstances()->with('monster')->orderBy('order', 'asc')->get()->map(function ($mi) {
            // Assuming MonsterInstance will have getListItemCssClasses or similar logic
            // For now, basic class determination:
            $isCurrentTurn = (isset($mi->order) && $mi->order == ($this->encounter->current_turn ?? 0));
            $monsterCssBase = 'monster'; // or 'monster-instance'
            $monsterCss = $isCurrentTurn ? "{$monsterCssBase}-current-turn" : "{$monsterCssBase}-not-turn";

            return [
                'id' => $mi->id,
                'type' => 'monster_instance',
                'name' => $mi->monster->name, // Access name from related Monster model
                'current_hp' => $mi->current_health,
                'max_hp' => $mi->monster->max_health, // Access max_health from related Monster model
                'ac' => $mi->monster->ac,         // Access ac from related Monster model
                'order' => $mi->order,
                'original_model' => $mi,
				'css_classes' => $monsterCss, // Placeholder for MonsterInstance CSS logic
            ];
        });

        $this->combatants = $playerCharacters->merge($monsterInstances)->sortBy('order')->values()->all();
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