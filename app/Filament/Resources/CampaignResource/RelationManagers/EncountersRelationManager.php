<?php

namespace App\Filament\Resources\CampaignResource\RelationManagers;

use App\Filament\Resources\EncounterResource;
use App\Models\Encounter;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
class EncountersRelationManager extends RelationManager
{
    protected static string $relationship = 'encounters';

	public function form(Schema $schema): Schema
	{
		return $schema
		->schema([
						 TextInput::make('name')
												   ->required()
												   ->maxLength(255)
												   ->columnSpanFull(),
						 // Add other encounter fields as needed here
						 // Forms\Components\TextInput::make('current_round')->numeric()->default(0),
						 // campaign_id is handled automatically by the relationship
					 ]);
    }

	public function table(Table $table): Table
	{
		return $table
			->recordTitleAttribute('name') // Assumes 'name' is the main identifying column
			->columns([
						  TextColumn::make('name')
												   ->searchable()
												   ->sortable(),
						  TextColumn::make('current_round')
												   ->numeric()
												   ->sortable(),
						  TextColumn::make('created_at')
												   ->dateTime()
												   ->sortable()
												   ->toggleable(isToggledHiddenByDefault: true),
					  ])
			->filters([
						  //
					  ])
			->headerActions([
								// Button to create a new encounter FOR THIS CAMPAIGN
								CreateAction::make(),
							])
			->recordActions([
						  // Button to edit an encounter
						  //Tables\Actions\EditAction::make(),
						  Action::make('edit')
											   ->label('Edit')
											   ->icon('heroicon-o-pencil-square') // Use standard edit icon
							  ->url(fn (Encounter $record): string => EncounterResource::getUrl('edit', ['record' => $record])), // Use FQN for EncounterResource if not imported

						  // Button to delete an encounter
						  DeleteAction::make(),
						  // Optional: Add a button to go to the "Run Encounter" page
						  Action::make('run')
											   ->label('Run')
											   ->icon('heroicon-o-play')
											   ->url(fn (Encounter $record): string => EncounterResource::getUrl('run', ['record' => $record])),
					  ])
			->toolbarActions([
							  BulkActionGroup::make([
												   DeleteBulkAction::make(),
											   ]),
						  ]);
	}
}
