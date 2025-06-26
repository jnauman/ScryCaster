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
use App\Models\Monster; // Added for Monster selection
use Filament\Notifications\Notification; // Added for notifications
use Filament\Forms\Components\TextInput; // Added for TextInput in action form
use Illuminate\Support\Str; // Added for Str::plural


class RunEncounter extends ViewRecord
{
	protected static string $resource = EncounterResource::class;
    protected static string $view = 'filament.resources.encounter-resource.pages.run-encounter';

    public bool $showInitiativeModal = false;
    public array $initiativeInputs = [];
    public array $combatantsForView = [];
    public bool $showMonsterDetailModal = false;
    public ?array $selectedMonsterForModal = null;

    /**
	 * Use the booted() lifecycle hook for setup logic.
	 * This runs after Filament has loaded the record.
	 */
    public function booted(): void
{
    Log::debug('RunEncounter booted. Encounter ID: ' . $this->record->id . ', Current Turn: ' . $this->record->current_turn);
	$this->record->loadMissing(['playerCharacters', 'monsterInstances.monster']);

	$hasPlayers = $this->record->playerCharacters()->exists();
	$hasMonsters = $this->record->monsterInstances()->exists();

    Log::debug('Has Players: ' . ($hasPlayers ? 'Yes' : 'No') . ', Has Monsters: ' . ($hasMonsters ? 'Yes' : 'No'));

	// This is the same logic as before, but now in the correct place.
	if (($this->record->current_turn === null || $this->record->current_turn === 0) && ($hasPlayers || $hasMonsters)) {
        Log::debug('Condition met: Showing initiative modal.');
		$this->showInitiativeModal = true;
		$this->prepareInitiativeInputs();
	} else {
        Log::debug('Condition not met or encounter already started. Loading combatants for view. Current turn: ' . $this->record->current_turn);
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

    public function removeMonsterInstance(int $monsterInstanceId): void
    {
        $monsterInstance = MonsterInstance::find($monsterInstanceId);

        if (!$monsterInstance) {
            Notification::make()->title('Monster instance not found.')->danger()->send();
            return;
        }

        $removedOrder = $monsterInstance->order; // Get order before deletion
        $currentTurnBeforeDelete = $this->record->current_turn;

        $monsterInstance->delete();

        // Recalculate order for all remaining combatants. This re-sequences 'order' column from 1 to N.
        $this->record->calculateOrder();
        $this->record->refresh(); // Refresh the record to get updated orders and potentially combatant counts.

        $totalCombatants = $this->record->playerCharacters()->count() + $this->record->monsterInstances()->count();

        if ($totalCombatants === 0) {
            $this->record->current_turn = 0;
            // current_round could also be reset or handled as per game rules for empty encounters
        } else {
            // If the encounter was active (current_turn was set)
            if ($currentTurnBeforeDelete !== null && $currentTurnBeforeDelete !== 0) {
                if ($currentTurnBeforeDelete > $removedOrder) {
                    // If the active turn was after the removed combatant, its effective position shifts up by 1.
                    // So, we decrement current_turn to keep it on the same logical combatant.
                    $this->record->current_turn = $currentTurnBeforeDelete - 1;
                } elseif ($currentTurnBeforeDelete == $removedOrder) {
                    // If the removed combatant was the one whose turn it was.
                    // The turn effectively passes to the next combatant in the new order,
                    // which now has the same 'order' number, unless it was the last one.
                    // We just need to ensure current_turn does not exceed the new totalCombatants.
                    $this->record->current_turn = $currentTurnBeforeDelete; // Stays, but might be clamped
                    if ($this->record->current_turn > $totalCombatants) {
                        $this->record->current_turn = $totalCombatants; // Clamp to the new last combatant
                    }
                } else { // $currentTurnBeforeDelete < $removedOrder
                    // If the active turn was before the removed combatant, its number doesn't change.
                    $this->record->current_turn = $currentTurnBeforeDelete;
                }

                // Ensure current_turn is at least 1 if there are combatants
                if ($this->record->current_turn === 0 && $totalCombatants > 0) {
                    $this->record->current_turn = 1;
                }
            } else {
                // Encounter was not started (current_turn was 0 or null), remains 0 or null if still no combatants,
                // or will be handled by initiative modal logic if new combatants are added/encounter starts.
                // For now, if it was 0, keep it 0. If combatants exist, it should ideally be 1 if started.
                // However, this function assumes an ongoing or ready-to-start encounter.
                // If current_turn was 0 and totalCombatants > 0, it implies initiative wasn't rolled.
                // We'll leave current_turn as is, as it's handled by the start encounter flow.
                 $this->record->current_turn = $currentTurnBeforeDelete; // Retain null or 0
            }
        }

        $this->record->save();
        $this->loadCombatantsForView(); // Refresh the view

        Notification::make()->title('Monster instance removed successfully.')->success()->send();
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
				'max_health' => $mi->monster->max_health,
				'initiative_roll' => $mi->initiative_roll,
				// Add base monster stats for inline display
				'ac' => $mi->monster->ac,
				'movement' => $mi->monster->movement,
				'strength' => $mi->monster->strength,
				'dexterity' => $mi->monster->dexterity,
				'constitution' => $mi->monster->constitution,
				'intelligence' => $mi->monster->intelligence,
				'wisdom' => $mi->monster->wisdom,
				'charisma' => $mi->monster->charisma,
				'original_model' => $mi, // Keep original model if needed elsewhere
			];
		});

