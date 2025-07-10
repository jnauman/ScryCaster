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
    protected string $view = 'filament.resources.encounter-resource.pages.run-encounter';

    public bool $showInitiativeModal = false;
    public array $initiativeInputs = [];
    public array $combatantsForView = [];
    public array $expandedMonsterInstances = []; // Added for inline collapsible details

    /**
	 * Use the booted() lifecycle hook for setup logic.
	 * This runs after Filament has loaded the record.
	 */
    public function booted(): void
{
    
	$this->record->loadMissing(['playerCharacters', 'monsterInstances.monster']);
    // Always load combatants for view on initial page load.
    $this->loadCombatantsForView();
}

    public function displayInitiativeModal(): void
    {
        $this->prepareInitiativeInputs();
        $this->showInitiativeModal = true;
    }

    protected function prepareInitiativeInputs(): void
    {
        $this->initiativeInputs = [];
        $this->record->loadMissing('monsterInstances.monster'); // Ensure monsters are loaded for display name logic

        // Add player characters
        $this->record->playerCharacters->each(function (Character $pc) {
            $this->initiativeInputs['player_' . $pc->id] = [
                'id' => $pc->id,
                'name' => $pc->name,
                'initiative' => $pc->pivot->initiative_roll ?? null,
                'type' => 'player',
                // 'key' is implicitly the array key now
            ];
        });

        $monsterInstances = $this->record->monsterInstances;
        $groupedMonsters = $monsterInstances->whereNotNull('initiative_group')->where('initiative_group', '!=', '')->groupBy('initiative_group');
        $ungroupedMonsters = $monsterInstances->whereNull('initiative_group')->union($monsterInstances->where('initiative_group', '==', ''));


        // Add initiative groups
        foreach ($groupedMonsters as $groupName => $instances) {
            // Sort instances by monster name (base type) then by display name for consistent listing
            $sortedInstances = $instances->sortBy([
                fn($mi) => $mi->monster->name, // Sort by base monster name first
                fn($mi) => $mi->display_name ?: $mi->monster->name, // Then by display name/actual name
            ]);

            // Use the initiative of the first monster in the original group, or null
            $firstInstance = $instances->first(); // Initiative should be consistent for the group anyway
            $monstersInGroupString = $sortedInstances->map(fn($mi) => $mi->display_name ?: $mi->monster->name)->join(', ');

            $this->initiativeInputs['group_' . $groupName] = [
                'id' => $groupName, // Group name serves as ID for the input field
                'name' => "Group: {$groupName} ({$monstersInGroupString})",
                'initiative' => $firstInstance->initiative_roll ?? null,
                'type' => 'monster_group',
                'member_ids' => $instances->pluck('id')->toArray(), // Store member IDs to update them later
            ];
        }

        // Add individual (ungrouped) monster instances
        $ungroupedMonsters->each(function (MonsterInstance $mi) {
            $this->initiativeInputs['monster_' . $mi->id] = [
                'id' => $mi->id,
                'name' => $mi->display_name ?: $mi->monster->name,
                'initiative' => $mi->initiative_roll ?? null,
                'type' => 'monster_instance',
            ];
        });
    }

    public function saveInitiativesAndStartEncounter(): void
    {
        if (empty($this->initiativeInputs)) {
            Notification::make()->title('No combatants to set initiative for.')->warning()->send();
            return;
        }

        foreach ($this->initiativeInputs as $key => $input) {
            $initiativeValue = is_numeric($input['initiative']) ? (int)$input['initiative'] : null; // Keep null if not numeric

            if ($input['type'] === 'player') {
                $this->record->playerCharacters()->updateExistingPivot($input['id'], ['initiative_roll' => $initiativeValue]);
            } elseif ($input['type'] === 'monster_instance') {
                MonsterInstance::find($input['id'])->update(['initiative_roll' => $initiativeValue]);
            } elseif ($input['type'] === 'monster_group') {
                // $input['id'] here is the groupName
                MonsterInstance::whereIn('id', $input['member_ids'])->update(['initiative_roll' => $initiativeValue]);
            }
        }

        $this->record->calculateOrder();
        // Only reset turn/round if encounter hasn't started
        if ($this->record->current_turn === null || $this->record->current_turn === 0) {
            $this->record->current_turn = 1;
            $this->record->current_round = ($this->record->current_round === null || $this->record->current_round === 0) ? 1 : $this->record->current_round; // Start at round 1 if not already set
        }
        // If current_round is 0 (e.g. after reset but before starting), set to 1
        if ($this->record->current_round === 0 && $this->record->current_turn !==0) {
             $this->record->current_round = 1;
        }

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
        $this->loadCombatantsForView(); // Refresh the view, which also re-initializes/cleans expandedMonsterInstances

        // Explicitly manage expanded states after removal, similar to nextTurn
        $newCurrentTurnOrder = $this->record->current_turn;
        $updatedExpandedStates = [];
        foreach ($this->combatantsForView as $combatant) {
            if ($combatant['type'] === 'monster_instance') {
                $updatedExpandedStates[$combatant['id']] = ($combatant['order'] == $newCurrentTurnOrder);
            }
        }
        $this->expandedMonsterInstances = $updatedExpandedStates;


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
            $displayName = $mi->display_name ?: $mi->monster->name;

			return [
				'id' => $mi->id,
				'type' => 'monster_instance',
                'name' => $displayName, // Use display_name or fallback to monster name
                'initiative_group' => $mi->initiative_group, // Added for visual indication
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
                'description' => $mi->monster->description,
                'traits' => $mi->monster->traits, // Ensure this is an array or string as expected by blade
                'attacks' => $mi->monster->attacks, // Ensure this is an array or string as expected by blade
				'original_model' => $mi,
                'group_color' => $mi->group_color, // Added group_color
			];
		});

		// $allCombatants = $playerCharacters->merge($monsterInstances)
		// 								 ->sortBy('order')
		// 								 ->values();
        // $this->combatantsForView = $allCombatants->all();

        // New grouping logic
        $groupedCombatants = [];
        $playerGroupIndex = 0; // For unique keys for player "groups"
        $allCombatantsSorted = $playerCharacters->merge($monsterInstances)->sortBy('order');

        foreach ($allCombatantsSorted as $combatantData) {
            // Make sure original_model is available for monster instances
            $monsterInstanceModel = null;
            if ($combatantData['type'] === 'monster_instance') {
                // The 'original_model' key should already be populated in the $monsterInstances mapping
                $monsterInstanceModel = $combatantData['original_model'] ?? null;
            }

            if ($combatantData['type'] === 'monster_instance' && !empty($combatantData['initiative_group'])) {
                $groupName = $combatantData['initiative_group'];

                if (!isset($groupedCombatants[$groupName])) {
                    $groupedCombatants[$groupName] = [
                        'type' => 'group',
                        'name' => $groupName,
                        // Use group_color from the model if available, otherwise default or empty
                        'group_css_classes' => $monsterInstanceModel ? $monsterInstanceModel->group_color : '',
                        'combatants' => [],
                        'order' => $combatantData['order'], // Use order of first member for group sorting
                    ];
                }
                $groupedCombatants[$groupName]['combatants'][] = $combatantData;
            } else {
                // Players and ungrouped monsters
                $groupKey = 'individual-' . $combatantData['type'] . '-' . ($combatantData['type'] === 'player' ? $playerGroupIndex++ : $combatantData['id']);
                $groupedCombatants[$groupKey] = [
                    'type' => 'individual',
                    'name' => $combatantData['name'],
                    'group_css_classes' => '', // No group border
                    'combatants' => [$combatantData], // Contains itself as a single combatant
                    'order' => $combatantData['order'],
                ];
            }
        }
        // Sort groups by the order of their first member / individual order
        uasort($groupedCombatants, function ($a, $b) {
            return $a['order'] <=> $b['order'];
        });
        $this->combatantsForView = array_values($groupedCombatants); // Re-index


        // Initialize or update expanded states
        $currentTurnOrder = $this->record->current_turn;
        $activeMonsterInstanceIds = $allCombatantsSorted->where('type', 'monster_instance')->pluck('id')->toArray(); // Corrected: Use $allCombatantsSorted

        // Preserve existing states for monsters still in combat
        $currentExpandedStates = array_intersect_key($this->expandedMonsterInstances, array_flip($activeMonsterInstanceIds));

        // Iterate over monster instances from $allCombatantsSorted to set initial expansion states
        foreach ($allCombatantsSorted as $combatantDataItem) {
            if ($combatantDataItem['type'] === 'monster_instance') {
                $id = $combatantDataItem['id'];
                if (!array_key_exists($id, $currentExpandedStates)) { // For new monsters or those not previously in expandedMonsterInstances
                    // Default to expanded if it's the current turn, otherwise collapsed.
                    $currentExpandedStates[$id] = ($combatantDataItem['order'] == $currentTurnOrder);
                }
                // Ensure the current turn monster is expanded
                if ($combatantDataItem['order'] == $currentTurnOrder) {
                    $currentExpandedStates[$id] = true;
                }
            }
        }
        $this->expandedMonsterInstances = $currentExpandedStates;
	}

    public function toggleMonsterDetail(int $monsterInstanceId): void
    {
        if (isset($this->expandedMonsterInstances[$monsterInstanceId])) {
            $this->expandedMonsterInstances[$monsterInstanceId] = !$this->expandedMonsterInstances[$monsterInstanceId];
        } else {
            // If not set, default to expanded. This case should ideally not be hit if initialized properly.
            $this->expandedMonsterInstances[$monsterInstanceId] = true;
        }
    }

    // Removed showMonsterModal method as it's being replaced by inline collapsible sections.

	public function nextTurn()
	{
        $allCombatantsOrdered = $this->record->getCombatants(); // Gets all Character and MonsterInstance models, sorted by 'order'
        $totalCombatantsCount = $allCombatantsOrdered->count();

        if ($totalCombatantsCount === 0) {
            $this->record->current_turn = 0;
            $this->record->current_round = max(1, $this->record->current_round ?? 1);
            $this->record->save();
            event(new TurnChanged($this->record->id, $this->record->current_turn, $this->record->current_round));
            return;
        }

        if ($this->record->current_turn === null || $this->record->current_turn === 0) {
            // Encounter hasn't started, or was reset. Start from the first combatant.
            $this->record->current_turn = $allCombatantsOrdered->first()->order ?? 1;
            $this->record->current_round = max(1, $this->record->current_round ?? 1);
        } else {
            $currentOrder = $this->record->current_turn;
            $currentCombatant = $allCombatantsOrdered->firstWhere(function ($c) use ($currentOrder) {
                return ($c instanceof Character ? $c->pivot->order : $c->order) == $currentOrder;
            });

            $nextOrderToFind = $currentOrder;

            if ($currentCombatant instanceof MonsterInstance && $currentCombatant->initiative_group) {
                // Current combatant is part of a group, find max order in this group
                $groupMembers = $this->record->monsterInstances()
                    ->where('initiative_group', $currentCombatant->initiative_group)
                    ->get();
                if ($groupMembers->isNotEmpty()) {
                    $nextOrderToFind = $groupMembers->max('order');
                }
            }

            // Find the next combatant whose order is strictly greater than $nextOrderToFind
            $nextCombatant = $allCombatantsOrdered->firstWhere(function ($c) use ($nextOrderToFind) {
                return ($c instanceof Character ? $c->pivot->order : $c->order) > $nextOrderToFind;
            });

            if ($nextCombatant) {
                $this->record->current_turn = ($nextCombatant instanceof Character ? $nextCombatant->pivot->order : $nextCombatant->order);
            } else {
                // Reached end of round, go to first combatant of next round
                $this->record->current_turn = $allCombatantsOrdered->first()->order ?? 1;
                $this->record->current_round = ($this->record->current_round ?? 0) + 1;
            }
        }

		$this->record->save();
		event(new TurnChanged($this->record->id, $this->record->current_turn, $this->record->current_round));
		$this->loadCombatantsForView(); // Reloads combatant data and correctly sets expandedMonsterInstances based on current turn
	}

	protected function getHeaderActions(): array
	{
		return [
			Action::make('selectCampaignImage')
				  ->label('Select Image')
				  ->schema([
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
						  Notification::make()
															  ->title('Image selected successfully')
															  ->success()
															  ->send();
					  }
				  })
				  ->icon('heroicon-o-photo'),

            Action::make('uploadAndSelectCampaignImage')
                ->label('Upload New Image')
                ->schema([
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
                        Notification::make()
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

                    Notification::make()
                        ->title('Image uploaded and selected successfully')
                        ->success()
                        ->send();
                })
                ->icon('heroicon-o-arrow-up-tray'),

            Action::make('addMonsters')
                ->label('Add Monsters')
                ->icon('heroicon-o-plus-circle')
                ->schema([
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
                    $this->loadCombatantsForView(); // Refresh the view, which also initializes expanded states for new monsters

                    // Explicitly manage expanded states after adding, similar to nextTurn
                    $newCurrentTurnOrder = $this->record->current_turn;
                    $updatedExpandedStates = [];
                    foreach ($this->combatantsForView as $combatant) {
                        if ($combatant['type'] === 'monster_instance') {
                            // If it's a new monster, it would have been initialized by loadCombatantsForView
                            // This ensures all are set according to current turn
                            $updatedExpandedStates[$combatant['id']] = ($combatant['order'] == $newCurrentTurnOrder);
                        }
                    }
                     // Merge to ensure any existing states (though less likely here) are handled, then override based on turn
                    $this->expandedMonsterInstances = array_merge($this->expandedMonsterInstances, $updatedExpandedStates);


                    Notification::make()->title( $data['quantity'] . ' ' . Str::plural($monster->name, $data['quantity']) . ' added.')->success()->send();
                    event(new TurnChanged($this->record->id, $this->record->current_turn, $this->record->current_round));
                }),
		];
	}
}
