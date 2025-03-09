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
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EncounterResource extends Resource
{
	protected static ?string $model = Encounter::class;

	protected static ?string $navigationIcon = 'heroicon-o-sparkles';


	public static function form(Form $form): Form
	{
		return $form
			->schema([
						 Forms\Components\TextInput::make('name')->required(),
						 Forms\Components\TextInput::make('round_count')->numeric()->default(0)->required(),
					 ]);
	}

	public static function table(Table $table): Table
	{
		return $table
			->columns([
						  Tables\Columns\TextColumn::make('name'),
						  Tables\Columns\TextColumn::make('round_count'),
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
			RelationManagers\CharactersRelationManager::class,
		];
	}

	public static function getPages(): array
	{
		return [
			'index' => Pages\ListEncounters::route('/'),
			'create' => Pages\CreateEncounter::route('/create'),
			'edit' => Pages\EditEncounter::route('/{record}/edit'),
		];
	}
}