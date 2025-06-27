<?php

namespace App\Filament\Resources\EncounterResource\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Resources\EncounterResource;
use App\Models\Encounter;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;

class EditEncounter extends EditRecord
{
    protected static string $resource = EncounterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
			Action::make('startEncounter')
				  ->label('Start Encounter')
				  ->action(function (Encounter $record) {
					  // Session flash no longer needed as initiative modal is manually triggered on RunEncounter page.
					  // This action now simply navigates to the run page.

					  // We might want to reconsider setting current_turn here,
					  // as the initiative modal on the RunEncounter page is intended to handle the actual start.
					  // For now, let's keep it to see how it interacts with the new modal trigger.
					  // If current_turn is already 1, the modal condition on RunEncounter page needs to allow this.
					  // The current plan is: `($this->record->current_turn === null || $this->record->current_turn === 0)`
					  // So, setting current_turn = 1 here would prevent the modal from showing with that condition.
					  // Let's remove setting current_turn and current_round here, and let the RunEncounter page handle it.
					  // $record->load('playerCharacters');
					  // $record->calculateOrder(); // calculateOrder is also called in saveInitiativesAndStartEncounter
					  // $record->current_turn = 1;
					  // $record->save(); // Save is not strictly needed if we only redirect

					  return redirect(EncounterResource::getUrl('run', ['record' => $record]));
				  }),
        ];
    }
}
