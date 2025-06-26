<?php

namespace App\Filament\Resources\EncounterResource\Pages;

use App\Filament\Resources\EncounterResource;
use Filament\Actions;
use Filament\Pages\Page;
use Filament\Resources\Pages\ViewRecord;
use App\Events\TurnChanged;
use Illuminate\Support\Facades\Log;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Actions\Action;
use App\Events\EncounterImageUpdated;
use App\Models\CampaignImage;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use App\Models\MonsterInstance; // Added for type hinting
use App\Models\Character; // Added for type hinting
use Filament\Notifications\Notification; // Added for notifications


class RunEncounter extends ViewRecord
{
	protected static string $resource = EncounterResource::class;
    protected static string $view = 'filament.resources.encounter-resource.pages.run-encounter';

    public bool $showInitiativeModal = false;
    public array $initiativeInputs = [];
    public array $combatantsForView = [];

    /**
	 * Use the booted() lifecycle hook for setup logic.
	 * This runs after Filament has loaded the record.
	 */
    public function booted(): void
{
	$this->record->loadMissing(['playerCharacters', 'monsterInstances.monster']);

	$hasPlayers = $this->record->playerCharacters()->exists();
	$hasMonsters = $this->record->monsterInstances()->exists();

	// This is the same logic as before, but now in the correct place.
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
		$this->record->refresh();
		$this->record->loadMissing(['playerCharacters', 'monsterInstances.monster']);

		$playerCharacters = $this->record->playerCharacters()->orderBy('pivot_order', 'asc')->get()->map(function ($pc) {
			return [
				'id' => $pc->id,
				'type' => 'player',
				'name' => $pc->name,
				'order' => $pc->pivot->order,
				'initiative_roll' => $pc->pivot->initiative_roll,
				'original_model' => $pc,
			];
		});

		$monsterInstances = $this->record->monsterInstances()->with('monster')->orderBy('order', 'asc')->get()->map(function ($mi) {
			// FIX: Use a local variable instead of changing the model property directly.
			$currentHealth = $mi->current_health ?? $mi->monster->max_health;

			return [
				'id' => $mi->id,
				'type' => 'monster_instance',
				'name' => $mi->monster->name,
				'order' => $mi->order,
				'current_health' => $currentHealth,
				'max_health' => $mi->monster->max_health, // Also corrected this to pull from the base monster.
				'initiative_roll' => $mi->initiative_roll,
				'original_model' => $mi,
			];
		});

		$this->combatantsForView = $playerCharacters->merge($monsterInstances)
													->sortBy('order')
													->values()
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
		event(new TurnChanged($this->record->id, $this->record->current_turn, $this->record->current_round));
	}

	protected function getHeaderActions(): array
	{
		return [
			Action::make('selectCampaignImage')
				  ->label('Select Image')
				  ->form([
							 Select::make('selected_campaign_image_id')
								   ->label('Choose an Image')
								 // This part is working correctly, no changes needed.
								   ->getOptionLabelUsing(function ($value) {
									 $image = CampaignImage::find($value);
									 return $image ? ($image->caption ? "{$image->original_filename} ({$image->caption})" : $image->original_filename) : null;
								 })

								 // --- START OF CHANGES ---
								   ->getSearchResultsUsing(function (string $search) {
									 if (!$this->record->campaign_id) {
										 return [];
									 }
									 return CampaignImage::where('campaign_id', $this->record->campaign_id)
														 ->where(function ($query) use ($search) {
															 $query->where('original_filename', 'like', "%{$search}%")
																   ->orWhere('caption', 'like', "%{$search}%");
														 })
														 ->limit(50)
														 ->get()
														 ->mapWithKeys(function (CampaignImage $image) {
															 $caption = $image->caption ? " ({$image->caption})" : '';
															 $imageUrl = $image->image_url;
															 return [$image->id =>
																		 '<div style="display: flex; align-items: center; gap: 10px;">' .
																		 '<img src="' . e($imageUrl) . '" alt="" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;" />' .
																		 '<div>' . e($image->original_filename . $caption) . '</div>' .
																		 '</div>'
															 ];
														 });
								 })

								 // --- END OF CHANGES ---

								   ->allowHtml() // Still required for the search results
								   ->searchable() // Keep this, it now feeds the $search term to getSearchResultsUsing
								   ->required()
								   ->default($this->record->selected_campaign_image_id),
						 ])
				  ->action(function (array $data) {
					  $this->record->update(['selected_campaign_image_id' => $data['selected_campaign_image_id']]);
					  $selectedImage = CampaignImage::find($data['selected_campaign_image_id']);
					  if ($selectedImage) {
						  event(new EncounterImageUpdated($this->record->id, $selectedImage->image_url));
						  \Filament\Notifications\Notification::make()
															  ->title('Image selected successfully')
															  ->success()
															  ->send();
					  }
				  })
				  ->icon('heroicon-o-photo'),

            Action::make('uploadAndSelectCampaignImage')
                ->label('Upload New Image')
                ->form([
                    FileUpload::make('new_campaign_image')
                        ->label('Upload Image')
                        ->image()
                        ->disk('public')
                        ->directory('campaign-images/' . $this->record->campaign_id)
                        ->preserveFilenames()
                        ->required(),
                    Textarea::make('caption')
                        ->label('Caption (Optional)')
                        ->columnSpanFull(),
                ])
                ->action(function (array $data) {
                    if (!$this->record->campaign_id) {
                        \Filament\Notifications\Notification::make()
                            ->title('Error: Encounter not linked to a campaign.')
                            ->danger()
                            ->send();
                        return;
                    }

                    $newImage = CampaignImage::create([
                        'campaign_id' => $this->record->campaign_id,
                        'uploader_user_id' => auth()->id(),
                        'image_path' => $data['new_campaign_image'],
                        'original_filename' => basename($data['new_campaign_image']), // Or use TemporaryUploadedFile methods if available
                        'caption' => $data['caption'],
                    ]);

                    $this->record->update(['selected_campaign_image_id' => $newImage->id]);
                    event(new EncounterImageUpdated($this->record->id, $newImage->image_url));

                    \Filament\Notifications\Notification::make()
                        ->title('Image uploaded and selected successfully')
                        ->success()
                        ->send();
                })
                ->icon('heroicon-o-arrow-up-tray'),
		];
	}
}
