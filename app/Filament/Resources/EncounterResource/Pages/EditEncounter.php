<?php

namespace App\Filament\Resources\EncounterResource\Pages;

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
            Actions\DeleteAction::make(),
			Action::make('startEncounter')
				  ->label('Start Encounter')
				  ->action(function (Encounter $record) {
					  $record->load('characters');
					  $record->calculateOrder();
					  $record->current_turn = 1;
					  $record->save();
					  return redirect(route('filament.admin.resources.encounters.run', $record));

				  }),
        ];
    }
}
