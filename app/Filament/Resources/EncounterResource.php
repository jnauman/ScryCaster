<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EncounterResource\Pages;
use App\Filament\Resources\EncounterResource\RelationManagers;
use App\Models\Encounter;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class EncounterResource extends Resource
{
	protected static ?string $model = Encounter::class;

	protected static ?string $navigationIcon = 'heroicon-o-sparkles';

	public static function form(Form $form): Form
	{
		return $form
			->schema([
						 // Add this Select field for Campaign
						 Forms\Components\Select::make('campaign_id')
												->label('Campaign')
												->relationship(
													name: 'campaign', // Relationship method name on Encounter model
													titleAttribute: 'name', // Column to display from Campaign model
													// Modify query to only show campaigns belonging to the current GM
													modifyQueryUsing: fn (Builder $query) => $query->where('gm_user_id', Auth::id())
												)
												->searchable()
												->preload() // Good for shorter lists of campaigns
												->required() // Make selecting a campaign mandatory
												->native(false), // Use Filament's styled dropdown

						 // Existing fields (add any others needed for an encounter)
						 Forms\Components\TextInput::make('name')
												   ->required()
												   ->maxLength(255),

						 // Other fields like current_round, current_turn might be set later
						 // or have defaults, so may not need to be in the create form.

					 ]);
	}

	public static function table(Table $table): Table
	{
		return $table
			->columns([
						  Tables\Columns\TextColumn::make('name'),
						  Tables\Columns\TextColumn::make('current_round'),
					  ])
			->filters([])
			->actions([
						  Tables\Actions\EditAction::make(),
						  Tables\Actions\DeleteAction::make(),
					  ])
			->bulkActions([
							  Tables\Actions\BulkActionGroup::make([
																	   Tables\Actions\DeleteBulkAction::make(),
																   ]),
						  ]);
	}

	public static function getRelations(): array
	{
		return [RelationManagers\CharactersRelationManager::class];
	}

	public static function getPages(): array
	{
		return [
			'index' => Pages\ListEncounters::route('/'),
			'create' => Pages\CreateEncounter::route('/create'),
			'edit' => Pages\EditEncounter::route('/{record}/edit'),
			'run' => Pages\RunEncounter::route('/{record}/run'),
		];
	}

	/*public static function getActions(): array
	{
		return [

		];
		return [
			Actions\Action::make('startEncounter')
						  ->label('Start Encounter')
						  ->icon('heroicon-o-play')
						  ->action(function (Encounter $record) {
							  $characters = $record->characters;

							  $initiativeOrder = $characters->sortByDesc('initiative_roll')->values();

							  $order = 1;
							  foreach ($initiativeOrder as $character) {
								  $record->characters()->updateExistingPivot($character->id, ['order' => $order]);
								  $order++;
							  }

							  $record->update(['current_turn' => 1, 'current_round' => 1]);
							  Notification::make()->title('Encounter Started')->success()->send();
						  }),
			Actions\Action::make('nextTurn')
						  ->label('Next Turn')
						  ->icon('heroicon-o-arrow-right')
						  ->requiresConfirmation()
						  ->action(function (Encounter $record, \Livewire\Component $livewire) {
							  $characterCount = $record->characters->count();
							  if ($record->current_turn < $characterCount) {
								  $record->current_turn++;
							  } else {
								  $record->current_turn = 1;
								  $record->current_round++;
							  }
							  $record->save();
							  $livewire->dispatch('refresh');
						  }),
			Actions\Action::make('viewDashboard')
						  ->label('View Dashboard')
						  ->url(fn (Encounter $record): string => route('encounter.dashboard', ['encounterId' => $record->id]))
						  ->openUrlInNewTab(),
		];
	}*/
}