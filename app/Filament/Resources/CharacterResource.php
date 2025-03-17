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
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CharacterResource extends Resource
{
	protected static ?string $model = Character::class;

	protected static ?string $navigationIcon = 'heroicon-o-user-group';

	public static function form(Form $form): Form
	{
		return $form
			->schema([
						 Forms\Components\TextInput::make('name')->required(),
						 Forms\Components\Select::make('type')
												->options([
															  'player' => 'Player',
															  'monster' => 'Monster',
														  ])->required(),
						 Forms\Components\TextInput::make('ac')->label('Armor Class')->numeric()->required(),
						 Forms\Components\TextInput::make('strength')->label('Strength')->numeric(),
						 Forms\Components\TextInput::make('dexterity')->label('Dexterity')->numeric(),
						 Forms\Components\TextInput::make('constitution')->label('Constitution')->numeric(),
						 Forms\Components\TextInput::make('intelligence')->label('Intelligence')->numeric(),
						 Forms\Components\TextInput::make('wisdom')->label('Wisdom')->numeric(),
						 Forms\Components\TextInput::make('charisma')->label('Charisma')->numeric(),
						 Forms\Components\TextInput::make('max_health')->label('Max Health')->numeric()->required(),
						 Forms\Components\TextInput::make('current_health')->label('Current Health')->numeric()->required(),
					 ]);
	}

	public static function table(Table $table): Table
	{
		return $table
			->columns([
						  Tables\Columns\TextColumn::make('name'),
						  Tables\Columns\TextColumn::make('type'),
						  Tables\Columns\TextColumn::make('ac'),
						  Tables\Columns\TextColumn::make('max_health'),
						  Tables\Columns\TextColumn::make('current_health'),
					  ])
			->filters([
						  //
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

	public static function getRelations(): array
	{
		return [
			//
		];
	}

	public static function getPages(): array
	{
		return [
			'index' => Pages\ListCharacters::route('/'),
			'create' => Pages\CreateCharacter::route('/create'),
			'edit' => Pages\EditCharacter::route('/{record}/edit'),
		];
	}
}