		$this->combatantsForView = $playerCharacters->merge($monsterInstances)
													->sortBy('order')
													->values()
													->all();
	}

    public function showMonsterModal(int $monsterInstanceId): void
    {
        $monsterInstance = MonsterInstance::with('monster')->find($monsterInstanceId);

        if (!$monsterInstance || !$monsterInstance->monster) {
            Notification::make()->title('Monster data not found.')->danger()->send();
            return;
        }

        $monster = $monsterInstance->monster;

        $this->selectedMonsterForModal = [
            'id' => $monsterInstance->id,
            'name' => $monster->name,
            'ac' => $monster->ac,
            'movement' => $monster->movement,
            'alignment' => $monster->alignment,
            'strength' => $monster->strength,
            'dexterity' => $monster->dexterity,
            'constitution' => $monster->constitution,
            'intelligence' => $monster->intelligence,
            'wisdom' => $monster->wisdom,
            'charisma' => $monster->charisma,
            'description' => $monster->description,
			'attacks' => $monster->attacks,
            'traits' => $monster->traits,
            'current_health' => $monsterInstance->current_health ?? $monster->max_health,
            'max_health' => $monster->max_health,
            // Add any other fields from Monster or MonsterInstance needed in the modal
        ];

        $this->showMonsterDetailModal = true;
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

            Action::make('addMonsters')
                ->label('Add Monsters')
                ->icon('heroicon-o-plus-circle')
                ->form([
                    Select::make('monster_id')
                        ->label('Monster')
                        ->options(Monster::query()->pluck('name', 'id'))
                        ->searchable()
                        ->required(),
                    TextInput::make('quantity')
                        ->numeric()
                        ->label('Quantity')
                        ->default(1)
                        ->minValue(1)
                        ->required(),
                    TextInput::make('initiative_roll')
                        ->numeric()
                        ->label('Initiative Roll (Optional)')
                        ->nullable(),
                ])
                ->action(function (array $data) {
                    $monster = Monster::find($data['monster_id']);
                    if (!$monster) {
                        Notification::make()->title('Selected monster not found.')->danger()->send();
                        return;
                    }

                    for ($i = 0; $i < $data['quantity']; $i++) {
                        MonsterInstance::create([
                            'encounter_id' => $this->record->id,
                            'monster_id' => $monster->id,
                            'current_health' => $monster->max_health, // Assuming Monster model has max_health
                            'max_health' => $monster->max_health,     // Assuming Monster model has max_health
                            'initiative_roll' => $data['initiative_roll'], // Can be null
                            // 'order' will be set by calculateOrder
                        ]);
                    }

                    $this->record->calculateOrder();
                    // If encounter hasn't started, new monsters will be included in the initiative prompt
                    if ($this->record->current_turn === null || $this->record->current_turn === 0) {
                        $this->showInitiativeModal = true; // Re-trigger modal if encounter not started
                        $this->prepareInitiativeInputs();  // It will now include the new monsters
                    } else {
                        // If encounter started, and no initiative was given, they get null/0.
                        // GM might need to manually adjust or we'd need a more complex "edit initiative" feature.
                        // For now, they are added and order is recalculated.
                    }

                    $this->record->save(); // Save encounter if current_turn/round changed by calculateOrder implicitly (though unlikely)
                    $this->loadCombatantsForView(); // Refresh the view

                    Notification::make()->title( $data['quantity'] . ' ' . Str::plural($monster->name, $data['quantity']) . ' added.')->success()->send();
                    event(new TurnChanged($this->record->id, $this->record->current_turn, $this->record->current_round));
                }),
		];
	}
}
