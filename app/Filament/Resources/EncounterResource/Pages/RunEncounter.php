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
use App\Models\MonsterInstance; // Added for type hinting
use App\Models\Character; // Added for type hinting
use Filament\Notifications\Notification; // Added for notifications

class RunEncounter extends ViewRecord
{
    protected static string $resource = EncounterResource::class;
	protected static string $view = 'filament.resources.encounter-resource.pages.run-encounter';

    public bool $showInitiativeModal = false;
    public array $initiativeInputs = [];
    public array $combatantsForView = []; // For storing combined and sorted combatants

	protected function-disabled mount($record): void
    {
        parent::mount($record); // TODO: Filament\Resources\Pages\ViewRecord does not have a mount method. Check if this should be `fillForm()` or similar.
        $this->record->loadMissing(['playerCharacters', 'monsterInstances.monster']);

        // Check if initiative needs to be set (e.g., current_turn is 0 or null)
        // And ensure there are combatants
        $hasPlayers = $this->record->playerCharacters()->exists();
        $hasMonsters = $this->record->monsterInstances()->exists();

        if (($this->record->current_turn === null || $this->record->current_turn === 0) && ($hasPlayers || $hasMonsters)) {
            $this->showInitiativeModal = true;
            $this->prepareInitiativeInputs();
        } else {
            $this->loadCombatantsForView();
        }
    }

    protected function prepareInitiativeInputs(): void
    {
        $this->initiativeInputs = [];
        $this->record->playerCharacters->each(function (Character $pc) {
            $this->initiativeInputs[] = [
                'id' => $pc->id,
                'name' => $pc->name,
                'initiative' => $pc->pivot->initiative_roll ?? null,
                'type' => 'player',
                'key' => 'player_' . $pc->id, // Unique key for wire:model
            ];
        });
        $this->record->monsterInstances->each(function (MonsterInstance $mi) {
            $this->initiativeInputs[] = [
                'id' => $mi->id,
                'name' => $mi->monster->name,
                'initiative' => $mi->initiative_roll ?? null,
                'type' => 'monster_instance',
                'key' => 'monster_' . $mi->id, // Unique key for wire:model
            ];
        });
    }

    public function saveInitiativesAndStartEncounter(): void
    {
        if (empty($this->initiativeInputs)) {
            Notification::make()->title('No combatants to set initiative for.')->warning()->send();
            return;
        }

        foreach ($this->initiativeInputs as $input) {
            $initiativeValue = is_numeric($input['initiative']) ? (int)$input['initiative'] : 0;
            if ($input['type'] === 'player') {
                $this->record->playerCharacters()->updateExistingPivot($input['id'], ['initiative_roll' => $initiativeValue]);
            } elseif ($input['type'] === 'monster_instance') {
                MonsterInstance::find($input['id'])->update(['initiative_roll' => $initiativeValue]);
            }
        }

        $this->record->calculateOrder(); // This method should exist on the Encounter model
        $this->record->current_turn = 1;
        $this->record->current_round = 1;
        $this->record->save();

        $this->showInitiativeModal = false;
        $this->loadCombatantsForView(); // Refresh combatant list for the view
        $this->dispatch('refresh'); // General refresh event if needed, or specific component refresh

        Notification::make()->title('Initiative saved and encounter started!')->success()->send();
        event(new TurnChanged($this->record->id, $this->record->current_turn, $this->record->current_round));

    }

    public function updateMonsterHp(int $monsterInstanceId, ?string $newHp): void
    {
        $monsterInstance = MonsterInstance::find($monsterInstanceId);
        if (!$monsterInstance) {
            Notification::make()->title('Monster not found.')->danger()->send();
            return;
        }

        $validatedHp = is_numeric($newHp) ? (int)$newHp : null;

        if ($validatedHp === null) {
            // Allow clearing HP if needed, or set to 0 if preferred
            // For now, let's assume null means no change or error, depending on desired UX
            // Or perhaps revert to old value if input is cleared.
            // For this implementation, we'll just not update if non-numeric.
             Notification::make()->title('Invalid HP value.')->warning()->send();
            return;
        }

        if ($validatedHp < 0) {
            $validatedHp = 0;
        } elseif ($validatedHp > $monsterInstance->max_health) {
            $validatedHp = $monsterInstance->max_health;
        }

        $monsterInstance->current_health = $validatedHp;
        $monsterInstance->save();

        $this->loadCombatantsForView(); // Refresh the combatant list in the view with updated HP
        // No need to dispatch TurnChanged here, only HP updated.
        Notification::make()->title('HP updated for ' . $monsterInstance->monster->name)->success()->send();
    }

    protected function loadCombatantsForView(): void
    {
        $this->record->refresh(); // Ensure local record is up-to-date
        $this->record->loadMissing(['playerCharacters', 'monsterInstances.monster']);

        $playerCharacters = $this->record->playerCharacters()->orderBy('pivot_order', 'asc')->get()->map(function ($pc) {
            return [
                'id' => $pc->id,
                'type' => 'player',
                'name' => $pc->name,
                'order' => $pc->pivot->order,
                'initiative_roll' => $pc->pivot->initiative_roll,
                'original_model' => $pc,
                // Add other player-specific details if needed in the view
            ];
        });

        $monsterInstances = $this->record->monsterInstances()->with('monster')->orderBy('order', 'asc')->get()->map(function ($mi) {
            return [
                'id' => $mi->id,
                'type' => 'monster_instance',
                'name' => $mi->monster->name,
                'order' => $mi->order,
                'current_health' => $mi->current_health,
                'max_health' => $mi->max_health,
                'initiative_roll' => $mi->initiative_roll,
                'original_model' => $mi,
                // Add other monster-specific details if needed in the view
            ];
        });

        $this->combatantsForView = $playerCharacters->merge($monsterInstances)
                                       ->sortBy('order') // Sort by the 'order' field
                                       ->values()        // Re-index the collection
                                       ->all();
    }

	public function nextTurn()
	{
		// Load both player characters and monster instances to get the total count
		$playerCharacterCount = $this->record->playerCharacters()->count();
		$monsterInstanceCount = $this->record->monsterInstances()->count();
		$totalCombatants = $playerCharacterCount + $monsterInstanceCount;

		if ($totalCombatants === 0) {
			// No combatants, perhaps reset turn/round or do nothing
			$this->record->current_turn = 0;
			$this->record->current_round = $this->record->current_round > 0 ? $this->record->current_round : 1; // Keep round or set to 1
			$this->record->save();
			event(new TurnChanged($this->record->id, $this->record->current_turn, $this->record->current_round));
			return;
		}

		if ($this->record->current_turn < $totalCombatants) {
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
