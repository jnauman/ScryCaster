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
// use Filament\Actions; // Not used as actions are commented out
// use Filament\Notifications\Notification; // Not used as actions are commented out
use Illuminate\Database\Eloquent\Builder;
// use Illuminate\Database\Eloquent\SoftDeletingScope; // Not used
use Illuminate\Support\Facades\Auth;

/**
 * Filament Resource for managing Encounter models.
 *
 * This resource defines the form schema, table columns, relations, and pages
 * for CRUD operations on Encounters within the Filament admin panel.
 * It allows associating encounters with campaigns owned by the current Game Master.
 */
class EncounterResource extends Resource
{
	/** @var string The Eloquent model this resource manages. */
	protected static ?string $model = Encounter::class;

	/** @var string|null The icon to use for navigation. Uses Heroicons. */
	protected static ?string $navigationIcon = 'heroicon-o-sparkles'; // Icon representing encounters or events.

	/**
	 * Defines the form schema for creating and editing Encounters.
	 *
	 * @param \Filament\Forms\Form $form The form instance.
	 * @return \Filament\Forms\Form The configured form.
	 */
	public static function form(Form $form): Form
	{
		return $form
			->schema([
				Forms\Components\Select::make('campaign_id')
					->label('Campaign')
					->relationship(
						name: 'campaign', // The relationship method name on the Encounter model.
						titleAttribute: 'name', // The attribute from the Campaign model to display as option text.
						// Modifies the query for the relationship to only show campaigns
						// where the 'gm_user_id' matches the currently authenticated user.
						modifyQueryUsing: fn (Builder $query) => $query->where('gm_user_id', Auth::id())
					)
					->searchable() // Allows searching through campaigns.
					->preload()    // Preloads campaign options, good for shorter lists.
					->required()   // A campaign must be selected.
					->native(false), // Uses Filament's styled dropdown instead of the browser's native one.

				Forms\Components\TextInput::make('name')
					->label('Encounter Name')
					->required()
					->maxLength(255)
					->columnSpanFull(),

				// Fields for 'current_round' and 'current_turn' are typically managed
				// by the application logic during an encounter, not directly in this form.
				// They might be set to defaults (e.g., 0 or 1) upon creation via the model or an observer.
				// Forms\Components\TextInput::make('current_round')->numeric()->default(0),
				// Forms\Components\TextInput::make('current_turn')->numeric()->default(0),
			]);
	}

	/**
	 * Defines the table columns for listing Encounters.
	 *
	 * @param \Filament\Tables\Table $table The table instance.
	 * @return \Filament\Tables\Table The configured table.
	 */
	public static function table(Table $table): Table
	{
		return $table
			->columns([
				Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
				Tables\Columns\TextColumn::make('campaign.name')->label('Campaign')->searchable()->sortable(),
				Tables\Columns\TextColumn::make('current_round')->label('Round')->sortable(),
				// Tables\Columns\TextColumn::make('current_turn')->label('Turn')->sortable(), // Optional, might be too granular for list view
				Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
				Tables\Columns\TextColumn::make('updated_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
			])
			->filters([
				// Filters can be added here, e.g., by campaign.
				// Tables\Filters\SelectFilter::make('campaign_id')
				//     ->label('Campaign')
				//     ->relationship('campaign', 'name', fn (Builder $query) => $query->where('gm_user_id', Auth::id())),
			])
			->actions([
				Tables\Actions\EditAction::make(),
				Tables\Actions\DeleteAction::make(),
				// Potentially a custom action to navigate to the 'run' page for the encounter.
				// Tables\Actions\Action::make('run')
				//    ->label('Run Encounter')
				//    ->url(fn (Encounter $record): string => static::getUrl('run', ['record' => $record]))
				//    ->icon('heroicon-o-play'),
			])
			->bulkActions([
				Tables\Actions\BulkActionGroup::make([
					Tables\Actions\DeleteBulkAction::make(),
				]),
			]);
	}

	/**
	 * Defines the relation managers for this resource.
	 *
	 * This allows managing characters associated with an encounter directly
	 * from the encounter's edit page.
	 *
	 * @return array<class-string<\Filament\Resources\RelationManagers\RelationManager>>
	 */
	public static function getRelations(): array
	{
		return [
			RelationManagers\CharactersRelationManager::class, // Manages characters within this encounter.
		];
	}

	/**
	 * Defines the pages (routes) for this resource.
	 *
	 * Includes standard CRUD pages and a custom 'run' page for active encounter management.
	 *
	 * @return array<string, string> An array mapping page names to routes.
	 */
	public static function getPages(): array
	{
		return [
			'index' => Pages\ListEncounters::route('/'),         // Main listing page.
			'create' => Pages\CreateEncounter::route('/create'),   // Encounter creation page.
			'edit' => Pages\EditEncounter::route('/{record}/edit'), // Encounter editing page.
			'run' => Pages\RunEncounter::route('/{record}/run'),   // Custom page to "run" or manage an active encounter.
		];
	}

	/*
	// Example of how custom actions might be defined for an Encounter.
	// These are currently commented out as they might be better placed on the 'RunEncounter' page
	// or handled by specific Livewire components within that page.
	public static function getActions(): array
	{
		return [
			Actions\Action::make('startEncounter')
				->label('Start Encounter')
				->icon('heroicon-o-play')
				->action(function (Encounter $record) {
					// Logic to initialize the encounter, e.g., calculate character order, set round to 1.
					$record->calculateOrder(); // Assuming a method on the Encounter model
					$record->update(['current_turn' => 1, 'current_round' => 1]);
					Notification::make()->title('Encounter Started')->success()->send();
				}),
			Actions\Action::make('nextTurn')
				->label('Next Turn')
				->icon('heroicon-o-arrow-right')
				->requiresConfirmation()
				->action(function (Encounter $record, \Livewire\Component $livewire) {
					// Logic to advance to the next turn or round.
					$characterCount = $record->characters->count();
					if ($record->current_turn < $characterCount) {
						$record->current_turn++;
					} else {
						$record->current_turn = 1;
						$record->current_round++;
					}
					$record->save();
					// Potentially dispatch an event to refresh parts of the UI.
					$livewire->dispatch('refresh'); // If this action is part of a Livewire component context.
				}),
			Actions\Action::make('viewDashboard')
				->label('View Dashboard')
				->url(fn (Encounter $record): string => route('encounter.dashboard', ['encounterId' => $record->id]))
				->openUrlInNewTab(),
		];
	}
	*/
}