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

        // Eager load the selectedCampaignImage relationship if not already loaded
        $this->encounter->loadMissing('selectedCampaignImage');

		if ($this->encounter->selectedCampaignImage && $this->encounter->selectedCampaignImage->image_path) {
			$this->imageUrl = $this->encounter->selectedCampaignImage->image_url; // Use the accessor
		} else {
			$this->imageUrl = '/images/monster_image.png';
		}
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
				'class' => $pc->class,
				'ancestry' => $pc->ancestry,
				'title' => $pc->title,
				// 'original_model' => $pc, // Removed as it might cause issues with Livewire state and isn't used in blade
				'image' => $pc->image ? Storage::disk('public')->url($pc->image) : '/images/torch_bearer.png',
				// Explicitly define the CSS classes here
				'css_classes' => $isCurrentTurn ? 'player-current-turn' : 'player-not-turn',
			];
		});

		$monsterInstances = $this->encounter->monsterInstances()->with('monster')->orderBy('order', 'asc')->get()->map(function ($mi) use ($currentTurn) {
			$isCurrentTurn = $mi->order == $currentTurn;

			return [
				'id' => $mi->id,
				'type' => 'monster_instance',
				'name' => $mi->display_name ?: $mi->monster->name, // Use display_name if available
				//'ac' => $mi->monster->ac,
				'order' => $mi->order,
				// 'original_model' => $mi, // Removed as it might cause issues with Livewire state and isn't used in blade
				'image' => $mi->monster->image ? Storage::disk('public')->url($mi->monster->image) : '/images/monster_image.png',
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

            // Refresh the encounter model instance to ensure relations are fresh
            $this->encounter->refresh();

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