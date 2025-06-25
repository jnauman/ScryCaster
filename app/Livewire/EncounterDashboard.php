<?php

namespace App\Livewire;

use App\Models\Encounter;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage; // Added for Storage facade
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

	/**
	 * Defines the event listeners for this component.
	 *
	 * We are now listening on public channels. The syntax is "echo:channel-name,.EventName"
	 *
	 * @return array<string, string>
	 */
	public function getListeners(): array
	{
		return [
			'refresh' => 'loadCombatants',
			// Echo listener for real-time image updates.
			"echo:encounter.{$this->encounter->id},.EncounterImageUpdated" => 'updateImage',
			// Echo listener for real-time turn changes.
			"echo:encounter.{$this->encounter->id},.TurnChanged" => 'handleTurnChanged',
		];
	}

	/**
	 * Handles the 'EncounterImageUpdated' event.
	 */
	public function updateImage(array $payload): void
	{
		if ($this->encounter->id === $payload['encounterId']) {
			$this->imageUrl = $payload['imageUrl'];
		}
	}

	/**
	 * Handles the 'TurnChanged' event.
	 */
	public function handleTurnChange(array $payload): void
	{
		if ($this->encounter->id === $payload['encounterId']) {
			$this->encounter->current_turn = $payload['currentTurn'];
			$this->encounter->current_round = $payload['currentRound'];
			$this->loadCombatants();
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