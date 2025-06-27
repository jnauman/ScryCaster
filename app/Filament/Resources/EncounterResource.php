<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EncounterResource\Pages\CreateEncounter;
use App\Filament\Resources\EncounterResource\Pages\EditEncounter;
use App\Filament\Resources\EncounterResource\Pages\ListEncounters;
use App\Filament\Resources\EncounterResource\Pages\RunEncounter;
use App\Filament\Resources\EncounterResource\RelationManagers\MonsterInstancesRelationManager;
use App\Filament\Resources\EncounterResource\RelationManagers\PlayerCharactersRelationManager;
use App\Models\Encounter;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

// Corrected: No longer using the generic RelationManagers namespace directly for listing them
// use App\Filament\Resources\EncounterResource\RelationManagers;
// use Filament\Actions; // Not used as actions are commented out
// use Filament\Notifications\Notification; // Not used as actions are commented out
// use Illuminate\Database\Eloquent\SoftDeletingScope; // Not used

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
	protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-sparkles'; // Icon representing encounters or events.
    /**
     * Defines the form schema for creating and editing Encounters.
     *
     * @param \Filament\Schemas\Schema $schema The form instance.
     * @return \Filament\Schemas\Schema The configured form.
     */
	public static function form(Schema $schema): Schema
	{
		return $schema
			->schema([
						 Select::make('campaign_id')
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

				TextInput::make('name')
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
     * @param Table $table The table instance.
     * @return Table The configured table.
     */
    public static function table(Table $table): Table
	{
		return $table
			->columns([
				TextColumn::make('name')->searchable()->sortable(),
				TextColumn::make('campaign.name')->label('Campaign')->searchable()->sortable(),
				TextColumn::make('current_round')->label('Round')->sortable(),
				// Tables\Columns\TextColumn::make('current_turn')->label('Turn')->sortable(), // Optional, might be too granular for list view
				TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
				TextColumn::make('updated_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
			])
			->filters([
				// Filters can be added here, e.g., by campaign.
				// Tables\Filters\SelectFilter::make('campaign_id')
				//     ->label('Campaign')
				//     ->relationship('campaign', 'name', fn (Builder $query) => $query->where('gm_user_id', Auth::id())),
			])
			->recordActions([
				EditAction::make(),
				DeleteAction::make(),
				// Potentially a custom action to navigate to the 'run' page for the encounter.
				// Tables\Actions\Action::make('run')
				//    ->label('Run Encounter')
				//    ->url(fn (Encounter $record): string => static::getUrl('run', ['record' => $record]))
				//    ->icon('heroicon-o-play'),
			])
			->toolbarActions([
				BulkActionGroup::make([
					DeleteBulkAction::make(),
				]),
			]);
	}

	/**
     * Defines the relation managers for this resource.
     *
     * This allows managing characters associated with an encounter directly
     * from the encounter's edit page.
     *
     * @return array<class-string<RelationManager>>
     */
    public static function getRelations(): array
	{
		return [
            PlayerCharactersRelationManager::class,
            MonsterInstancesRelationManager::class,
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
			'index' => ListEncounters::route('/'),         // Main listing page.
			'create' => CreateEncounter::route('/create'),   // Encounter creation page.
			'edit' => EditEncounter::route('/{record}/edit'), // Encounter editing page.
			'run' => RunEncounter::route('/{record}/run'),   // Custom page to "run" or manage an active encounter.
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