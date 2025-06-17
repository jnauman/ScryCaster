<?php

namespace App\Filament\Resources\EncounterResource\RelationManagers;

use App\Models\Character;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;

class PlayerCharactersRelationManager extends RelationManager
{
	protected static string $relationship = 'playerCharacters';
    protected static ?string $recordTitleAttribute = 'name'; // Or keep as is if 'name' is sufficient

	public function form(Form $form): Form
	{
		return $form
			->schema([
                TextInput::make('initiative_roll') // This field is on the pivot table
                    ->numeric()
                    ->nullable()
                    ->label('Initiative Roll'),
                // 'order' is typically calculated, not manually set here.
                // 'current_health' is an attribute of Character, not easily editable in BelongsToMany form here.
					 ]);
	}

	public function table(Table $table): Table
	{
		return $table
            ->recordTitleAttribute('name') // Refers to the Character's name
			->columns([
                TextColumn::make('name')->label('Player Character')->searchable()->sortable(),
                TextColumn::make('current_health')->label('Current HP')->sortable(),
                TextColumn::make('max_health')->label('Max HP')->sortable(),
                TextColumn::make('ac')->label('AC')->sortable(),
                TextColumn::make('pivot.initiative_roll')->label('Initiative')->sortable(), // Access pivot data
                TextColumn::make('pivot.order')->label('Order')->sortable(), // Access pivot data
					  ])
			->filters([
						  //
					  ])
			->headerActions([
                Tables\Actions\AttachAction::make()
                    ->preloadRecordSelect()
                    ->recordTitleAttribute('name') // Uses Character's name for selection
                    ->multiple()
                    ->form(fn (Tables\Actions\AttachAction $action): array => [
                        $action->getRecordSelect(), // This is the select field for Character
                        TextInput::make('initiative_roll')->numeric()->nullable()->label('Initiative Roll'),
                    ])
                    ->recordSelectSearchColumns(['name']),
                // Tables\Actions\CreateAction::make(), // Removed: Prefer creating PCs via CharacterResource
							])
			->actions([
                Tables\Actions\EditAction::make(), // Edits pivot table fields defined in form()
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