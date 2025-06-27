<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MonsterResource\Pages\CreateMonster;
use App\Filament\Resources\MonsterResource\Pages\EditMonster;
use App\Filament\Resources\MonsterResource\Pages\ListMonsters;
use App\Filament\Resources\MonsterResource\RelationManagers;
use App\Models\Monster;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form; // This import is correct for Filament 4
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

// For FileUpload afterStateUpdated (commented out)
// If you uncomment the afterStateUpdated, you'll also need these:
// use Filament\Forms\Set;
// use Filament\Forms\Get; // If you use Get within the closure
// use Illuminate\Http\UploadedFile; // Or use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class MonsterResource extends Resource
{
	protected static ?string $model = Monster::class;

	protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-shield-exclamation';

	/**
	 * Defines the form schema for creating and editing Monsters.
	 *
	 * @param Form $form The form instance.
	 * @return Form The configured form.
	 */
	public static function form(Schema $schema): Schema
	{
		return $schema
			->schema([
							 FileUpload::make('monster_json_upload')
									   ->label('Upload Monster JSON')
									   ->acceptedFileTypes(['application/json'])
									   ->columnSpanFull()
								 // If you uncomment this, ensure Set, Get, and UploadedFile are imported
								 // ->afterStateUpdated(function (Set $set, Get $get, UploadedFile $file) { // Add Get if needed
								 //     $data = self::parseMonsterJson($file->get());
								 //     if ($data) {
								 //         $set('name', $data['name'] ?? null);
								 //         $set('ac', $data['ac'] ?? null);
								 //         // ... set other fields
								 //         $set('data', json_encode($data['data'] ?? []));
								 //     }
								 // })
									   ->helperText('Upload a JSON file to pre-fill monster data. Manual fields below will override JSON values if subsequently edited.'),
							 TextInput::make('name')
									  ->required()
									  ->maxLength(255)
									  ->columnSpanFull(),
							 TextInput::make('ac')
									  ->label('Armor Class')
									  ->numeric()
									  ->required(),
							 TextInput::make('max_health')
									  ->label('Max Health')
									  ->numeric()
									  ->default(10), // Removed required as it has a default
							 TextInput::make('strength')
									  ->numeric()
									  ->default(10), // Removed required
							 TextInput::make('dexterity')
									  ->numeric()
									  ->default(10), // Removed required
							 TextInput::make('constitution')
									  ->numeric()
									  ->default(10), // Removed required
							 TextInput::make('intelligence')
								 // ->tel() // Removed tel() type, it's for telephone numbers
									  ->numeric()
									  ->default(10), // Removed required
							 TextInput::make('wisdom')
									  ->numeric()
									  ->default(10), // Removed required
							 TextInput::make('charisma')
									  ->numeric()
									  ->default(10), // Removed required
							 Textarea::make('data')
									 ->label('Additional Data (JSON)')
									 ->columnSpanFull()
									 ->nullable(),
							 Select::make('user_id')
								   ->label('Owner (GM)')
								   ->relationship('user', 'name')
								   ->nullable()
								   ->placeholder('Global Monster (Shared)')
								   ->default(fn () => auth()->id()),
						 ])->columns(2);
	}

	public static function table(Table $table): Table
	{
		return $table
			->columns([
						  TextColumn::make('name')->searchable()->sortable(),
						  TextColumn::make('ac')->label('AC')->sortable(),
						  TextColumn::make('max_health')->label('Max HP')->sortable(),
						  TextColumn::make('user.name')->label('Owner (GM)')->sortable()->placeholder('Global'),
						  TextColumn::make('updated_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
					  ])
			->filters([
						  //
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

	public static function parseMonsterJson(string $jsonContent): ?array
	{
		// TODO: Implement monster JSON parsing similar to CharacterResource if needed.
		// For now, this is a placeholder.
		// Ensure this method can handle potential JSON errors gracefully.
		// Example structure:
		// $data = json_decode($jsonContent, true);
		// if (json_last_error() !== JSON_ERROR_NONE) { return null; }
		// return $data; // or transform $data to fit model structure
		return null;
	}

	public static function getRelations(): array
	{
		return [
			//
		];
	}

	public static function getPages(): array
	{
		return [
			'index' => ListMonsters::route('/'),
			'create' => CreateMonster::route('/create'),
			'edit' => EditMonster::route('/{record}/edit'),
		];
	}
}