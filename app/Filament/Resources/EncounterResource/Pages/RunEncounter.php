<?php

namespace App\Filament\Resources\EncounterResource\Pages;

use App\Filament\Resources\EncounterResource;
use Filament\Actions;
use Filament\Pages\Page;
use Filament\Resources\Pages\ViewRecord;

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
		$this->mount($this->record->id);
	}
}
