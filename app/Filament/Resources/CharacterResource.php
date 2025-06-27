<?php

namespace App\Filament\Resources;

use Filament\Forms\Form; // Correct import for the form method
// Remove this: use Filament\Schemas\Schema;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Get; // Correct import for Get utility
use Filament\Forms\Set; // Correct import for Set utility
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\CharacterResource\Pages\ListCharacters;
use App\Filament\Resources\CharacterResource\Pages\CreateCharacter;
use App\Filament\Resources\CharacterResource\Pages\EditCharacter;
use App\Filament\Resources\CharacterResource\Pages;
use App\Filament\Resources\CharacterResource\RelationManagers;
use App\Models\Character;
use Filament\Forms;
use Filament\Forms\Components;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile; // Required for type hinting in afterStateUpdated

/**
 * Filament Resource for managing Character models.
 *
 * This resource defines the form schema, table columns, and pages
 * for CRUD operations on Characters within the Filament admin panel.
 * It includes fields for character stats, type (player/monster), and health.
 */
class CharacterResource extends Resource
{
	/** @var string The Eloquent model this resource manages. */
	protected static ?string $model = Character::class;

	/** @var string|null The icon to use for navigation. Uses Heroicons. */
	protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-user-group';

	/**
	 * Defines the form schema for creating and editing Characters.
	 *
	 * @param \Filament\Forms\Form $form The form instance. // *** CHANGED PARAMETER TYPE HINT ***
	 * @return \Filament\Forms\Form The configured form.   // *** CHANGED RETURN TYPE HINT ***
	 */
	public static function form(Schema $schema): Schema
	{
		return $schema
			->schema([
					   FileUpload::make('character_json_upload')
								 ->label('Character JSON File')
								 ->acceptedFileTypes(['application/json'])
								 ->maxSize(1024) // Max size in kilobytes (1MB)
								 ->afterStateUpdated(function (Set $set, Get $get, $state) { // Set and Get correctly typed with new imports
							   // Initial checks for the state of the uploaded file
							   // $state here is the state of 'character_json_upload'
							   if (! $state instanceof UploadedFile) {
								   // Not an uploaded file instance (e.g., initial null state, or file removed by user)
								   // Clear all related form fields by setting them to their original schema defaults or null
								   $fieldsToReset = ['name', 'ac', 'max_health', 'strength', 'dexterity', 'constitution', 'intelligence', 'wisdom', 'charisma'];
								   foreach ($fieldsToReset as $field) {
									   // Note: $get($field) will correctly retrieve the *current* state of the field
									   // For resetting to default, you might want to consider explicit defaults
									   // or ensure your schema fields have them. For FileUpload, it will give null.
									   $set($field, null); // Set to null for a clean reset, or to a default if you have one elsewhere
								   }
								   $set('data', null); // Specifically ensure the 'data' field (for DB JSON) is nulled.
								   return;
							   }

							   if (!$state->isValid()) {
								   // File was uploaded but is not valid (e.g., upload error, too large after initial client check)
								   // Similar logic to clear/reset fields might be desired, or show a specific error
								   // For now, just return, but one might want to clear fields too.
								   return;
							   }

							   $content = file_get_contents($state->getRealPath());
							   if ($content === false) {
								   // Handle file read error, maybe log it or show a notification
								   // Optionally: Filament::notify('danger', 'Error reading file content.');
								   return;
							   }

							   $parsedData = static::parseCharacterJson($content);

							   if ($parsedData === null) {
								   // JSON parsing failed (invalid JSON)
								   // Clear all related form fields by setting them to their original schema defaults or null
								   $fieldsToReset = ['name', 'ac', 'max_health', 'strength', 'dexterity', 'constitution', 'intelligence', 'wisdom', 'charisma'];
								   foreach ($fieldsToReset as $field) {
									   $set($field, null); // Reset to null or schema default
								   }
								   $set('data', null); // Specifically ensure the 'data' field (for DB JSON) is nulled.
								   // Optionally: Filament::notify('danger', 'Invalid JSON file. Could not parse character data.');
								   return;
							   }

							   // Successfully parsed, now set form fields
							   $set('data', $parsedData['data']); // Full original JSON
							   $set('name', $parsedData['name']);
							   $set('ac', $parsedData['ac']);
							   $set('max_health', $parsedData['max_health'] ?? 10); // Use a hardcoded default or derive it
							   $set('strength', $parsedData['strength'] ?? 10);
							   $set('dexterity', $parsedData['dexterity'] ?? 10);
							   $set('constitution', $parsedData['constitution'] ?? 10);
							   $set('intelligence', $parsedData['intelligence'] ?? 10);
							   $set('wisdom', $parsedData['wisdom'] ?? 10);
							   $set('charisma', $parsedData['charisma'] ?? 10);
						   })
								 ->columnSpanFull(),
					   TextInput::make('name')
								->label('Character Name')
								->required()
								->maxLength(255)
								->columnSpanFull(),
					   // The 'type' field is removed as Characters are now always 'player' type.
					   // Ownership is determined by user_id, set automatically.
					   TextInput::make('ac')
								->label('Armor Class')
								->numeric() // Input should be a number.
								->required(),
					   TextInput::make('strength')->label('Strength')->numeric()->default(10),
					   TextInput::make('dexterity')->label('Dexterity')->numeric()->default(10),
					   TextInput::make('constitution')->label('Constitution')->numeric()->default(10),
					   TextInput::make('intelligence')->label('Intelligence')->numeric()->default(10),
					   TextInput::make('wisdom')->label('Wisdom')->numeric()->default(10),
					   TextInput::make('charisma')->label('Charisma')->numeric()->default(10),
					   TextInput::make('max_health')
								->label('Max Health')
								->numeric()
								->required()
								->default(10),
					   // current_health field removed
					   TextInput::make('class')
								->label('Class')
								->maxLength(255),
					   TextInput::make('ancestry')
								->label('Ancestry')
								->maxLength(255),
					   TextInput::make('title')
								->label('Title')
								->maxLength(255),
					   FileUpload::make('image')
								 ->label('Character Image')
								 ->disk('public')
								 ->directory('character-images')
								 ->image()
								 ->maxSize(10000),
					 ])->columns(2); // Arrange form fields in 2 columns.
	}

