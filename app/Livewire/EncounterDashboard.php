<?php

namespace App\Livewire;

use App\Models\Encounter;
use Illuminate\Support\Facades\Storage; // Added for Storage facade
use Livewire\Component;

/**
 * Livewire component for displaying an interactive encounter dashboard.
 *
 * This component shows encounter details, character order, and the current encounter image.
 * It listens for real-time updates to the encounter image via Laravel Echo.
 */
class EncounterDashboard extends Component
{
	/** @var int The ID of the encounter being displayed. */
	public int $encounterId;

	/** @var Encounter|null The loaded Encounter model instance. */
	public ?Encounter $encounter;

	/** @var string|null The URL of the current image for the encounter. */
	public ?string $imageUrl;

	/** @var bool Controls the visibility of the sidebar. */
	public bool $sidebarCollapsed = false;

	/**
	 * Mounts the component and loads the initial encounter data.
	 *
	 * @param int $encounterId The ID of the encounter to load.
	 * @return void
	 */
	public function mount(int $encounterId): void
	{
		$this->encounterId = $encounterId;
		$this->loadEncounter();
	}

	/**
	 * Loads the encounter data from the database.
	 *
	 * Fetches the encounter with its characters, ordered by their turn order in the encounter.
	 * Sets the initial image URL for the encounter.
	 *
	 * @return void
	 */
	public function loadEncounter(): void
	{
		// Eager load characters, ensuring they are ordered by their 'order' in the pivot table.
		$this->encounter = Encounter::with(['characters' => function ($query) {
			$query->orderBy('encounter_character.order', 'asc');
		}])->find($this->encounterId);

		// Set initial image URL from storage, or use a default placeholder.
		$this->imageUrl = $this->encounter?->current_image
			? Storage::disk('public')->url($this->encounter->current_image)
			: '/images/placeholder.jpg'; // Default placeholder if no image is set
	}

	/**
	 * Defines the event listeners for this component.
	 *
	 * Includes a listener for Livewire's 'refresh' event and a Laravel Echo listener
	 * for real-time updates to the encounter image.
	 * The Echo listener is for the '.EncounterImageUpdated' event on a private channel
	 * specific to this encounter instance (e.g., 'private-encounter.123').
	 *
	 * @return array<string, string>
	 */
	public function getListeners(): array
	{
		return [
			'refresh' => 'loadEncounter', // Standard Livewire event listener
			// Echo listener for real-time image updates.
			// Listens on a private channel: "private-encounter.{encounterId}"
			// For an event named: "EncounterImageUpdated" (prefixed with a dot if not using broadcastAs)
			"echo-private:encounter.{$this->encounterId},.EncounterImageUpdated" => 'updateImage',
		];
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
		// Update the public property, Livewire will automatically re-render relevant parts.
		$this->imageUrl = $payload['imageUrl'];
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