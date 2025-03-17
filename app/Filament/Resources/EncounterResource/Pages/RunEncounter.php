<?php

namespace App\Filament\Resources\EncounterResource\Pages;

use App\Filament\Resources\EncounterResource;
use Filament\Actions;
use Filament\Pages\Page;
use Filament\Resources\Pages\ViewRecord;
use App\Events\TurnChanged;
use Illuminate\Support\Facades\Log;

class RunEncounter extends ViewRecord
{
    protected static string $resource = EncounterResource::class;

	protected static string $view = 'filament.resources.encounter-resource.pages.run-encounter';


	public function nextTurn()
	{

		$characterCount = $this->record->characters->count();
		if ($this->record->current_turn < $characterCount) {
			$this->record->current_turn++;
		} else {
			$this->record->current_turn = 1;
			$this->record->current_round++;
		}

		$this->record->save();
		// In your Laravel code where you broadcast the event
		event(new TurnChanged($this->record->id, $this->record->current_turn, $this->record->current_round));
		//broadcast(new TurnChanged($this->record->id, $this->record->current_turn));

		//$this->mount($this->record->id);

	}
}