	/**
	 * Defines the table columns for listing Characters.
	 *
	 * @param Table $table The table instance.
	 * @return Table The configured table.
	 */
	public static function table(Table $table): Table
	{
		return $table
			->columns([
						  TextColumn::make('name')->searchable()->sortable(),
						  // 'type' column removed as it's implicitly 'player'
						  TextColumn::make('ac')->label('AC')->sortable(), // Shortened label for Armor Class.
						  TextColumn::make('max_health')->label('Max HP')->sortable(),
						  TextColumn::make('user.name')->label('Owner (GM)')->sortable(),
					  ])
			->filters([
						  // Filters can be defined here if needed.
					  ])
			->recordActions([
								EditAction::make(),
								DeleteAction::make(),
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
	 * Currently, no relation managers are defined for Characters directly from this resource.
	 * Characters are typically managed via Campaign or Encounter resources.
	 *
	 * @return array An array of relation manager class strings.
	 */
	public static function getRelations(): array
	{
		return [
			// No relation managers defined here by default.
			// Characters are usually related to Campaigns or Encounters.
		];
	}

	/**
	 * Defines the pages (routes) for this resource.
	 *
	 * @return array<string, string> An array mapping page names to routes.
	 */
	public static function getPages(): array
	{
		return [
			'index' => ListCharacters::route('/'),     // Main listing page.
			'create' => CreateCharacter::route('/create'), // Character creation page.
			'edit' => EditCharacter::route('/{record}/edit'), // Character editing page.
		];
	}

	public static function parseCharacterJson(string $jsonContent): ?array
	{
		$decodedJson = json_decode($jsonContent, true);

		if (json_last_error() !== JSON_ERROR_NONE) {
			return null; // Invalid JSON
		}

		$outputData = [];
		$outputData['data'] = $decodedJson; // Store the whole original JSON

		$outputData['name'] = $decodedJson['name'] ?? null;
		$outputData['ac'] = $decodedJson['armorClass'] ?? null;

		if (isset($decodedJson['maxHitPoints'])) {
			$outputData['max_health'] = $decodedJson['maxHitPoints'];
		} else {
			$outputData['max_health'] = null; // Or a schema default if accessible
		}

		if (isset($decodedJson['stats'])) {
			$stats = $decodedJson['stats'];
			$outputData['strength'] = $stats['STR'] ?? null;
			$outputData['dexterity'] = $stats['DEX'] ?? null;
			$outputData['constitution'] = $stats['CON'] ?? null;
			$outputData['intelligence'] = $stats['INT'] ?? null;
			$outputData['wisdom'] = $stats['WIS'] ?? null;
			$outputData['charisma'] = $stats['CHA'] ?? null;
		} else {
			// If 'stats' block is missing, set all stats to null (or schema defaults if accessible)
			foreach (['strength', 'dexterity', 'constitution', 'intelligence', 'wisdom', 'charisma'] as $statField) {
				$outputData[$statField] = null;
			}
		}
		return $outputData;
	}
}