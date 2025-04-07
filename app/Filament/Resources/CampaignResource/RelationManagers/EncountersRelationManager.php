<?php

namespace App\Filament\Resources\CampaignResource\RelationManagers;

use App\Filament\Resources\EncounterResource;
use App\Models\Encounter;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EncountersRelationManager extends RelationManager
{
    protected static string $relationship = 'encounters';

    public function form(Form $form): Form
    {
		return $form
			->schema([
						 Forms\Components\TextInput::make('name')
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
						  Tables\Columns\TextColumn::make('name')
												   ->searchable()
												   ->sortable(),
						  Tables\Columns\TextColumn::make('current_round')
												   ->numeric()
												   ->sortable(),
						  Tables\Columns\TextColumn::make('created_at')
												   ->dateTime()
												   ->sortable()
												   ->toggleable(isToggledHiddenByDefault: true),
					  ])
			->filters([
						  //
					  ])
			->headerActions([
								// Button to create a new encounter FOR THIS CAMPAIGN
								Tables\Actions\CreateAction::make(),
							])
			->actions([
						  // Button to edit an encounter
						  //Tables\Actions\EditAction::make(),
						  Tables\Actions\Action::make('edit')
											   ->label('Edit')
											   ->icon('heroicon-o-pencil-square') // Use standard edit icon
							  ->url(fn (Encounter $record): string => \App\Filament\Resources\EncounterResource::getUrl('edit', ['record' => $record])), // Use FQN for EncounterResource if not imported

						  // Button to delete an encounter
						  Tables\Actions\DeleteAction::make(),
						  // Optional: Add a button to go to the "Run Encounter" page
						  Tables\Actions\Action::make('run')
											   ->label('Run')
											   ->icon('heroicon-o-play')
											   ->url(fn (Encounter $record): string => EncounterResource::getUrl('run', ['record' => $record])),
					  ])
			->bulkActions([
							  Tables\Actions\BulkActionGroup::make([
												   Tables\Actions\DeleteBulkAction::make(),
											   ]),
						  ]);
	}
}
