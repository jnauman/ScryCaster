<?php

namespace App\Filament\Resources\EncounterResource\RelationManagers;

use App\Models\Character;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CharactersRelationManager extends RelationManager
{
	protected static string $relationship = 'characters';

	public function form(Form $form): Form
	{
		return $form
			->schema([
						 Forms\Components\TextInput::make('initiative_roll')
												   ->numeric()
												   ->nullable(),
						 Forms\Components\Toggle::make('current_turn')
												->default(false),
					 ]);
	}

	public function table(Table $table): Table
	{
		return $table
			->columns([
						  Tables\Columns\TextColumn::make('name'),
						  Tables\Columns\TextColumn::make('initiative_roll'),
						  Tables\Columns\IconColumn::make('current_turn')
												   ->boolean(),
					  ])
			->filters([
						  //
					  ])
			->headerActions([
								Tables\Actions\AttachAction::make()
									->recordTitle(fn (Character $record): string => "{$record->name} - {$record->type}")
									->recordSelectSearchColumns(['name']),
								Tables\Actions\CreateAction::make(),
							])
			->actions([
						  Tables\Actions\EditAction::make(),
						  Tables\Actions\DetachAction::make(),
						  Tables\Actions\DeleteAction::make(),
					  ])
			->bulkActions([
							  Tables\Actions\BulkActionGroup::make([
																	   Tables\Actions\DetachBulkAction::make(),
																	   Tables\Actions\DeleteBulkAction::make(),
																   ]),
						  ]);
	}
}