<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CharacterResource\Pages;
use App\Filament\Resources\CharacterResource\RelationManagers;
use App\Models\Character;
use Filament\Forms;
use Filament\Forms\Components;
use Filament\Forms\Form;
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
	protected static ?string $navigationIcon = 'heroicon-o-user-group';

	/**
	 * Defines the form schema for creating and editing Characters.
	 *
	 * @param \Filament\Forms\Form $form The form instance.
	 * @return \Filament\Forms\Form The configured form.
	 */
	public static function form(Form $form): Form
	{
		return $form
			->schema([
				Forms\Components\FileUpload::make('character_json_upload')
					->label('Character JSON File')
					->acceptedFileTypes(['application/json'])
					->maxSize(1024) // Max size in kilobytes (1MB)
					->afterStateUpdated(function (Forms\Set $set, Forms\Get $get, $state) {
						// Initial checks for the state of the uploaded file
						// $state here is the state of 'character_json_upload'
						if (! $state instanceof UploadedFile) {
							// Not an uploaded file instance (e.g., initial null state, or file removed by user)
							// Clear all related form fields by setting them to their original schema defaults or null
							$fieldsToReset = ['name', 'ac', 'max_health', 'current_health', 'strength', 'dexterity', 'constitution', 'intelligence', 'wisdom', 'charisma'];
							foreach ($fieldsToReset as $field) {
								$set($field, $get($field)); // $get($field) will provide the schema default if one is defined, otherwise null for non-FileUpload fields
							}
							$set('data', null); // Specifically ensure the 'data' field (for DB JSON) is nulled. $get('data') would refer to the FileUpload.
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
							return;
						}

						$parsedData = static::parseCharacterJson($content);

						if ($parsedData === null) {
							// JSON parsing failed (invalid JSON)
							// Clear all related form fields by setting them to their original schema defaults or null
							$fieldsToReset = ['name', 'ac', 'max_health', 'current_health', 'strength', 'dexterity', 'constitution', 'intelligence', 'wisdom', 'charisma'];
							foreach ($fieldsToReset as $field) {
								$set($field, $get($field)); // Reset to schema default or null
							}
                            $set('data', null); // Specifically ensure the 'data' field (for DB JSON) is nulled.
							// Optionally: Filament::notify('danger', 'Invalid JSON file. Could not parse character data.');
							return;
						}

						// Successfully parsed, now set form fields
						$set('data', $parsedData['data']); // Full original JSON
						$set('name', $parsedData['name']);
						$set('ac', $parsedData['ac']);
						$set('max_health', $parsedData['max_health'] ?? $get('max_health'));
						$set('current_health', $parsedData['current_health'] ?? $get('current_health'));
						$set('strength', $parsedData['strength'] ?? $get('strength'));
						$set('dexterity', $parsedData['dexterity'] ?? $get('dexterity'));
						$set('constitution', $parsedData['constitution'] ?? $get('constitution'));
						$set('intelligence', $parsedData['intelligence'] ?? $get('intelligence'));
						$set('wisdom', $parsedData['wisdom'] ?? $get('wisdom'));
						$set('charisma', $parsedData['charisma'] ?? $get('charisma'));
					})
					->columnSpanFull(),
				Forms\Components\TextInput::make('name')
					->label('Character Name')
					->required()
					->maxLength(255)
					->columnSpanFull(),
				Forms\Components\Select::make('type')
					->label('Character Type')
					->options([ // Predefined options for character type.
						'player' => 'Player',
						'monster' => 'Monster',
					])
					->required(),
				Forms\Components\TextInput::make('ac')
					->label('Armor Class')
					->numeric() // Input should be a number.
					->required(),
				Forms\Components\TextInput::make('strength')->label('Strength')->numeric()->default(10),
				Forms\Components\TextInput::make('dexterity')->label('Dexterity')->numeric()->default(10),
				Forms\Components\TextInput::make('constitution')->label('Constitution')->numeric()->default(10),
				Forms\Components\TextInput::make('intelligence')->label('Intelligence')->numeric()->default(10),
				Forms\Components\TextInput::make('wisdom')->label('Wisdom')->numeric()->default(10),
				Forms\Components\TextInput::make('charisma')->label('Charisma')->numeric()->default(10),
				Forms\Components\TextInput::make('max_health')
					->label('Max Health')
					->numeric()
					->required()
					->default(10),
				Forms\Components\TextInput::make('current_health')
					->label('Current Health')
					->numeric()
					->required()
					->default(10),
			])->columns(2); // Arrange form fields in 2 columns.
	}

	/**
	 * Defines the table columns for listing Characters.
	 *
	 * @param \Filament\Tables\Table $table The table instance.
	 * @return \Filament\Tables\Table The configured table.
	 */
	public static function table(Table $table): Table
	{
		return $table
			->columns([
				Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
				Tables\Columns\TextColumn::make('type')->sortable(),
				Tables\Columns\TextColumn::make('ac')->label('AC')->sortable(), // Shortened label for Armor Class.
				Tables\Columns\TextColumn::make('max_health')->label('Max HP')->sortable(),
				Tables\Columns\TextColumn::make('current_health')->label('Current HP')->sortable(),
			])
			->filters([
				// Filters can be defined here if needed.
			])
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
			'index' => Pages\ListCharacters::route('/'),     // Main listing page.
			'create' => Pages\CreateCharacter::route('/create'), // Character creation page.
			'edit' => Pages\EditCharacter::route('/{record}/edit'), // Character editing page.
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
			// Default current_health to max_health if maxHitPoints is present
			$outputData['current_health'] = $decodedJson['maxHitPoints'];
		} else {
			$outputData['max_health'] = null; // Or a schema default if accessible
			$outputData['current_health'] = null; // Or a schema default
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