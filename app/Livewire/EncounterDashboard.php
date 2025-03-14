<?php

namespace App\Livewire;

use App\Models\Encounter;
use Livewire\Component;

class EncounterDashboard extends Component
{
	public $encounterId;
	public $encounter;

	protected $listeners = ['refresh' => 'loadEncounter'];

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
	}

	public function render()
	{
		return view('livewire.encounter-dashboard');
	}
}