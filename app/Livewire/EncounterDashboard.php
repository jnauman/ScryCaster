<?php

namespace App\Livewire;

use App\Models\Encounter;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use App\Models\Character;
use App\Models\MonsterInstance;

class EncounterDashboard extends Component
{
	public Encounter $encounter;
	public array $combatants = [];
	public ?string $imageUrl;
	public bool $sidebarCollapsed = false;

	/**
	 * Mounts the component and loads the initial encounter data.
	 */
	public function mount(Encounter $encounter): void
	{
		$this->encounter = $encounter;
		$this->loadCombatants();
		$this->imageUrl = $this->encounter->current_image
			? Storage::disk('public')->url($this->encounter->current_image)
			: '/images/placeholder.jpg';
	}

	/**
	 * This method rebuilds the combatants array and calculates their CSS classes.
	 * This is the core logic for highlighting the current turn.
	 */
	public function loadCombatants(): void
	{
		$this->encounter->loadMissing('playerCharacters', 'monsterInstances.monster');

		$currentTurn = $this->encounter->current_turn ?? 0;

		$playerCharacters = $this->encounter->playerCharacters()->orderBy('pivot_order', 'asc')->get()->map(function ($pc) use ($currentTurn) {
			$isCurrentTurn = $pc->pivot->order == $currentTurn;

			return [
				'id' => $pc->id,
				'type' => 'player',
				'name' => $pc->name,
				'ac' => $pc->ac,
				'order' => $pc->pivot->order,
				'original_model' => $pc,
				// Explicitly define the CSS classes here
				'css_classes' => $isCurrentTurn ? 'player-current-turn' : 'player-not-turn',
			];
		});

		$monsterInstances = $this->encounter->monsterInstances()->with('monster')->orderBy('order', 'asc')->get()->map(function ($mi) use ($currentTurn) {
			$isCurrentTurn = $mi->order == $currentTurn;

			return [
				'id' => $mi->id,
				'type' => 'monster_instance',
				'name' => $mi->monster->name,
				'ac' => $mi->monster->ac,
				'order' => $mi->order,
				'original_model' => $mi,
				// Explicitly define the CSS classes here
				'css_classes' => $isCurrentTurn ? 'monster-current-turn' : 'monster-not-turn',
			];
		});

		$this->combatants = $playerCharacters->merge($monsterInstances)->sortBy('order')->values()->all();
	}

	/**
	 * Defines the event listeners for the component.
	 */
	public function getListeners(): array
	{
		return [
			"echo:encounter.{$this->encounter->id},.EncounterImageUpdated" => 'updateImage',
			"echo:encounter.{$this->encounter->id},.TurnChanged" => 'handleTurnChange',
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
	 * Handles the 'TurnChanged' event, updating the state and reloading the combatants.
	 */
	public function handleTurnChange(array $payload): void
	{
		if ($this->encounter->id === $payload['encounterId']) {
			// 1. Update the component's state from the event payload.
			$this->encounter->current_turn = $payload['currentTurn'];
			$this->encounter->current_round = $payload['currentRound'];

			// 2. Reload the combatants list, which will recalculate the CSS classes.
			$this->loadCombatants();
		}
	}

	public function toggleSidebar(): void
	{
		$this->sidebarCollapsed = !$this->sidebarCollapsed;
	}

	/**
	 * Renders the component view. Livewire automatically handles re-rendering when properties change.
	 */
	public function render()
	{
		return view('livewire.encounter-dashboard');
	}
}