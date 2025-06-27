<?php

namespace App\Filament\Resources\EncounterResource\RelationManagers;

use Filament\Schemas\Schema;
use Filament\Actions\AttachAction;
use Filament\Actions\EditAction;
use Filament\Actions\DetachAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DetachBulkAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;

class PlayerCharactersRelationManager extends RelationManager
{
	protected static string $relationship = 'playerCharacters';
    protected static ?string $recordTitleAttribute = 'name'; // Or keep as is if 'name' is sufficient

	public function form(Schema $schema): Schema
	{
		return $schema
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
                AttachAction::make()
                    ->preloadRecordSelect()
                    ->recordTitleAttribute('name') // Uses Character's name for selection
                    ->multiple()
                    ->form(fn (AttachAction $action): array => [
                        $action->getRecordSelect(), // This is the select field for Character
                        TextInput::make('initiative_roll')->numeric()->nullable()->label('Initiative Roll'),
                    ])
                    ->recordSelectSearchColumns(['name']),
                // Tables\Actions\CreateAction::make(), // Removed: Prefer creating PCs via CharacterResource
							])
			->recordActions([
                EditAction::make(), // Edits pivot table fields defined in form()
						  DetachAction::make(),
						  DeleteAction::make(),
					  ])
			->toolbarActions([
							  BulkActionGroup::make([
																	   DetachBulkAction::make(),
																	   DeleteBulkAction::make(),
																   ]),
						  ]);
	}
}