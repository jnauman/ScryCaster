<?php

namespace App\Filament\Resources\EncounterResource\Pages;

use App\Filament\Resources\EncounterResource;
use Filament\Actions;
use Filament\Pages\Page;
use Filament\Resources\Pages\ViewRecord;
use App\Events\TurnChanged;
use Illuminate\Support\Facades\Log;
use Filament\Forms\Components\FileUpload;
use Filament\Actions\Action;
use App\Events\EncounterImageUpdated; // Create this event later
use Illuminate\Support\Facades\Storage;

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

	protected function getHeaderActions(): array
	{
		return [
			Action::make('uploadImage')
				  ->label('Upload Image')
				  ->form([
							 FileUpload::make('current_image_upload')
									   ->label('Encounter Image')
									   ->image() // Specify it's an image
								 		->rules(['image'])
									   ->disk('public') // Use the public disk
									   ->directory('encounter-images') // Optional: store in a subdirectory
									   ->required()
									   ->helperText('Upload a new image to display to players.')
									   //->reactive() // Make it reactive if needed, though maybe not necessary in a simple action
						 ])
				  ->action(function (array $data) {
					  // $data['current_image_upload'] will contain the temporary path
					  // Filament handles the final move. We just need to save the path.

					  // Get the final path after Filament stores it
					  $path = $data['current_image_upload'];

					  $this->record->update(['current_image' => $path]);
					  $this->refreshFormData(['current_image_upload']); // Clear the form input potentially

					  // Broadcast the update
					  event(new EncounterImageUpdated($this->record->id, Storage::disk('public')->url($path)));

					  \Filament\Notifications\Notification::make()
														  ->title('Image uploaded successfully')
														  ->success()
														  ->send();

				  }),
		];
	}
}
