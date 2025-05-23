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
}