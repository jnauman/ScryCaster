<?php

namespace App\Livewire;

use App\Models\Encounter;
use Livewire\Component;

class EncounterDashboard extends Component
{
	public $encounterId;
	public $encounter;
	public $imageUrl;
	public bool $sidebarCollapsed = false;

	//protected $listeners = ['refresh' => 'loadEncounter'];

	public function mount($encounterId)
	{
		$this->encounterId = $encounterId;
		$this->loadEncounter();
	}

	public function loadEncounter()
	{
		$this->encounter = Encounter::with(['characters' => function ($query) {
			$query->orderBy('encounter_character.order');
		}])->find($this->encounterId);

		// Set initial image URL
		$this->imageUrl = $this->encounter?->current_image
			? Storage::disk('public')->url($this->encounter->current_image)
			: '/images/placeholder.jpg'; // Default placeholder
	}

	/**
	 * Define Livewire listeners including Echo event listeners.
	 * Syntax: 'echo-private:channel-name,EventClassName' => 'methodName'
	 */
	public function getListeners(): array
	{
		return [
			'refresh' => 'loadEncounter',
			"echo:encounter,.EncounterImageUpdated" => 'updateImage',
			//"echo-private:encounter.{$this->encounterId},EncounterImageUpdated" => 'updateImage',
			// If you used broadcastAs() in the Event, use that name instead:
            //"echo-private:encounter.{$this->encounterId},.image.updated" => 'updateImage',

		];
	}

	// Method to handle the image update event
	public function updateImage(array $payload): void
	{
		$this->imageUrl = $payload['imageUrl'];
		// Optional: Force component re-render if needed, though updating public property usually suffices.
		//$this->dispatch('$refresh');
	}

	public function toggleSidebar()
	{
		$this->sidebarCollapsed = !$this->sidebarCollapsed;
	}

	public function render()
	{
		return view('livewire.encounter-dashboard');
	}
}