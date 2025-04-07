<?php

namespace App\Filament\Resources\EncounterResource\RelationManagers;

use App\Models\Character;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

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
						 Forms\Components\TextInput::make('order')
							 ->numeric()
							 ->nullable(),
					 ]);
	}

	public function table(Table $table): Table
	{
		return $table
			->columns([
						  Tables\Columns\TextColumn::make('name'),
						  Tables\Columns\TextColumn::make('initiative_roll'),
						  Tables\Columns\TextColumn::make('order'),
					  ])
			->filters([
						  //
					  ])
			->headerActions([
								Tables\Actions\AttachAction::make()
									->preloadRecordSelect() // Preloads options for better performance/UX
									->recordTitle(fn (Character $record): string => "{$record->name} - {$record->type}")
									->multiple()
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